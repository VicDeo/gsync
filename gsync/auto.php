<?php

/**
 * ownCloud - gsync plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA_Gsync;

\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('contacts');
\OCP\App::checkAppEnabled('gsync');

$refreshToken = \OCP\Config::getUserValue(\OCP\User::getUser(), 'gsync', 'refresh_token', '');
if ($refreshToken){
	$response = Request::getAccessTokenByRefreshToken($refreshToken);
	$respData = json_decode($response, true);

	\OCP\Util::writeLog('Gsync', 'Autosync session.' . $response, \OCP\Util::DEBUG);
	Contact::import(@$respData['access_token']);
}

exit();