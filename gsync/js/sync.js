/**
 * ownCloud - gsync plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

var Request = {
	getContacts : function(){
		try {
			$.post(OC.filePath('gsync', 'ajax', 'sync.php'), 
				{access_token : this.getToken()}, 
				function() {
					window.location = OC.filePath('contacts', '', 'index.php'); 
				}
			);
		} catch (e) {
			console.log(e);
		}		
	},
	
	getToken : function(){
		if (this._token){
			return this._token;
		}
		var accessToken = false;
		var tokenParts = window.location.href.match(/access_token=([^&]*)/);
		if (tokenParts && tokenParts[1]){
			accessToken = tokenParts[1];
		}
		this._token = accessToken; 
		return accessToken;
	},
	
	getCode : function(){
		if (this._code){
			return this._code;
		}
		var code = false;
		var codeParts = window.location.href.match(/code=([^&]*)/);
		if (codeParts && codeParts[1]){
			code = codeParts[1];
		}
		this._code = code;
		return code;
	}
};

$(document).ready( function (){
	$('#gsync-splash').css({
		'position': 'absolute',
		'zIndex' : 100,
		'top' : 0,
		'left' : 0,
		'height' : '100%',
		'width' : '100%',
		'backgroundColor' : '#333',
		'opacity' : '0.3'
	});
	$('#gsync-loader').css({
		'position': 'absolute',
		'zIndex' : 110,
		'top' : '50%',
		'left' : '50%'
	})
	$('#gsync-loader').show();
	if (Request.getToken()){
		Request.getContacts();
	} else {

	}
});


/* TODO: Add support of the rels below */
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
