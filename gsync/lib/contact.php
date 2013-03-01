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
 * Store Contact details
 * 
 * @package Gsync
 */

namespace OCA_Gsync;

class Contact {
	//Do not sync contacts that was synced in last 10 minutes

	const UPDATE_THRESHOLD = 600;
	const CONTACT_FULLNAME = 'FN';
	const CONTACT_NAME = 'N';
	const CONTACT_NICKNAME = 'NICKNAME';
	const CONTACT_ORGANIZATION = 'ORG';
	const CONTACT_EMAIL = 'EMAIL';
	const CONTACT_PHONE = 'TEL';
	const CONTACT_ADDRESS = 'ADR';
	const CONTACT_BIRTHDAY = 'BDAY';
	const CONTACT_PHOTO = 'PHOTO';
	const CONTACT_CATEGORIES = 'CATEGORIES';
	const CONTACT_NOTE = 'NOTE';
	const CONTACT_GID = 'GID';

	private static $_propertyStrings = array(
		self::CONTACT_NAME,
		self::CONTACT_FULLNAME,
		self::CONTACT_GID,
		self::CONTACT_NICKNAME,
		self::CONTACT_ORGANIZATION,
		self::CONTACT_BIRTHDAY,
		self::CONTACT_NOTE
	);
	private static $_bookId = null;

	public static function import($accessToken) {
		if (!$accessToken){
			\OCP\Util::writeLog('Gsync', 'Import attempt. Access token is empty', \OCP\Util::DEBUG);
			return;
		}
		Request::setAccessToken($accessToken);

		\OCP\Util::writeLog('Gsync', 'Firing contacts request', \OCP\Util::DEBUG);

		$respData = Request::getContactsFeed();
		$feed = json_decode($respData, true);
		$feed = @$feed['feed']['entry'];

		\OCP\Util::writeLog('Gsync', 'Got response from Google. Items count: ' . count($feed), \OCP\Util::DEBUG);

		if (is_array($feed) && count($feed)) {
			self::parseFeed($feed);
		}
	}

	public static function parseFeed($feed) {
		$userid = \OCP\User::getUser();

		foreach ($feed as $source) {
			
			$entry = Adapter::translateContact($source);

			if (isset($entry[self::CONTACT_GID]) && !empty($entry[self::CONTACT_GID])) {
				$oldContactId = self::findByGid($userid, $entry[self::CONTACT_GID]);
				if ($oldContactId) {
					//If exists and should not be updated - skip
					if (self::needUpdate($oldContactId)) {
						$vcard = self::toVcard($entry);
						\OC_Contacts_VCard::edit($oldContactId, $vcard);
					}
					continue;
				}
			}
			
			$vcard = self::toVcard($entry);
			$bookid = self::getBookId($userid);
			\OC_Contacts_VCard::add($bookid, $vcard);
		}
		\OC_Contacts_App::getCategories();
	}

	/**
	 * Init address book Id to save new Contacts
	 * @param string $userid
	 * @return int bookId
	 */
	public static function getBookId($userid) {
		if (!self::$_bookId) {
			self::$_bookId = \OC_Contacts_Addressbook::add($userid, 'sync on ' . date('jS \of F Y h:i:s A'), null);
			\OC_Contacts_Addressbook::setActive(self::$_bookId, 1);
		}
		return self::$_bookId;
	}

	/**
	 * Check if the contact already exists and has't updated within threshold time.
	 * @param type $userid
	 * @param type $gid
	 * @return int contactId
	 */
	public static function findByGid($userid, $gid) {
		$stmt = \OC_DB::prepare("SELECT cc.id FROM *PREFIX*contacts_cards AS cc
			INNER JOIN *PREFIX*contacts_addressbooks AS ca ON cc.addressbookid=ca.id AND ca.userid = ? WHERE cc.carddata LIKE ?");
		$result = $stmt->execute(array($userid, "%$gid%"));

		return $result->fetchOne();
	}

	/**
	 * Check if the Contact hasn't been modified recently
	 * @param int $contactId
	 * @return bool
	 */
	public static function needUpdate($contactId) {
		$modifiedBefore = time() - self::UPDATE_THRESHOLD;
		$stmt = \OC_DB::prepare("SELECT count(id) FROM *PREFIX*contacts_cards WHERE id=? AND lastmodified<?");
		$result = $stmt->execute(array($contactId, $modifiedBefore));
		return $result->fetchOne();
	}

	/**
	 * Parse contact details into the Contact object
	 * @param array $contact
	 * @return OC_VObject 
	 */
	public static function toVcard($contact) {
		$vcard = new \OC_VObject('VCARD');
		$vcard->setUID();

		$vcard = self::_addPropertyStrings($contact, $vcard);

		if (isset($contact[self::CONTACT_PHONE])) {
			foreach ($contact[self::CONTACT_PHONE] as $phone) {
				if (!isset($phone['value'])) {
					continue;
				}

				$vcard->addProperty(self::CONTACT_PHONE, $phone['value']);
				$line = count($vcard->children) - 1;
				foreach ($phone['type'] as $type) {
					$vcard->children[$line]->parameters[] = new \Sabre_VObject_Parameter('TYPE', $type);
				}
			}
		}

		if (isset($contact[self::CONTACT_CATEGORIES])) {
			$categories = array();
			foreach ($contact[self::CONTACT_CATEGORIES] as $categoryId) {

				$categoryData = Request::getGroupDetails($categoryId);
				preg_match('/<title>(.*)<\/title>/i', $categoryData, $matches);
				if (@$matches[1]) {
					$categories[] = $matches[1];
				}
			}
			if (count($categories)) {
				$vcard->setString(self::CONTACT_CATEGORIES, implode(',', $categories));
			}
		}

		if (isset($contact[self::CONTACT_ADDRESS])) {
			foreach ($contact[self::CONTACT_ADDRESS] as $address) {
				$vcard->addProperty(self::CONTACT_ADDRESS, $address);
				$line = count($vcard->children) - 1;
				$vcard->children[$line]->parameters[] = new \Sabre_VObject_Parameter('TYPE', $address['type']);
			}
		}

		if (isset($contact[self::CONTACT_EMAIL])) {
			foreach ($contact[self::CONTACT_EMAIL] as $email) {
				$vcard->addProperty(self::CONTACT_EMAIL, $email['value']);
				  $line = count($vcard->children) - 1;
				  $vcard->children[$line]->parameters[] = new \Sabre_VObject_Parameter('TYPE', $email['type']);
			}
		}

		if (isset($contact[self::CONTACT_PHOTO]) && !empty($contact[self::CONTACT_PHOTO])) {
			$data = Request::getContactImage($contact[self::CONTACT_PHOTO]);
			$img = new \OC_Image();
			if ($img->loadFromData($data)) {
				$vcard->addProperty(self::CONTACT_PHOTO, $img->__toString(), array('ENCODING' => 'b', 'TYPE' => $img->mimeType()));
			} else {
				\OCP\Util::writeLog('Gsync', 'Unable to parse the image provided by Google. ', \OCP\Util::WARN);
			}
		}

		return $vcard;
	}

	/**
	 * Fill the string properties in a one go
	 * @param array $contact
	 * @param OC_VObject $vcard
	 * @return OC_VObject
	 */
	protected static function _addPropertyStrings($contact, $vcard) {

		foreach (self::$_propertyStrings as $property) {
			if (isset($contact[$property])) {
				$vcard->setString($property, $contact[$property]);
			}
		}
		return $vcard;
	}

}