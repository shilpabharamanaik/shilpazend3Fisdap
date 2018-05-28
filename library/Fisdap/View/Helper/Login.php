<?php

/**
 * Login helper
 *
 */
class Fisdap_View_Helper_Login
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function login()
    {
		$html = '';
		
		// HEADER AREA
		$html .= <<<HEADER
		
			<div class='header'>
				<!-- <div class='back_link'><a href='http://content.fisdapoffice.com/home'>&lt;&lt; Back to Fisdap Home</a></div> -->
				<div class='top-bar'> 
					<div class='logo'>	
						<img src='images/Fisdap_logo_new_small.png'> 
					</div> 
					<div class='page-header'>
						Member Login
					</div> 
					<div class='spacer'></div> 
				</div> 
			</div>
		<div class='grid_12'>
		</div>
HEADER;
		
		// LOGIN AREA
		$html .= "<div class='grid_8'><div id='login_area' class='area left'>";
		$html .= $this->view->form . '</div></div>';
		
		// CREATE ACCOUNT AREA
		$createAccountLink = '<a href="' . $this->view->url() . '">Create an Account</a>';
		$contactUsLink = '<a href="' . $this->view->url() . '">Contact Us</a>';
		//<a href='http://content.fisdapoffice.com/contact_us'>Contact us</a> 
		
		$html .= <<<CREATEACCT
		<div class='grid_4'>
			<div id='create_acct' class='area left'>
				<div class="form_field"> 
					<h3>Not a member yet?</h3><br/>
					<div style='margin-left:0'>	
						$createAccountLink
					</div> 
				</div> 
				
				<br/><br/>
				
				<div class="form_field"> 
					<h3>Questions?</h3><br/>
					<div style='margin-left:0'>	
						$contactUsLink
					</div> 
				</div>  		
			</div>
		</div>
CREATEACCT;
			
		return $html; 
	}
}
?>