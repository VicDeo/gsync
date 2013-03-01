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
 * Change contact format
 * 
 * @package Gsync
 */

namespace OCA_Gsync;

class Adapter {
	/*
	 *  Constants from http://code.google.com/apis/gdata/elements.html 
	 */

	const REL_HOME = 'http://schemas.google.com/g/2005#home';
	const REL_HOME_FAX = 'http://schemas.google.com/g/2005#home_fax';
	const REL_WORK = 'http://schemas.google.com/g/2005#work';
	const REL_WORK_FAX = 'http://schemas.google.com/g/2005#work_fax';
	const REL_WORK_MOBILE = 'http://schemas.google.com/g/2005#work_mobile';
	const REL_WORK_PAGER = 'http://schemas.google.com/g/2005#work_pager';
	const REL_OTHER = 'http://schemas.google.com/g/2005#other';
	const REL_CAR = 'http://schemas.google.com/g/2005#car';
	const REL_FAX = 'http://schemas.google.com/g/2005#fax';
	const REL_GENERAL = 'http://schemas.google.com/g/2005#general';
	const REL_INTERNAL = 'http://schemas.google.com/g/2005#internal-extension';
	const REL_MOBILE = 'http://schemas.google.com/g/2005#mobile';
	const REL_PAGER = 'http://schemas.google.com/g/2005#pager';
	const REL_SATELLITE = 'http://schemas.google.com/g/2005#satellite';
	const REL_VOIP = 'http://schemas.google.com/g/2005#voip';
	const REL_MAIN = 'http://schemas.google.com/g/2005#main';
	const REL_ASSISTANT = 'http://schemas.google.com/g/2005#assistant';
	const REL_CALLBACK = 'http://schemas.google.com/g/2005#callback';
	const REL_COMPANY_MAIN = 'http://schemas.google.com/g/2005#company_main';
	const REL_ISDN = 'http://schemas.google.com/g/2005#isdn';
	const REL_OTHER_FAX = 'http://schemas.google.com/g/2005#other_fax';
	const REL_RADIO = 'http://schemas.google.com/g/2005#radio';
	const REL_TELEX = 'http://schemas.google.com/g/2005#telex';
	const REL_TTY_TDD = 'http://schemas.google.com/g/2005#tty_tdd';
	const PHOTO_LINK_REL = 'http://schemas.google.com/contacts/2008/rel#photo';
	const REL_DEFAULT = 'REL_DEFAULT';

	/**
	 * Adapt Google contact to VCard 
	 * @param array $contact
	 * @return array 
	 */
	public static function translateContact($contact) {
		$entry = self::translatePropertyStrings($contact);
		$entry[Contact::CONTACT_EMAIL] = self::translateEmails($contact);
		$entry[Contact::CONTACT_PHONE] = self::translatePhones($contact);
		$entry[Contact::CONTACT_ADDRESS] = self::translateAddress($contact);
		$entry[Contact::CONTACT_PHOTO] = self::translatePhoto($contact);
		$entry[Contact::CONTACT_CATEGORIES] = self::translateCategories($contact);

		return $entry;
	}

	/**
	 * Adapt all string properties in one go
	 * https://developers.google.com/google-apps/contacts/v3/#contact_entry
	 * API 3.0 is backward compatible with 2.0 
	 * Organization
	 * https://developers.google.com/gdata/docs/2.0/elements#gdOrganization 
	 */
	public static function translatePropertyStrings($contact) {
		$name = '';
		$nameParts = array('gd$familyName', 'gd$givenName', 'gd$additionalName', 'gd$namePrefix', 'gd$nameSuffix');
		foreach ($nameParts as $part) {
			$name .= @$contact['gd$name'][$part]['$t'] . ';';
		}

		$fullname = @$contact['gd$name']['gd$fullName']['$t'] ? @$contact['gd$name']['gd$fullName']['$t'] : "unknown";

		/* NOTE: We shorten the result to fit one line  */
		$googId = @$contact['id']['$t'] ? str_replace('http://www.google.com/m8/feeds/contacts/', '', @$contact['id']['$t']) : '';

		return array(
			Contact::CONTACT_FULLNAME => $fullname,
			Contact::CONTACT_NAME => $name,
			Contact::CONTACT_GID => $googId,
			Contact::CONTACT_NICKNAME => @$contact['gContact$nickname']['$t'],
			Contact::CONTACT_ORGANIZATION => @$contact['gd$organization'][0]['gd$orgName']['$t'],
			Contact::CONTACT_BIRTHDAY => @$contact['gContact$birthday']['when'],
			Contact::CONTACT_NOTE => @$contact['content']['$t']
		);
	}

	/**
	 * Translate contacts into Emails array
	 * @param array $contact
	 * @return array
	 * https://developers.google.com/gdata/docs/2.0/elements#gdEmail
	 */
	public static function translateEmails($contact) {

		$rels = array(
			self::REL_HOME => 'HOME',
			self::REL_WORK => 'WORK',
			self::REL_DEFAULT => 'HOME'
		);

		$emails = array();

		if (is_array(@$contact['gd$email'])) {
			foreach ($contact['gd$email'] as $email) {
				if (!@$email['address']) {
					continue;
				}

				$type = array_key_exists(@$email['rel'], $rels) ? $rels [@$email['rel']] : $rels[self::REL_DEFAULT];
				$emails[] = array(
					'value' => @$email['address'],
					'type' => $type
				);
			}
		}
		return $emails;
	}

