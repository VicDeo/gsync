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

\OCP\Util::addScript(App::APP_ID, 'settings');
$info = \OCP\App::getAppInfo(App::APP_ID);

$tmpl = new \OCP\Template(App::APP_ID, 'settings');

$tmpl->assign(App::GOOGLE_CLIENT_ID, App::getClientId());
$tmpl->assign(App::GOOGLE_SECRET, App::getSecret());
$tmpl->assign(App::GOOGLE_REFRESH_TOKEN, App::getRefreshToken());
$tmpl->assign('gsync_redirect', App::getRedirectUri());
$tmpl->assign('app_version', @$info['version']);

return $tmpl->fetchPage();
