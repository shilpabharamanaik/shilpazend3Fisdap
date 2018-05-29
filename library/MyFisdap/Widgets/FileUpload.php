<?php

class MyFisdap_Widgets_FileUpload extends MyFisdap_Widgets_Base
{
	protected $registeredCallbacks = array('handleUpload', 'downloadFile', 'deleteFile');
	
	public function render(){
		$fileList = $this->renderFileList();
		$formElement = $this->renderFormElement();
		
		// Check to see if they're on ipad..
		$device = Zend_Registry::get('device');
		
		if($device->getBrowser() == 'Safari Mobile'){
			return "File downloads are not supported on the iPad. To view and download files, please log in on your computer.";
		}else{
			$html ="
				{$fileList}
				
				{$formElement}
			";
		}
		
		return $html;
	}
	
	private function renderFileList(){
		$user = $this->getWidgetUser();
		
		$uploads = \Fisdap\EntityUtils::getRepository('WidgetUploads')->getUploadedFilesForProgram($this->getWidgetProgram(), $this->getWidgetUser());
		
		$html = '';
		
		if(count($uploads) > 0){
			$html .= "Click any of the files below to download them:";
			
			foreach($uploads as $upload){
				$dlURL = "/my-fisdap/widget-ajax/reroute-ajax-request/wid/{$this->widgetData->id}/fcn/downloadFile/data/{$upload->id}";
				
				$html .= "
					<script>
						var modalOptions = {
							modal: true,
							resizable: false,
							draggable: false,
							width: 600
						}
						
						confirmDelete = function(id, title){
							
							
							modalOptions.buttons = {
								'Delete' : function() {
									var dialogEl = $(this);
									
									routeAjaxRequest(
										{$this->widgetData->id},
										'deleteFile',
										{uploadId: id},
										function(){
											dialogEl.dialog('close');
											reloadWidget({$this->widgetData->id});
										}
									);
								},
								'Cancel' : function() {
									$(this).dialog('close');
								}
							};
							
							$('<div>Are you sure that you want to delete \"' + title + '\"?  This action cannot be undone.</div>').dialog(modalOptions);
							
							return false;
						};
					</script>
				";
				
				if($user->isInstructor() && $user->hasPermission('Edit Program Settings')){
					$html .= "
						<div>
							<a href='$dlURL'>
								{$upload->original_name}
							</a>
							
							<span style='position: relative; top: 5px;'><img src='/images/icons/delete.png' style='width: 20px; height: 20px;' onclick='confirmDelete({$upload->id}, \"{$upload->original_name}\");' /></span>
						</div>
					";
				}else{
					$html .= "<div><a href='$dlURL'>{$upload->original_name}</a></div>";
				}
			}
			
		}else{
			if($user->isInstructor() && $user->hasPermission('Edit Program Settings')){
				$html .= "<div>Upload files for your students and educators!</div>";
			}else{
				$html .= "<div>Your educator has not uploaded any files for you yet.</div>";
			}
		}
		
		return '<div class="file-list">' . $html . '</div>';
	}
	
	private function renderFormElement(){
		$user = $this->getWidgetUser();
		$certOptions = \Fisdap\Entity\CertificationLevel::getFormOptions(false, true, "description", $user->getCurrentProgram()->profession->id);
		
		if($user->isInstructor() && $user->hasPermission('Edit Program Settings')){
			$uploaderId = $this->getNamespacedName('file_uploader');
			$formId = $this->getNamespacedName('file_uploader_form');
			$uploadSpinner = $this->getNamespacedName('upload_spinner');
			$buttonId = $this->getNamespacedName('upload_button');
			
			$html = <<< EOF
				<div class='file-upload-form'>
					<script src='/js/jquery.iframe-post-form.js' type="text/javascript"></script>
					
					<script>
						$(function(){
							$('#{$formId}').iframePostForm({
								post: function(){
									$('#{$formId}').toggle();
									$('#{$uploadSpinner}').toggle();
								},
								complete: function(response){
									$('#{$formId}').toggle();
									$('#{$uploadSpinner}').toggle();
									
									if(response == 'false'){
										var modalOptions = {
											modal: true,
											resizable: false,
											draggable: false,
											width: 600,
											buttons: {
												'Ok' : function() {
													$(this).dialog('close');
												}
											}
										}
										
										$('<div>We\'re sorry, but we were unable to upload your file.  Files must be under 10Mb in size.</div>').dialog(modalOptions);
											
										return false;
									}else{
										reloadWidget({$this->widgetData->id});
									}
								}
							});
							
							$('#{$buttonId}').button().click(function() {
								if($('#{$uploaderId}').val() != ''){
									//$('#{$formId}').submit();
									return true;
								}else{
									var modalOptions = {
										modal: true,
										resizable: false,
										draggable: false,
										width: 600,
										buttons: {
											'Ok' : function() {
												$(this).dialog('close');
											}
										}
									}
									
									$('<div>Please select a file to upload.</div>').dialog(modalOptions);
									
									return false;
								}
							});
						});
					</script>
					
					<form id='{$formId}' name="form" action="/my-fisdap/widget-ajax/reroute-ajax-request" method="POST" enctype="multipart/form-data">
						<input type='hidden' name='wid' value='{$this->widgetData->id}' />
						<input type='hidden' name='fcn' value='handleUpload' />
						<input type='hidden' name='html-results' value='1' />
						<input type="hidden" name="MAX_FILE_SIZE" value="83886080" />
						<label for='{$uploaderId}' class='file-upload-label'>
								Upload a new file
							<span class='upload-widget-subtext'>
								(limit 10 MB):
							</span>
						</label>
						<input type='file' id='{$uploaderId}' name='upload' />
						<div class='file-upload-options'>
							Make this file available to:
							<div class='grid_12'>
								<div class='grid_6'>
									<input type='checkbox' name='data[educators-allowed]' value='1' CHECKED='CHECKED'/>Educators
								</div>
								<div class='clear'></div>
								<div class='grid_12 withTopMargin'>
									{$this->view->multiCheckboxList("data[certificationLevels][]", array_keys($certOptions), array(), $certOptions, "")}
								</div>
								
							</div>
						</div>
						<div class='green-buttons extra-small file-upload-submit'>
							<button value='Upload' id='$buttonId'>Upload</button>
						</div>
					</form>
					<div id='$uploadSpinner' style='display: none'>
						<img src='/images/throbber_small.gif' />
					</div>
				</div>
EOF;

			return $html;
		}else{
			return '';
		}
	}
	
	public function getDefaultData(){
		return array();
	}
	
	public function deleteFile($data){
		$upload = \Fisdap\EntityUtils::getEntity('WidgetUploads', $data['uploadId']);
		$upload->delete();
		
		return true;
	}
	
	public function downloadFile($data){
		$upload = \Fisdap\EntityUtils::getEntity('WidgetUploads', $data);
		$upload->getFile();
	}
	
	public function handleUpload($data){
		if($_FILES['upload']['tmp_name'] != ''){
			$upload = new \Fisdap\Entity\WidgetUploads();
			
			$upload->educators_allowed = (array_key_exists('educators-allowed', $data) && $data['educators-allowed'] == 1);
			$upload->setCertificationIds((array)$data['certificationLevels']);
			$upload->program = $this->getWidgetProgram();
			
			$upload->processFile($_FILES['upload'], '');
			
			return 'true';
		}else{
			return 'false';
		}
	}
}
