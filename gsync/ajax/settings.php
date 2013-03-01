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
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');
OCP\JSON::checkAppEnabled('gsync');

$l = new OC_L10N('core');

// Get data
if (isset($_POST['gsync_client_id'])) {
	$client_id = preg_replace('/[^0-9a-zA-Z\.\-_]*/i', '', $_POST['gsync_client_id']);
	OCP\Config::setUserValue(OCP\User::getUser(), 'gsync', 'client_id', $client_id);
} elseif (isset($_POST['gsync_secret'])) {
	$appSecret = preg_replace('/[^0-9a-zA-Z\.\-_]*/i', '', $_POST['gsync_secret']);
	OCP\Config::setUserValue(OCP\User::getUser(), 'gsync', 'secret', $appSecret);
} elseif (isset($_POST['revoke_token'])) {
	$token = OCP\Config::getUserValue(OCP\User::getUser(), 'gsync', 'refresh_token', '');
	OCA_Gsync\Request::revokeRefreshToken($token);
	OCP\Config::setUserValue(OCP\User::getUser(), 'gsync', 'refresh_token', '');
} else {
	OCP\JSON::error(array("data" => array("message" => $l->t("Invalid request"))));
}

OCP\JSON::success(array("data" => array("message" => $l->t("Saved"))));