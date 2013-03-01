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
namespace OCA_Gsync;

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('contacts');
\OCP\JSON::checkAppEnabled('gsync');

Contact::import(@$_POST['access_token']);

\OCP\JSON::success(array());
