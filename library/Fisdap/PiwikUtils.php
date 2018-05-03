<?php

/****************************************************************************
 *
*         Copyright (C) 1996-2014.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

namespace Fisdap;

class PiwikUtils {

	/**
	 * Return Zend_Db for Piwik installation
	 */
	public static function getConnection()
	{
		$config = \Zend_Registry::get('config');
	

		$connection = \Zend_Db::factory($config->piwik);
		return $connection;
	}
	
	
	/**
	 * Stub for getting basic browser info along with Fisdap userIDs
	 * Fisdap UserID (from fisdap2_users) is being stored in piwik as custom_var_k1/custom_var_v1
	 */
	public static function getVisits() {
		$connection = self::getConnection();;
		$select = "select config_browser_name, config_browser_version, custom_var_k1, custom_var_v1 FROM piwik_log_visit WHERE custom_var_k1 = 'userid'";
		$visits = $connection->fetchAll($select);
		return $visits;
	}
}