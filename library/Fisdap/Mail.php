<?php

namespace Fisdap;

/**
 *	Fisdap email
 */
class Mail //extends Zend_Mail
{
    protected static $_instance;

    protected $entityManager;
    
    private function __construct()
    {
    }
    
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
        
        
    /**
     *	each recipient can be specified as:
     *		<number>							= user_id
     *		<string> or <number> => <string>	= textual email / no name caption
     *		<string> => <string>				= email => name caption
     * @param mixed $optionsOrRecipient	all arguments can be given in here, but for
     *		simple messages quickly give: recipient, subject, message
     * @param string $subject
     * @param string $message
     *
     * 	$options:
     * 		'From'		default: fisdap-robot@fisdap.net
     * 		'To'		accepted: single or array of: user_id, UserEntity, email-address or pair:
     * 			array('email' => 'Name')
     * 		'Subject'
     * 		'BodyText'
     * 		'ReplyTo'
     * 		'Signature'	setting it overwrites default fisdap signature (to disable signature set to '')
     *
     * 	Quick usage:
     * 		sendMail(array('mbogucki@fisdap.net' => 'Maciej', 307), 'Subject-2 recipients', 'Hello from Fisdap')
     * 		sendMail(1, 'Subject', 'Quick one!');
     */
    public static function sendMail($optionsOrRecipient, $subject='FISDAP: Automated Message', $message='')
    {
        $ret = array();
        $ret['Result'] = true;

        $me = self::getInstance();
        $em = \Fisdap\EntityUtils::getEntityManager();
        
        // options or recipient
        if (is_array($optionsOrRecipient)) {
            $options = $optionsOrRecipient;
            
            // process 'To'
            $recipients = self::processRecipients($options['To']);
            if (empty($recipients->messages)) {
                $options['To'] = $recipients->To;
            } else {
                return array('BodyText' => implode(', ', $recipients->messages));
            }
        } else {
            $options = array();

            // process 'To'
            $recipients = self::processRecipients($optionsOrRecipient);
            if (empty($recipients->messages)) {
                $options['To'] = $recipients->To;
            } else {
                return array('BodyText' => implode(', ', $recipients->messages));
            }
        }
        
        // Signature
        if (!isset($options['Signature'])) {
            $options['Signature'] = self::getSignature();
        }
        
        // set subject
        if (!isset($options['Subject'])) {
            $options['Subject'] = $subject;
        }
        
        // set message
        if (!isset($options['BodyText'])) {
            $options['BodyText'] = $message;
        }
        
        // Default options
        $defaults = array(
            'From' => 'fisdap-robot@fisdap.net',
            //'ReplyTo' => 'fisdap-robot@fisdap.net',
        );
        
        foreach ($defaults as $option => $defaultValue) {
            if (!isset($options[$option])) {
                $options[$option] = $defaultValue;
            }
        }
        
        // From
        $recipients = self::processRecipients($options['From']);
        if ($recipients->messages) {
            return array('messages' => $recipients->messages);
        } else {
            $options['FromEmail'] = key($recipients->To);
            $options['FromName'] = $recipients->To[$options['FromEmail']];
        }
        
        $ret = $options;
        
        
        // Options to set to Zend_Mail
        $mail = new \Zend_Mail();
        
        // add signature to message
        $options['BodyText'] .= $options['Signature'];
        
        // Subject, Message
        $zendOptions = array(
            'BodyText', 'Subject'
        );
        foreach ($zendOptions as $option) {
            if (isset($options[$option])) {
                $method = 'set' . $option;
                $mail->$method($options[$option]);
            }
        }
        
        // To-s:
        foreach ($options['To'] as $email => $name) {
            $mail->addTo($email, $name);
        }
        
        // Reply-To:
        if (isset($options['ReplyTo'])) {
            $mail->addHeader('Reply-To', $options['ReplyTo']);
        }
        
        $mail->setFrom($options['FromEmail'], $options['FromName']);
        
        $mail->send();
        
        return $ret;
    }
    
    /**
     *	@param mixed user_id or user object
     *	@return std_object messages, email, name.
     *		Not empty messages = error message in it.
     */
    public static function getUserEmail($user_id)
    {
        $ret->messages = '';
        if (is_numeric($user_id)) {
            $user = \Fisdap\EntityUtils::getEntity('User', $user_id);
            if (!$user) {
                $ret->messages = 'User does not exist';
                return $ret;
            }
        } else {
            $user = $user_id;
        }
        
        if (!$user->email) {
            $ret->messages = 'No email on file for user';
        } else {
            $ret->email = $user->email;
            $ret->name = $user->first_name . ' ' . $user->last_name;
        }
        return $ret;
    }
    
    public static function getSignature()
    {
        $result .= "\nStay safe.\n\n";
        $result .= "FISDAP Robot\n";
        $result .= "Director of Automated Communications\n";
        $result .= "fisdap-robot@fisdap.net\n";
        $result .= "651-690-9241\n\n";
        $result .= "Please do not reply to this email.";
        return $result;
    }
    
    /**
     *
     * Takes user settings in different forms and gets recipient's emails and names
     * @todo this should be recursive but it's not.
     */
    public static function processRecipients($to)
    {
        $ret->To = array();
        $ret->messages = array();
        
        if (isset($to)) {
            if (is_array($to)) {
                foreach ($to as $id => $val) {
                    if (is_numeric($val)) { // user id given
                        $email = self::getUserEmail($val);
                        if ($email->messages) { // problem email/user
                            $ret->messages[] = $email->messages . '(UserId: ' . $val . ')';
                        } else {
                            $ret->To[$email->email] = $email->name;
                        }
                    } else {
                        if (is_numeric($id)) {	// email string given, no name string
                            $ret->To[$val] = $val;
                        } else {				// email and name string given
                            $ret->To[$id] = $val;
                        }
                    }
                }
            } else { // one textual email address
                if (is_numeric($to)) {
                    $email = self::getUserEmail($to);
                    if ($email->messages) { // problem email/user
                        $ret->messages[] = $email->messages . '(UserId: ' . $to . ')';
                    } else {
                        $ret->To[$email->email] = $email->name;
                    }
                } else {
                    $ret->To[$to] = $to;
                }
            }
        }
        return $ret;
    }
}
