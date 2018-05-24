<?php
/**
 * Class to export files in csv format
 * @package Fisdap
 */
class CsvController extends Zend_Controller_Action
{
	/**
	 * This action creates a csv document based on the posted values
	 * Sends HTML headers for file to download (prompts download dialog)
	 */
	public function createCsvAction()
	{
		$fileName = urldecode($this->_getParam('fileName'));
		$fileContents = urldecode($this->_getParam('fileContents'));
		
		// send header
		header("Content-type: text/csv");
		header("Content-Transfer-Encoding: UTF-8");
		header("Content-Disposition: attachment; filename={$fileName}");
		
		echo $fileContents;
	
		die();
	}

}
