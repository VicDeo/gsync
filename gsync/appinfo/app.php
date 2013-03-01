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
OC::$CLASSPATH['OCA_Gsync\Contact'] = 'gsync/lib/contact.php';
OC::$CLASSPATH['OCA_Gsync\Request'] = 'gsync/lib/request.php';
OC::$CLASSPATH['OCA_Gsync\Adapter'] = 'gsync/lib/adapter.php';

OCP\App::registerPersonal('gsync', 'settings');

