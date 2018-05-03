<?php
/* 
 * This interface should be used for any classes that will use the automatic
 * conversion script.
 *
 * That method can be called automagically from some service.
 */

/**
 *
 * @author astevenson
 */
interface Util_Convert_iDatabaseConverter {
	
	/**
	 * This method is used to actually move the specific fields for one piece
	 * from the old database into the new.
	 *
	 * @param Mixed $data Any data that is required for the conversion to
	 * complete successfully should be passed in here.
	 */
	public function convert($data);
}
?>
