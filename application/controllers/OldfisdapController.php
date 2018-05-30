<?php

/****************************************************************************
*
*         Copyright (C) 1996-2011.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

/**
 * This file will handle redirecting a user over to the old version of fisdap.
 *
 * @author astevenson
 */
class OldfisdapController extends Fisdap_Controller_Base
{
    public function init()
    {
        parent::init();
    }
    
    public function redirectAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity() || $this->_getParam('username', null) != null) {
            //This tells the other server to come back to members once they've been logged in
            $loopback = $this->_getParam('loopback', 0);
            
            $loc = $this->_getParam('loc');
            $loc = str_replace(';', '&', $loc);
            
            $location = urlencode($loc);

            $userName = '';
            
            if ($this->_getParam('username', null)) {
                $userName = $this->_getParam('username', null);
            } else {
                $userName = Zend_Auth::getInstance()->getIdentity();
            }
            
            // This is a pre-shared key used to encode the username.  Makes it
            // so that unless someone knows this key and XORs it with the passed
            // username, they won't be able to tell what it is.  Also lessens
            // the liklihood of someone spoofing the username and passing across
            // a different one.
            $bitKey = base64_encode("MaryHadALittleLambWhoseFleeceWasWhiteAsSnow");
            
            $loginNamespace = new Zend_Session_Namespace('loginVars');
            
            // XOR the username with the bitkey, then run it through a couple of
            // filters to change it around a bit, then finally urlencode it so it
            // can be passed around.
            $encodedUsername = urlencode(base64_encode($userName ^ $bitKey));
            $encodedPassword = urlencode(base64_encode($loginNamespace->password ^ $bitKey));
            
            // Grab whether or not the user checked the "I'm Secure" button when
            // they logged in...
            $isSecure = $loginNamespace->isSecure;
            
            // Get the base URL to redirect to...
            $oldServerBase = Util_HandyServerUtils::get_fisdap_members1_url_root();
            
            if ($loginNamespace->legacyLoggedIn == true) {
                //If we've already logged in to legacy, send along a flag so that the legacy server doesn't force another login
                $oldServerURL = $oldServerBase . "rerouteRequest.php?iss=$isSecure&c=$encodedUsername&d=$encodedPassword&loc=$location&login=1";
            } elseif ($loopback) {
                //Send along the loopback flag if it's set
                $oldServerURL = $oldServerBase . "rerouteRequest.php?iss=$isSecure&c=$encodedUsername&d=$encodedPassword&loopback=1&loc=$location";
            } else {
                $oldServerURL = $oldServerBase . "rerouteRequest.php?iss=$isSecure&c=$encodedUsername&d=$encodedPassword&loc=$location";
            }
            
            //Grab browser info to send to legacy fisdap
            $browser = Zend_Registry::get('device')->getBrowser();
            $browserVersion = Zend_Registry::get('device')->getBrowserVersion();
            $oldServerURL .= "&browser=$browser&version=$browserVersion";

            if (isset($_GET['forceUsername']) && $_GET['forceUsername'] == 1) {
                $oldServerURL .= "&forceUsername=1";
            }

            $loginNamespace->legacyLoggedIn = true;
            
            $this->redirect($oldServerURL);
        } else {
            $this->redirect('/login');
        }
    }
    
    /**
     * This function takes a hook ID and fetches back a listing of associated
     * evals.
     */
    public function evalHookAction()
    {
        $hookID = $this->_getParam('hid');
        $shiftID = $this->_getParam('sid');
        $this->user =  \Fisdap\Entity\User::getLoggedInUser();
        if (!empty($this->user)) {
            $evals = \Fisdap\EntityUtils::getRepository("EvalDefLegacy")->getEvalsByHook($hookID, $this->user->getProgramId());
        }
        $this->view->hookID = $hookID;
        $this->view->shiftID = $shiftID;
        
        $subjectId = \Fisdap\Entity\User::getLoggedInUser()->id;
        
        $this->view->subject = $subjectId; //Zend_Auth::getInstance()->getIdentity();

        $this->view->evals = $evals;

        $this->view->pageTitle = "Skills Sheets & Evaluations";
        
        $this->view->proc = $this->_getParam('proc', false);
        // This is a context sensitive ID- differs depending on the proc field.
        // For edit/delete, it's the Eval_Session id, for Add, it's the Eval_Def id.
        $this->view->aid = $this->_getParam('aid', false);
        
        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftID);
        
        // Build up a list of evals that the student has completed.
        $this->view->completedEvals = \Fisdap\Entity\EvalSessionLegacy::getUsersCompletedEvals($shiftID, $hookID, $shift->student->user->id);
        
        $this->view->noHeader = true;
        $this->view->noFooter = true;
    }
    
    public function getEvalUrlAction()
    {
        $evalDefID = $this->_getParam('edid');
        $evalHookID = $this->_getParam('ehid');
        
        // The primary shift ID.  This is the actual shift that we need to return to once we're done saving the eval.
        // It needs to be passed around for cases when we're filling out an eval for a lab partner who DOES NOT own the shift.
        $psid = $this->_getParam('psid');
        
        $shiftID = $this->_getParam('sid');
        
        $source = $this->_getParam('source', 'f2');
        
        // This is where the remote form will post back to.  Basically will
        // send the form data back to the new fisdap2 server that the request
        // originally came from.
        if ($this->_getParam('postUrl')) {
            $posturl = urlencode($this->_getParam('postUrl'));
        } else {
            $posturl = urlencode($this->view->serverUrl() . "/oldfisdap/save-eval/");
        }
        
        $locString =  "shift/evals/eval_display.html?";
        $locString .= "EvalDef_id=$evalDefID";
        $locString .= "&EvalHookDef_id=$evalHookID";
        $locString .= "&Shift_id=$shiftID";
        $locString .= "&psid=$psid";
        $locString .= "&source=$source";
        $locString .= "&SessionListExpr=window.opener.EvalArray";
        $locString .= "&posturl=$posturl";
        
        // Figure out the actual subject string here...
        // The format for this is awful.  The format is {type}_{id}.
        //	1 => "Instructor",
        //	2 => "Student",
        //	3 => "Preceptor",
        //	4 => "Program",
        //	5 => "Site",
        //	6 => "Dept/Base",
        //	7 => "Anonymous"
        $subject = $this->_getParam('subject', null);
        
        if ($subject) {
            $user = \Fisdap\EntityUtils::getEntity('User', $subject);
            if ($user->isInstructor()) {
                $subject = "1_" . $user->getCurrentRoleData()->id;
            } else {
                $subject = "2_" . $user->getCurrentRoleData()->id;
            }
            
            $locString .= "&subject=$subject";
        }
        
        // If the link was generated from a lab skill link, tack on the pid (practice definition ID)
        if ($source == 'lab') {
            $pdi = $this->_getParam('pdi', null);
            
            if ($pdi) {
                $locString .= "&pdi=$pdi";
            }
        }
        
        $baseURL = "/oldfisdap/redirect/?loc=" . urlencode($locString);
        
        $this->_helper->json($baseURL);
    }
    
    public function getEvalViewUrlAction()
    {
        $evalSessionID = $this->_getParam('esid');
        
        $source = $this->_getParam('source', 'f2');
        
        if ($this->_getParam('postUrl')) {
            $posturl = urlencode($this->_getParam('postUrl'));
        } else {
            $posturl = urlencode($this->view->serverUrl() . "/oldfisdap/save-eval/");
        }
        
        $basePage = 'eval_session_display';
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        if ($user->isInstructor()) {
            $basePage = 'eval_session_edit';
        }
        
        $locString =  "shift/evals/{$basePage}.html?EvalSession_id=$evalSessionID&posturl=$posturl&source=$source";
        
        if ($source == 'lab') {
            $lpii = $this->_getParam('lpii', null);
            if ($lpii) {
                $locString .= "&lpii=$lpii";
            }
        }
        
        $baseURL = "/oldfisdap/redirect/?loc=" . urlencode($locString);
        
        $this->_helper->json($baseURL);
    }
    
    public function getEvalDeleteUrlAction()
    {
        $evalSessionID = $this->_getParam('esid');
        
        $locString =  "shift/evals/delete_eval_session.html?eval_to_delete=$evalSessionID&source=f2";
        
        $baseURL = "/oldfisdap/redirect/?loc=" . urlencode($locString);
        
        $this->_helper->json($baseURL);
    }
    
    public function fetchEvalAction()
    {
        $contents = self::getEvalContents($this->_getParam('esid'), $this->view->serverUrl());
        
        $this->view->headContents = $contents['head'];
        $this->view->bodyContents = $contents['body'];
        
        $this->view->redirectionTracker = $redirectionTracker;
        //$this->view->output = $output;
        
        if ($this->_getParam('json', false) == '1') {
            $this->_helper->json(array('head' => $headString, 'body' => $bodyString));
        } else {
            $this->_helper->layout->disableLayout();
        }
    }
    
    public static function getEvalContents($evalSessionID, $serverUrl)
    {
        $locString =  "shift/evals/eval_session_display.html?EvalSession_id=$evalSessionID&source=f2&server=members";
        $baseURL = $serverUrl . "/oldfisdap/redirect/?loc=" . urlencode($locString) . "&forceUsername=1&username=" . Zend_Auth::getInstance()->getIdentity();
        
        $redirectionTracker = array($baseURL);
        
        $this->view->includeBase = true;
        
        $output = \Fisdap\OldFisdapUtils::getLegacyPage($baseURL, $redirectionTracker);
        
        $headString = $output['head'];
        $bodyString = $output['body'];
        
        // Remove some references to some javascript files that just don't work...
        $headString = str_replace("<script type='text/javascript' src='../../phputil/cpaint2.inc.compressed.js'>", "", $headString);
        $headString = str_replace("<script type='text/javascript' src='../../phputil/html_gen_lib.js'></script>", "", $headString);
        $headString = str_replace("<script type='text/javascript' src='eval_display.js'></script>", "", $headString);
        
        // Remove the PDF icon...
        $bodyString = str_replace('<img align="right" border=0 src="../../images/pdf_icon.png">', '', $bodyString);
        
        return array('head' => $headString, 'body' => $bodyString);
    }
    
    /**
     * This function takes a list of eval session IDs and generates one file (in tmp) containing the contents of each eval.
     * The names of the files are returned in an ajax array.
     */
    public function writeEvalFilesAction()
    {
        $evalSessionIds = $this->_getParam('esids', array());
        
        $tmpNames = array();
        
        foreach ($evalSessionIds as $esid) {
            
            // Generate the contents of the eval...
            $contents = self::getEvalContents($esid, $this->view->serverUrl());
            
            $rootURL = Util_HandyServerUtils::get_fisdap_members1_url_root();
            
            $htmlContents = <<<EOT
			 <html>
				<head>
					<base href="{$rootURL}" />
					<link href="{$rootURL}shift/evals/eval_display.css" media="screen" rel="stylesheet" type="text/css" />
					<link href="{$rootURL}shift/evals/eval_display_new.css" media="screen" rel="stylesheet" type="text/css" />
					 
					<script type="text/javascript" src="{$rootURL}shift/evals/eval_display.js"></script>
					<script type="text/javascript" src="{$rootURL}phputil/html_gen_lib.js"></script>
					<script type="text/javascript" src="{$rootURL}phputil/cpaint2.inc.compressed.js"></script>
					 
					{$contents['head']}
				</head>
			 	
				<body>
					<script>
						$(function(){
							$('#stopwatch').remove();
							$('img[src="../../images/pdf_icon.png"]').remove();
						});
					</script> 
					{$contents['body']}
				</body>
			</html>
EOT;

            // Dump out to a file and track the name it was saved to...
            $tempFileName = tempnam(sys_get_temp_dir(), 'eval_html_') . ".html";
            $handle = fopen($tempFileName, 'w+');
            
            fwrite($handle, $htmlContents);
            fclose($handle);
            
            $tmpNames[] = $tempFileName;
        }
        
        // Now generate a PDF of the contents of the temp files, store that in temp as well and return the name of the PDF file.
        $pdfFileName = tempnam(sys_get_temp_dir(), 'pdf_') . ".pdf";
        
        $pdfer = new wkhtmltopdf_Pdf();
        
        $pdfer->createPDFFromFiles($tmpNames, $pdfFileName);
        
        $this->_helper->json($pdfFileName);
    }
    
    public function testEvalPdfAction()
    {
    }
    
    public function saveEvalAction()
    {
        // @TODO: Logic to perform the save goes here...
        $this->view->reopenEval = $this->_getParam('submitnew');
        $this->view->evalDefID = $this->_getParam('EvalDef_id');
    }
    
    public function formTestAction()
    {
        $form = new Fisdap_Form_CommentContacts();
        $form->customInit();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $postVars = $request->getPost();
            var_dump($form->process($postVars['contactSubform']));
        }
        
        $bigForm = new Zend_Form();
        $bigForm->addSubForm($form, 'contactSubform');
        
        //$bigForm->addElement(new Zend_Form_Element_Submit('submit'));
        
        $this->view->form = $bigForm; //$bigForm;
    }
    
    public function evalRenderAction()
    {
        $this->_helper->layout->disableLayout();
    }
}
