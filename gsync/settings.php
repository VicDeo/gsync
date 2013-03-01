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
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('contacts');
OCP\App::checkAppEnabled('gsync');

OCP\Util::addScript('gsync', 'settings');

$info = OCP\App::getAppInfo('gsync');

$clientID = OCP\Config::getUserValue(OCP\User::getUser(), 'gsync', 'client_id', '');

//fallback to the old config parameter
if (empty($clientID)){
	$legacy = OC_Preferences::getValue(OCP\User::getUser(), 'settings', 'gsync_client_id', '');
	if ($legacy){
		OCP\Config::setUserValue(OCP\User::getUser(), 'gsync', 'client_id', $legacy);
		OC_Preferences::setValue(OCP\User::getUser(), 'settings', 'gsync_client_id', '');
	}
	
}

$appSecret = OCP\Config::getUserValue(OCP\User::getUser(), 'gsync', 'secret', '');

$tmpl = new OCP\Template('gsync', 'settings');
$tmpl->assign('app_version', $info['version']);

$tmpl->assign('gsync_redirect', OCP\Util::linkToAbsolute('gsync', 'index.php'));
$tmpl->assign('gsync_client_id', OCP\Config::getUserValue(OCP\User::getUser(), 'gsync', 'client_id', ''));
$tmpl->assign('gsync_secret', $appSecret);

$tmpl->assign('gsync_refresh_token', OCP\Config::getUserValue(OCP\User::getUser(), 'gsync', 'refresh_token', ''));

return $tmpl->fetchPage();