	/*
	 * Phones
	 * API 3.0 is backward compatible with 2.0 
	 * https://developers.google.com/gdata/docs/2.0/elements#gdPhoneNumber
	 */

	public static function translatePhones($contact) {

/*
CELL
WORK
TEXT
VOICE
MSG
FAX
VIDEO
PAGER
 */		
		
		$rels = array(
			self::REL_HOME => array ('HOME'),
			self::REL_HOME_FAX => array('HOME', 'FAX'),
			self::REL_WORK => array ('WORK'),
			self::REL_WORK_FAX => array('WORK', 'FAX'),
			self::REL_WORK_MOBILE => array('WORK', 'CELL'),
			self::REL_WORK_PAGER => array('WORK', 'PAGER'),
			self::REL_CAR => array('HOME'), //NB!
			self::REL_FAX => array('FAX'),
			self::REL_GENERAL => array('HOME'), //NB!
			self::REL_INTERNAL => array('HOME'), //NB!
			self::REL_MOBILE => array('CELL'),
			self::REL_PAGER => array('PAGER'), 
			self::REL_SATELLITE => array('CELL'), // //NB!
			self::REL_VOIP => array('VOICE'),
			self::REL_MAIN => array('HOME'), //NB!
			self::REL_ASSISTANT => array('MSG'),  //NB!
			self::REL_CALLBACK => array('MSG'),  //NB!
			self::REL_COMPANY_MAIN => array('WORK'),  //NB!
			self::REL_ISDN => array('VOIP'),  //NB!
			self::REL_OTHER_FAX => array('FAX'),  //NB!
			self::REL_RADIO => array('HOME'),  //NB!
			self::REL_TELEX => array('TEXT'), 
			self::REL_TTY_TDD => array('TEXT'),
			self::REL_OTHER => array('HOME'),  //NB!
			
			self::REL_DEFAULT => array ('HOME')
		);
		
		$phones = array();

		if (is_array(@$contact['gd$phoneNumber'])) {
			foreach ($contact['gd$phoneNumber'] as $phone) {
				if (!@$phone['$t']) {
					continue;
				}

				$type = array_key_exists(@$phone['rel'], $rels) ? $rels [@$phone['rel']] : $rels[self::REL_DEFAULT];
				$phones[] = array(
					'value' => @$phone['$t'],
					'type' => $type
				);
			}
		}
		return $phones;
	}

	/*
	 * Photo
	 * https://developers.google.com/google-apps/contacts/v3/#retrieving_a_contacts_photo
	 */

	public static function translatePhoto($contact) {
		if (is_array(@$contact['link'])) {
			foreach ($contact['link'] as $link) {
				if (isset($link['gd$etag']) && @$link['rel'] == self::PHOTO_LINK_REL) {
					return $link['href'];
				}
			}
		}

		return;
	}

	/**
	 * Postal addresses
	 * https://developers.google.com/google-apps/contacts/v3/#contact_entry
	 * https://developers.google.com/gdata/docs/2.0/elements#gdStructuredPostalAddress
	 */
	public static function translateAddress($contact) {
		$rels = array(
			self::REL_HOME => 'HOME',
			self::REL_WORK => 'WORK',
			self::REL_DEFAULT => 'HOME'
		);

		$postal = array();
		if (is_array(@$contact['gd$structuredPostalAddress'])) {
			foreach ($contact['gd$structuredPostalAddress'] as $address) {
				$type = array_key_exists(@$address['rel'], $rels) ? $rels [$address['rel']] : $rels[self::REL_DEFAULT];
				$postal[] = array(
					'pobox' => '',
					'extra' => '',
					'street' => @$address['gd$street']['$t'],
					'city' => @$address['gd$city']['$t'],
					'region' => @$address['gd$region']['$t'],
					'zip' => @$address['gd$postcode']['$t'],
					'country' => @$address['gd$country']['$t'],
					'type' => $type
				);
			}
		}

		return $postal;
	}

	public static function translateCategories($contact) {
		$categories = array();

		if (is_array(@$contact['gContact$groupMembershipInfo'])) {
			foreach ($contact['gContact$groupMembershipInfo'] as $group) {
				preg_match('/[^\/]*$/', @$group['href'], $groupId);
				if (@$groupId[0]) {
					$categories[] = $groupId[0];
				}
			}
		}

		return $categories;
	}

}

/* TODO: Add support of the rels below 
IM_AIM = 'http://schemas.google.com/g/2005#AIM';
IM_MSN = 'http://schemas.google.com/g/2005#MSN';
IM_YAHOO = 'http://schemas.google.com/g/2005#YAHOO';
IM_SKYPE = 'http://schemas.google.com/g/2005#SKYPE';
IM_QQ = 'http://schemas.google.com/g/2005#QQ';
IM_GOOGLE_TALK = 'http://schemas.google.com/g/2005#GOOGLE_TALK';
IM_ICQ = 'http://schemas.google.com/g/2005#ICQ';
IM_JABBER = 'http://schemas.google.com/g/2005#JABBER';
IM_NETMEETING = 'http://schemas.google.com/g/2005#netmeeting';

PHOTO_EDIT_LINK_REL = 'http://schemas.google.com/contacts/2008/rel#edit-photo';
*/
