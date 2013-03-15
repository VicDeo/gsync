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

App::initController();

//Check if we are redirected here with permanent sync request
if (isset($_GET['code'])){
	$code = preg_replace('/[^0-9a-zA-Z\.\-_\/]*/i', '', $_GET['code']);
	$response = Request::getTokenByCode($code);
	$respData = json_decode($response, true);
	App::log('Goglesaid.' . $response, \OCP\Util::DEBUG);
	if (@$respData[App::GOOGLE_REFRESH_TOKEN]){
		App::setRefreshToken($respData[App::GOOGLE_REFRESH_TOKEN]);
	}

	//Dirty hack. 
	//It's not possible to redirect with server headers at this point
	echo '<script>window.location="' . \OCP\Util::linkTo('settings', 'personal.php') . '";</script>';
	exit();

} else {
	\OCP\Util::addScript(App::APP_ID, 'sync');
	$tmpl = new \OCP\Template(App::APP_ID, 'index', 'user');
	$tmpl->printPage();

}