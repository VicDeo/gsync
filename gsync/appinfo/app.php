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
	const GOOGLE_CLIENT_ID = 'gsync_client_id';
	const GOOGLE_SECRET = 'gsync_secret';
	const GOOGLE_REFRESH_TOKEN = 'refresh_token';


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
	
	public static function initController(){
		\OCP\User::checkLoggedIn();
		\OCP\App::checkAppEnabled('contacts');
		\OCP\App::checkAppEnabled(self::APP_ID);
	}
	
	public static function getClientId(){
		return self::getUserValue(self::GOOGLE_CLIENT_ID);
	}
	
	public static function setClientId($clientId){
		$value = self::cleanValue($clientId);
		self::setUserValue(self::GOOGLE_CLIENT_ID, $value);
	}

	public static function getSecret(){
		return self::getUserValue(self::GOOGLE_SECRET);
	}
	
	public static function setSecret($secret){
		$value = self::cleanValue($secret);
		self::setUserValue(self::GOOGLE_SECRET, $value);
	}
	
	public static function getRedirectUri(){
		return \OCP\Util::linkToAbsolute(self::APP_ID, 'index.php');
	}

	public static function getRefreshToken(){
		return self::getUserValue(self::GOOGLE_REFRESH_TOKEN);
	}
	
	public static function setRefreshToken($token){
		$value = self::cleanValue($token);
		self::setUserValue(self::GOOGLE_REFRESH_TOKEN, $value);
	}
	
	protected static function getUserValue($key, $default=''){
		return \OCP\Config::getUserValue(\OCP\User::getUser(), self::APP_ID, $key, $default);
	}
	
	protected static function setUserValue($key, $value){
		return \OCP\Config::setUserValue(\OCP\User::getUser(), self::APP_ID, $key, $value);
	}

	protected static function cleanValue($value){
		return preg_replace('/[^0-9a-zA-Z\.\-_]*/i', '', $value);
	}
}

App::init();
