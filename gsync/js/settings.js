/**
 * ownCloud - gsync plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

$(document).ready(function(){
	$('#gsync_client_id').blur(function(event){
		event.preventDefault();
		OC.msg.startSaving('#gsyncform .msg');
		$( "#gsync_client_id" ).val($( "#gsync_client_id" ).val().replace(/[^0-9a-zA-Z\.-]*/, ''));
		var post = {
			gsync_client_id : $( "#gsync_client_id" ).val()
		};
		$.post( OC.filePath('gsync', 'ajax', 'settings.php'), post, function(data){
			var disabled = !$("#gsync_client_id").val();
			$('#gsync_import').attr('disabled', (disabled ? 'disabled' : false));
			OC.msg.finishedSaving('#gsyncform .msg', data);
			disabled ? $('#gsyncform div').hide() : $('#gsyncform div').show();
		});
	});
	$('#gsync_import').click(function(event){
		event.preventDefault();
		gsync_url = gsync_url.replace(/client_id=[^&]*/, 'client_id=' + $( "#gsync_client_id" ).val());
		window.location = gsync_url;
	});
	
	$('#gsync_secret').blur(function(event){
		event.preventDefault();
		OC.msg.startSaving('#gsyncform .msg-2');
		$( "#gsync_secret" ).val($("#gsync_secret").val().replace(/[^0-9a-zA-Z\.-_]*/, ''));
		var post = {
			gsync_secret : $( "#gsync_secret" ).val()
		};
		$.post( OC.filePath('gsync', 'ajax', 'settings.php'), post, function(data){
			var disabled = !$("#gsync_secret").val();
			$('#gsync_autosync').attr('disabled', (disabled ? 'disabled' : false));
			OC.msg.finishedSaving('#gsyncform .msg-2', data);
		});
	});

	$('#gsync_autosync').click(function(event){
		event.preventDefault();
		gsync_perm_url = gsync_perm_url.replace(/client_id=[^&]*/, 'client_id=' + $( "#gsync_client_id" ).val());
		window.location = gsync_perm_url;
	});
	$('#gsync_revoke').click(function(event){
		event.preventDefault();
		$.post( OC.filePath('gsync', 'ajax', 'settings.php'), 
			{revoke_token : "true"},
			function(data){
				window.location.reload();
			}
		);
	});

});