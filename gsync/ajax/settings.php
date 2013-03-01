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

App::initAjaxController();

$l = new \OC_L10N('core');

// Get data
if (isset($_POST[App::GOOGLE_CLIENT_ID])){
	App::setClientId($_POST[App::GOOGLE_CLIENT_ID]);
	
} elseif (isset($_POST[App::GOOGLE_SECRET])){
	App::setSecret($_POST[App::GOOGLE_SECRET]);
	
} elseif (isset($_POST[App::GOOGLE_REFRESH_TOKEN])) {
	$token = App::getRefreshToken();
	Request::revokeRefreshToken($token);
	App::setRefreshToken('');
	
} else {
	\OCP\JSON::error(array("data" => array("message" => $l->t("Invalid request"))));
}

\OCP\JSON::success(array("data" => array("message" => $l->t("Saved"))));