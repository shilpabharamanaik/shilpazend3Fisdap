<?php
/****************************************************************************
*
*         Copyright (C) 1996-2010.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

/**
 * This class just acts as a wrapper around the \MailChimp_Api class, and provides
 * some convenience methods for accessing the API.  If you need to add more
 * functionality, the optimal choice would be to add it here so others can use
 * it at a later time.
 *
 * If you need to directly access a function on the API class, simply call it
 * on the instance of the wrapper.  It will automagically delegate any call to
 * an unknown function onto the actual API class.
 *
 * For example, calling $wrapperInstance->campaignExecute($campaignID) (which
 * is not explicitley defined on the wrapper) will call
 * \MailChimp_Api::campaignExecute($campaignID) and seamlessly return the results.
 *
 * If for some reason you really really need to have a local instance of the API,
 * just call $wrapperInstance->getAPIObject().
 */
class MailChimp_Wrapper
{
    /**
     * This stores to API access key that we can use.  This was generated on the
     * MailChimp website, and hopefully won't be very dynamic.  If it does expire,
     * just generate a new one and place it here.
     */
    public static $apikey = "f706541380323453d69e96fe0e389371-us2";
    
    /**
     * @var array key/value pairs for states
     */
    public static $states = array(
        'AL'=>"Alabama",
        'AK'=>"Alaska",
        'AZ'=>"Arizona",
        'AR'=>"Arkansas",
        'CA'=>"California",
        'CO'=>"Colorado",
        'CT'=>"Connecticut",
        'DE'=>"Delaware",
        'DC'=>"District Of Columbia",
        'FL'=>"Florida",
        'GA'=>"Georgia",
        'HI'=>"Hawaii",
        'ID'=>"Idaho",
        'IL'=>"Illinois",
        'IN'=>"Indiana",
        'IA'=>"Iowa",
        'KS'=>"Kansas",
        'KY'=>"Kentucky",
        'LA'=>"Louisiana",
        'ME'=>"Maine",
        'MD'=>"Maryland",
        'MA'=>"Massachusetts",
        'MI'=>"Michigan",
        'MN'=>"Minnesota",
        'MS'=>"Mississippi",
        'MO'=>"Missouri",
        'MT'=>"Montana",
        'NE'=>"Nebraska",
        'NV'=>"Nevada",
        'NH'=>"New Hampshire",
        'NJ'=>"New Jersey",
        'NM'=>"New Mexico",
        'NY'=>"New York",
        'NC'=>"North Carolina",
        'ND'=>"North Dakota",
        'OH'=>"Ohio",
        'OK'=>"Oklahoma",
        'OR'=>"Oregon",
        'PA'=>"Pennsylvania",
        'RI'=>"Rhode Island",
        'SC'=>"South Carolina",
        'SD'=>"South Dakota",
        'TN'=>"Tennessee",
        'TX'=>"Texas",
        'UT'=>"Utah",
        'VT'=>"Vermont",
        'VA'=>"Virginia",
        'WA'=>"Washington",
        'WV'=>"West Virginia",
        'WI'=>"Wisconsin",
        'WY'=>"Wyoming"
    );
    
    private $lists = array();
    
    /**
     * This is the API instance.  Instantiated in the constructor.
     */
    private $api;
    
    /**
     * Default constructor- mainly just instantiates the API class and pulls
     * down a list of the avaialable lists.
     *
     * @param $overrideKey 	String containing the API key to use.  If none is
     * 						set, defaults to the one stored in
     * 						MailChimp_MCIWrapper::apikey
     */
    public function __construct($overrideKey=null)
    {
        $key = (is_null($overrideKey)? self::$apikey : $overrideKey);
        $this->api = new \MailChimp_Api($key);
        
        $this->populateLocalLists();
    }
    
