<?php

/**
 * ownCloud - gsync plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('contacts');
OCP\App::checkAppEnabled('gsync');

//Check if we are redirected here with permanent sync request
if (isset($_GET['code'])){   
	$code = preg_replace('/[^0-9a-zA-Z\.\-_\/]*/i', '', $_GET['code']);
	$response = OCA_Gsync\Request::getTokenByCode($code);
	$gogelSaid = json_decode($response, true);
	OCP\Util::writeLog('Gsync', 'Gogle ssaid.' . $response, OCP\Util::DEBUG);
	if (@$gogelSaid['refresh_token']){
		OCP\Config::setUserValue(OCP\User::getUser(), 'gsync', 'refresh_token', $gogelSaid['refresh_token']);
	}
	
	//Dirty hack. 
	//It's not possible to redirect with server headers at this point
	echo '<script>window.location="' . OCP\Util::linkTo('settings', 'personal.php') . '";</script>';
	exit();

} else {
	OCP\Util::addScript('gsync', 'sync');
	$tmpl = new OCP\Template('gsync', 'index', 'user');
	$tmpl->printPage();
}