<?php

/**
 * ownCloud - gsync plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * 
 * 
 * Request Google services
 * 
 * @package Gsync
 */

namespace OCA_Gsync;

class Request {
	/**
	 * The name we provide to Google
	 */
	const USERAGENT = "ownCloud Sync App google-api-php-client/0.5.0";

	/**
	 * Url to deal with tokens
	 */
	const TOKEN_REQUEST = 'https://accounts.google.com/o/oauth2/token';

	/**
	 * Url to revoke access token
	 */
	const TOKEN_REVOKE = 'https://accounts.google.com/o/oauth2/revoke?token=';
	
	/**
	 * Url to request Contact list
	 */
	const CONTACT_REQUEST = "https://www.google.com/m8/feeds/contacts/default/full?alt=json&v=3";

	/**
	 * Url to request group details
	 */
	const GROUP_REQUEST = "https://www.google.com/m8/feeds/groups/default/base/";

	/**
	 * Number of contacts to request
	 */
	const MAX_CONTACTS = 1500;

	/**
	 * Request url
	 * @var string
	 */
	private static $_url;

	/**
	 * Headers to send
	 * @var array 
	 */
	private static $_headers = array();

	/**
	 * Request method
	 * @var string
	 */
	private static $_method = 'GET';

	/**
	 * Params for POST request
	 * @var array
	 */
	private static $_postParams = array();
	
	/**
	 * Store all responses to group requests here
	 * @var array
	 */
	private static $_groupCache = array();

	/**
	 * Init the access token
	 * @param string $accessToken 
	 */
	public static function setAccessToken($accessToken) {
		self::$_headers = array(
			"authorization" => "Bearer " . $accessToken
		);
	}

	/**
	 * Request Refresh token by code
	 * @param string $code
	 * @return string 
	 */
	public static function getTokenByCode($code){
		self::$_url = self::TOKEN_REQUEST;
		self::$_method = 'POST';
		self::$_headers =array(
			'Content-Type' => 'application/x-www-form-urlencoded'
			);
		self::$_postParams = array(
			'code' => $code,
			'client_id' => \OCP\Config::getUserValue(\OCP\User::getUser(), 'gsync', 'client_id', ''),
			'client_secret' => \OCP\Config::getUserValue(\OCP\User::getUser(), 'gsync', 'secret', ''),
			'redirect_uri' => \OCP\Util::linkToAbsolute('gsync', 'index.php'),
			'grant_type' => 'authorization_code'
		);
		return self::_execute();
	}
	
	/**
	 * Request Access token by Refresh token
	 * @param string $refreshToken
	 * @return string 
	 */
	public static function getAccessTokenByRefreshToken($refreshToken){
		self::$_url = self::TOKEN_REQUEST;
		self::$_method = 'POST';
		self::$_headers =array(
			'Content-Type' => 'application/x-www-form-urlencoded'
			);
		self::$_postParams = array(
			'refresh_token' => $refreshToken,
			'client_id' => \OCP\Config::getUserValue(\OCP\User::getUser(), 'gsync', 'client_id', ''),
			'client_secret' => \OCP\Config::getUserValue(\OCP\User::getUser(), 'gsync', 'secret', ''),
			'grant_type' => 'refresh_token'
		);
		return self::_execute();
	}
	
	/**
	 * Dispose Refresh token
	 * @param string $refreshToken
	 * @return string
	 */
	public static function revokeRefreshToken($refreshToken){
		return self::_doGet(self::TOKEN_REVOKE . $refreshToken);
	}

	/**
	 * Request contacts
	 * @return array
	 */
	public static function getContactsFeed() {
		return self::_doGet(self::CONTACT_REQUEST . '&max-results=' . self::MAX_CONTACTS);
	}
	
	/**
	 * Request contact image
	 * @param string $imageUrl
	 * @return string 
	 */
	public static function getContactImage($imageUrl) {
		return self::_doGet($imageUrl);
	}

	/**
	 * Request group details
	 * @param string $groupId
	 * @return string 
	 */
	public static function getGroupDetails($groupId) {
		if (!isset(self::$_groupCache[$groupId])) {
			self::$_groupCache[$groupId] = self::_doGet(self::GROUP_REQUEST . $groupId . '?v=3');
		}

		return self::$_groupCache[$groupId];
	}
	
	/**
	 * Perform Get request
	 * @param string $url
	 * @return string
	 */
	private static function _doGet($url){
		self::$_url = $url;
		self::$_method = 'GET';
		self::_cleanHeaders();
		return self::_execute();
	}
	
	/**
	 * Unset request headers preserving authorization
	 */
	private static function _cleanHeaders(){
		$accessToken = @self::$_headers['authorization'];
		if ($accessToken){
			self::$_headers = array('authorization' => $accessToken);
		} else {
			self::$_headers = array();
		}
	}
	
	/**
	 * Perform a request
	 * @return string
	 */
	private static function _execute() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$_url);

		$parsedHeaders = array();
		foreach (self::$_headers as $k => $v) {
			$parsedHeaders[] = "$k: $v";
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $parsedHeaders);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::$_method);
		
		if (self::$_method == 'POST' && count(self::$_postParams)){
			$paramStr = '';
			foreach (self::$_postParams as $key=>$value){
				$paramStr .= $key."=". \urlencode($value)."&";
			}
			$paramStr = substr($paramStr,0,-1);
			
			curl_setopt($ch, CURLOPT_POSTFIELDS, $paramStr);
		}
		
		curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$response = curl_exec($ch);

		// Retry if certificates are missing.
		if (curl_errno($ch) == CURLE_SSL_CACERT) {
			error_log('SSL certificate problem, verify that the CA cert is OK.'
					. ' Retrying with the CA cert bundle from google-api-php-client.');
			curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cacert.pem');
			$response = curl_exec($ch);
		}

		$error = curl_errno($ch);
		if ($error) {
			\OCP\Util::writeLog('Gsync', 'Curl reports the error: ' . curl_error($ch), \OCP\Util::WARN);
		}

		curl_close($ch);

		return $response;
	}

}