    /**
     * This function implements the magic __call functionality that PHP provides
     * for classes.  In effect, if a call comes through that we aren't explicitly
     * handling in this class, this function gets called.  It will then allow us
     * to delegate the call off to the API class itself- this makes it so that
     * we don't have to implement simple one-liner wrapper functions.
     */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->api, $name), $args);
    }
    
    /**
     * Accessor function for getting at the API class.
     *
     * @return \MailChimp_Api instance
     */
    public function getAPIObject()
    {
        return $this->api;
    }
    
    /**
     * This function pulls down all available lists and stores them in a more
     * easily searchable local copy.
     */
    private function populateLocalLists()
    {
        $allLists = $this->api->lists();
        
        if ($allLists) {
            foreach ($allLists['data'] as $list) {
                $this->lists[$list['name']] = $list['id'];
            }
        }
    }
    
    /**
     * This function returns the ID of the specified list.
     *
     * @param String $listName 	Name of the list to return the ID for
     *
     * @return 	String containing the MailChimp ID for the specified list or
     * 			Boolean false if the list name does not exist.
     */
    public function getListIDByName($listName)
    {
        if (isset($this->lists[$listName])) {
            return $this->lists[$listName];
        } else {
            return false;
        }
    }
    
    /**
     * This function returns the name of the specified list.
     *
     * @param String $listID 	ID of the list to return the name for
     *
     * @return 	String containing the MailChimp Name for the specified list or
     * 			Boolean false if the list name does not exist.
     */
    public function getListNameByID($listID)
    {
        foreach ($this->lists as $name => $id) {
            if ($id == $listID) {
                return $name;
            }
        }
        
        return false;
    }
    
    /**
     * This method is used to return all lists that an email address is currently
     * subscribed to.
     *
     * @param String $email 	The email address to search for
     *
     * @return 	Array containing the subscribed lists, or Boolean false if
     * 			the user is not subscribed to anything.
     */
    public function getSubscribedLists($email)
    {
        $subscribedLists = array();
        
        foreach ($this->lists as $name => $listID) {
            if ($this->isEmailSubscribed($email, $name)) {
                $subscribedLists[] = $name;
            }
        }
        
        if (count($subscribedLists) > 0) {
            return $subscribedLists;
        } else {
            return false;
        }
    }
    
    /**
     * This method is used to determine if an email is subscribed to a specific
     * list.
     *
     * @param String $email 	The email address to find
     * @param String $listName	The list to subscribe the user to
     *
     * @return 	Boolean true on success, false if the user is not subscribed to
     * 			the given list.
     */
    public function isEmailSubscribed($email, $listName)
    {
        if ($listID = $this->getListIDByName($listName)) {
            $result = $this->api->listMemberInfo($listID, $email);
            
            if ($result['success'] == 1) {
                foreach ($result['data'] as $datum) {
                    if ($datum['status'] == 'subscribed') {
                        return true;
                    }
                }
            }
            return false;
        }
        
        // If we get down here, the query either failed or the user wasn't
        // subscribed to anything.
        return false;
    }
    
    /**
     * This function returns all members currently associated with the given
     * list.
     *
     * @param String $listName	The list to subscribe the user to
     * @param String $status 	Option argument to only get subscribed/unsubscribed
     * 							members back.  Defaults to 'subscribed'.
     * @param String $sortField Field to sort the elements by
     * @param String $sortDir 	Direction ('asc', 'desc') to sort by.
     *
     * @return Array of associated email addresses.
     */
    public function getListMembers($listName, $status='subscribed', $sortField='email', $sortDir='asc')
    {
        if ($listID = $this->getListIDByName($listName)) {
            $rawMembers = $this->api->listMembers($listID, $status);
            $members = $rawMembers['data'];
            
            // Sort the members based on the supplied data
            switch ($sortField) {
                case 'email':
                    usort($members, array(get_class($this), 'sortMembersEmail'));
                    break;
                case 'timestamp':
                    usort($members, array(get_class($this), 'sortMembersTimeAdded'));
                    break;
            }
            
            if ($sortDir == 'desc') {
                $members = array_reverse($members);
            }
            
            return $members;
        }
    }
    
    /**
     * This function takes an email address and subscribes it to the requested
     * list.
     *
     * @param String $email 	Email address to subscribe
     * @param String $listName 	List to subscribe the user to
     * @param Array $mergeData 	Array containing any extra data we want to store
     * 							with this user.  Currently only really set up to
     * 							handle "FNAME" and "LNAME".  Make the
     * 							index of the merge data the name of the field,
     * 							and the value at that index should be the person
     * 							-specific value.
     * 							Ex:
     * 							array(
     * 								'FNAME' => 'Alex',
     * 								'LNAME' => 'Stevenson'
     * 							)
     * @param Boolean $confirm 	Boolean determining whether or not to send out
     * 							a confirmation email before adding the new user
     * 							to the list.
     *
     * @return Boolean false if unable to subscribe the email, true on success
     */
    public function subscribeEmail($email, $listName, $mergeData, $confirm=false)
    {
        // Flag to determine whether or not to update the merge data for the user.
        // Assume true.
        $forceUpdateData = true;
        
        // If there is no Merge Data given, don't update the merge data.
        if (count($mergeData) == 0) {
            $forceUpdateData = false;
        }
        if ($listID = $this->getListIDByName($listName)) {
            $res = $this->api->listSubscribe($listID, $email, $mergeData, 'html', $confirm, $forceUpdateData);
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * This function takes an email address and unsubscribes it from the
     * requested list.
     *
     * @param String $email 		Email address to unsubscribe
     * @param String $listName 		List to unsubscribe the user from
     * @param Boolean $sendNotice 	Boolean determining whether or not to send
     * 								out a notification email of removal from
     * 								the list.  Defaults to false.
     *
     * @return Boolean false if unable to unsubscribe the email, true on success
     */
    public function unsubscribeEmail($email, $listName, $sendNotice=false)
    {
        if ($listID = $this->getListIDByName($listName)) {
            $this->api->listUnsubscribe($listID, $email, false, false, $sendNotice);
            
            // if something broke, return false.
            if ($message = $this->getErrorMessage()) {
                die('Error ' . $message['code'] . ':' . $message['message']);
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    
    /**
     * This function takes a current subscribe and updates their data on the requested list
     *
     * @param string $currentEmail
     * @param array $updatedData
     * @param string $listName
     *
     * @return boolean true on success, false on failure
     */
    public function updateEmail($currentEmail, $updatedData, $listName)
    {
        // If there is no data to update, return false
        if (count($updatedData) == 0) {
            return false;
        }
        
        if ($listID = $this->getListIDByName($listName)) {
            $res = $this->api->listUpdateMember($listID, $currentEmail, $updatedData);
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     * This method is used to get back a list of all available campaigns.
     *
     * @param $filters 	Array containing various MailChimp specific arguments.
     * 					See the \MailChimp_Api::campaigns() function docs for more
     * 					details.
     *
     * @return 	Array containing the requested campaigns, grouped together by
     * 			the lists they were created with.
     */
    public function getCampaigns($filters=array())
    {
        $rawCampaigns = $this->api->campaigns($filters);
        
        $campaigns = array();
        
        foreach ($rawCampaigns['data'] as $campaign) {
            $campaignAtom = $campaign;
            $campaignAtom['template_info'] = $this->templateInfo($campaign['template_id']);
            $campaigns[$campaign['list_id']][] = $campaignAtom;
        }
        
        return $campaigns;
    }
    
    /**
     * This method is used to get back a specific campaign.
     *
     * @param $id String ID of the campaign to fetch
     *
     * @return 	Array containing the requested campaign, grouped together by
     * 			the lists they were created with.
     */
    public function getCampaignByID($id)
    {
        $res = $this->api->campaigns(array('id' => $id));
        
        if (count($res['data']) > 0) {
            return array_pop($res['data']);
        }
        
        return false;
    }
    
    /**
     * This method just returns the last error message/codes from the API object
     *
     * @return 	Array containing the last error message/code, or boolean false
     * 			if no error exists.
     */
    public function getErrorMessage()
    {
        if ($this->api->errorMessage) {
            $error = array();
            $error['message'] = $this->api->errorMessage;
            $error['code'] = $this->api->errorCode;
            return $error;
        } else {
            return false;
        }
    }
    
    /**
     * Helper function to allow a sort to happen based on emails.
     *
     * @param $a 	Array containing the first element
     * @param $b 	Array containing the second element
     *
     * @return Int 0 if the values are equal, +1 if $a > $b, and -1 if $b > $a
     */
    public static function sortMembersEmail($a, $b)
    {
        if ($a['email'] == $b['email']) {
            return 0;
        }
        
        return ($a['email'] > $b['email'])?1:-1;
    }
    
    /**
     * Helper function to allow a sort to happen based on time added.
     *
     * @param $a 	Array containing the first element
     * @param $b 	Array containing the second element
     *
     * @return Int 0 if the values are equal, +1 if $a > $b, and -1 if $b > $a
     */
    public static function sortMembersTimeAdded($a, $b)
    {
        $aTime = strtotime($a['timestamp']);
        $bTime = strtotime($b['timestamp']);
        
        if ($aTime == $bTime) {
            return 0;
        }
        
        return ($aTime > $bTime)?1:-1;
    }
}
