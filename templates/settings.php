<?php
/**
 * Copyright (c) 2012 Victor Dubiniuk <victor.dubiniuk@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */
?>
<?php $cid = $_[OCA_Gsync\App::GOOGLE_CLIENT_ID] ?>
<?php $secret = $_[OCA_Gsync\App::GOOGLE_SECRET] ?>
<?php $isReady = !empty($cid) ?>
<?php $redirect = $_['gsync_redirect'] ?>

<form id="gsyncform">
    <fieldset class="personalblock">
	<strong>Google Sync v<?php echo $_['app_version']; ?></strong>
	<?php if (!in_array('curl', get_loaded_extensions())) { ?>
		<span class="bold"><?php echo $l->t('The curl-php extension is required for this app to work.') ?></span>
	<?php } else { ?>
		<br />
		<span class="bold"><?php echo $l->t('Your Redirect URI') ?>:</span> <?php echo $redirect ?>
		<br />
		<a target="_blank" href="https://code.google.com/apis/console">
			<?php echo $l->t('Create a new OAuth 2.0 Web Application') ?>
		</a>
		<br />
		<label><strong>Client ID:</strong></label>
		<input type="text" id="gsync_client_id" value="<?php echo $cid ?>" placeholder="<?php echo $l->t('App Client id') ?>" />
		<button id="gsync_import" <?php if (!$isReady) { ?>disabled="disabled"<?php } ?>>
			<?php echo $l->t('Import') ?>
		</button>
		<span class="msg"></span>
		<br />
		<input type="hidden" id="GSYNC_URL_JS" value="https://accounts.google.com/o/oauth2/auth?client_id=<?php echo $cid ?>&response_type=token&scope=https://www.google.com/m8/feeds&redirect_uri=<?php echo $redirect ?>">
		<input type="hidden" id="GSYNC_PERM_URL_JS" value="https://accounts.google.com/o/oauth2/auth?scope=https://www.google.com/m8/feeds/&state=/profile&response_type=code&client_id=<?php echo $cid ?>&access_type=offline&redirect_uri=<?php echo $redirect ?>">
		<div <?php echo ($isReady ? '' : 'style="display:none"');  ?>>
			<hr />
			<strong><?php echo $l->t('Autosync') ?></strong><br />
			<label><strong><?php echo $l->t('App Secret') ?>:</strong></label>
			<input type="text" id="gsync_secret" value="<?php echo $secret ?>" placeholder="<?php echo $l->t('App Secret') ?>" />
        <?php if ( empty($_[OCA_Gsync\App::GOOGLE_REFRESH_TOKEN])){ ?> 
		    <button <?php if (empty($secret)) { ?>disabled="disabled"<?php } ?> id="gsync_autosync" name="gsync_autosync">
				<?php echo $l->t('Request autosync permissions') ?>
			</button>
			<span class="msg-2"></span> 
			<?php } else { ?>
				<button id="gsync_revoke"><?php echo $l->t('Revoke Permissions'); ?></button>
			<?php } ?>
		</div>
	<?php } ?>
    </fieldset>
</form>

<?php /*
 * revoke: https://accounts.google.com/b/0/IssuedAuthSubTokens  
 */ ?>
