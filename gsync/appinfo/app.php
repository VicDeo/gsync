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

class App {

	const APP_ID = 'gsync';

	public static function init(){
		\OC::$CLASSPATH['OCA_Gsync\Contact'] = self::APP_ID . '/lib/contact.php';
		\OC::$CLASSPATH['OCA_Gsync\Request'] = self::APP_ID . '/lib/request.php';
		\OC::$CLASSPATH['OCA_Gsync\Adapter'] = self::APP_ID . '/lib/adapter.php';
		
		\OCP\App::registerPersonal(self::APP_ID, 'settings');
	}
	
	public static function initAjaxController(){
		\OCP\JSON::checkLoggedIn();
		\OCP\JSON::callCheck();
		\OCP\JSON::checkAppEnabled('contacts');
		\OCP\JSON::checkAppEnabled(self::APP_ID);
	}

}

App::init();
