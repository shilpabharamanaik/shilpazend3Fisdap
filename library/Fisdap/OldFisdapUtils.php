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

namespace Fisdap;

/**
 * Collection of static methods handy for Entities.
 *
 * @author astevenson
 */
class OldFisdapUtils
{
	public static function getLegacyPage($baseURL, &$redirectionTracker=array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_URL, $baseURL);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);

		$redirects = 0;

		$redirectionTracker = array($baseURL);

		// Loop through redirects...  If the remote server returns a 302 code, call up the redirection location and try again.
		// Limited to 10 attempts just so it doesn't murder the server if some crazy redirect loop happens.  Should only take 2-3.
		while($info['http_code'] == 302 && $redirects <= 10){
			curl_setopt($ch, CURLOPT_URL, $info['redirect_url']);

			$redirectionTracker[] = $info['redirect_url'];

			$output = curl_exec($ch);
			$info = curl_getinfo($ch);

			$redirects++;
		}

		curl_close($ch);

		preg_match("/\<head.*?\>(.*)\<\/head\>/s", $output, $headMatches);
		preg_match("/\<body.*?\>(.*)\<\/body\>/s", $output, $bodyMatches);

		$headString = $headMatches[1];
		$bodyString = $bodyMatches[1];

		return array('head' => $headString, 'body' => $bodyString);
	}
}
