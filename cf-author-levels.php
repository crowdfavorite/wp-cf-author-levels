<?php
/*
Plugin Name: CF Author Levels
Plugin URI: http://crowdfavorite.com
Description: Advanced options for author levels
Version: 1.3.4
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

load_plugin_textdomain('cfum_author_lvl');

define('CFUM_VERSION', '1.3.4');

$cfum_allowedtags = array(
	'a' => array(
		'href' => array(),
		'title' => array(), 
		'target' => array(),
	),
	'abbr' => array(
		'title' => array()
	),
	'acronym' => array(
		'title' => array()
	),
	'code' => array(), 
	'pre' => array(), 
	'em' => array(),
	'strong' => array(),
	'i' => array(),
	'b' => array(),
	'p' => array()
);

/**
 * 
 * CF Author Levels Admin Handlers
 * 
 */

function cfum_menu_items() {
	if (current_user_can('manage_options')) {
		add_submenu_page(
			'users.php',
			__('CF Author Levels', 'cfum_author_lvl'),
			__('CF Author Levels', 'cfum_author_lvl'),
			10,
			basename(__FILE__),
			'cfum_check_page'
		);
	}
}
add_action('admin_menu','cfum_menu_items');

function cfum_check_page() {
	if (current_user_can('manage_options')) {
		if (isset($_GET['cfum_page'])) {
			$check_page = $_GET['cfum_page'];
		}
		else {
			$check_page = '';
		}
		switch($check_page) {
			case 'edit':
				cfum_edit_form();
				break;
			case 'descriptions':
				cfum_description_process();
				break;
			case 'main':
			default:
				cfum_options_form();
				break;
		}
	}
}

function cfum_request_handler() {
	if (current_user_can('manage_options') && !empty($_POST['cf_action'])) {
		switch($_POST['cf_action']) {
			case 'cfum_update_author_lvls':
				cfum_update_author_lvls($_POST['cfum_author_lvls']);
				wp_redirect(admin_url('users.php?page=cf-author-levels.php&cfum_page=edit&cfum_message=updated'));
				break;
			case 'cfum_update_author_lists':
				cfum_update_author_list($_POST['cfum_author_list']);
				wp_redirect(admin_url('users.php?page=cf-author-levels.php&cfum_page=main&cfum_message=updated'));
				break;
		}
	}
	
	// Add the CSS, JS and Proper Meta fields to the proper screens
	// Note that these functions need to go here since pluggable.php isn't loaded on plugins_loaded.
	if ((basename($_SERVER['SCRIPT_FILENAME']) == 'profile.php' || basename($_SERVER['SCRIPT_FILENAME']) == 'user-edit.php') && (current_user_can('unfiltered_html') || current_user_can('manage_options'))) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('cfum_admin_user_js', trailingslashit(get_bloginfo('url')).'?cf_action=cfum_admin_user_js', 'jquery', CFUM_VERSION, true);
		wp_enqueue_script('cf-author-levels-ckeditor', plugins_url('cf-author-levels/ckeditor/ckeditor.js'), 'jquery', CFUM_VERSION, true);
		wp_enqueue_style('cfum_admin_user_css', trailingslashit(get_bloginfo('url')).'?cf_action=cfum_admin_user_css', '', CFUM_VERSION, 'screen');

		add_action('edit_user_profile', 'cfum_show_user_form_fields');
		add_action('show_user_profile', 'cfum_show_user_form_fields');
	}
	else if (basename($_SERVER['SCRIPT_FILENAME']) == 'profile.php' && !current_user_can('unfiltered_html')) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('cfum_regular_user_js', trailingslashit(get_bloginfo('url')).'?cf_action=cfum_regular_user_js', 'jquery', CFUM_VERSION, true);
		wp_enqueue_style('cfum_regular_user_css', trailingslashit(get_bloginfo('url')).'?cf_action=cfum_regular_user_css', '', CFUM_VERSION, 'screen');

		add_action('show_user_profile', 'cfum_show_user_information');
	}
	else if (!empty($_GET['page']) && strpos($_GET['page'], 'cf-author-levels') !== false) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('cfum_admin_js', trailingslashit(get_bloginfo('url')).'?cf_action=cfum_admin_js', 'jquery', CFUM_VERSION);
		wp_enqueue_style('cfum_admin_css', trailingslashit(get_bloginfo('url')).'?cf_action=cfum_admin_css', '', CFUM_VERSION, 'screen');
	}
}
add_action('init','cfum_request_handler');
add_action('wp_ajax_cfum_update_settings','cfum_request_handler');

function cfum_resources_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfum_admin_js':
				cfum_admin_js();
				break;
			case 'cfum_admin_css':
				cfum_admin_css();
				break;
			case 'cfum_admin_user_js':
				cfum_admin_user_js();
				break;
			case 'cfum_admin_user_css':
				cfum_admin_user_css();
				break;
			case 'cfum_regular_user_js':
				cfum_regular_user_js();
				break;
			case 'cfum_regular_user_css':
				cfum_regular_user_css();
				break;
			case 'cfum_ckeditor_toolbar_config':
				cfum_ckeditor_toolbar_config();
				break;
		}
	}
}
add_action('init', 'cfum_resources_handler', 1);

function cfum_admin_css() {
	header('Content-type: text/css');
	?>
	#cfum-list { list-style: none; padding: 0; margin: 0; }
	#cfum-list li { margin: 0; padding: 0; }
	#cfum-list .handle { cursor: move; }
	.cfum-author-list { list-style: none; padding: 0; margin: 0; }
	.cfum-author-list li { margin: 0; padding: 0; background-color: #FFFFFF; }
	.cfum-author-list .handle { cursor: move; }
	#cfum-log { padding: 5px; border: 1px solid #ccc; }
	.cfum-info { list-style: none; }
	#cfum-description p {
		padding:5px 15px;
	}
	#cfum-description p .description{
		font-weight:bold;
	}
	.widefat tr td.cfum_handle_box {
		width: 80px;
		text-align: center;
		vertical-align: center;
	}
	.widefat tr td.cfum_delete_box {
		width: 80px;
		text-align: center;
		vertical-align: middle;
	}
	.cfum_list_name {
		clear: both;
		font: 20px Georgia, "Times New Roman", Times, serif;
		margin: 5px 0 0 -4px;
		padding: 0 280px 7px 10px;
		color: #666;
	}
	<?php
	die();
}

function cfum_admin_js() {
	header('Content-type: text/javascript');
	$cfum_author_lvls = cfum_get_levels();

	if (is_array($cfum_author_lvls)) {
		foreach ($cfum_author_lvls as $cfum_author_lvl_key => $cfum_author_lvl) {
			print('
				// When the document is ready set up our sortable with its inherant function(s)
				jQuery(document).ready(function() {
					jQuery("#cfum-list-'.$cfum_author_lvl_key.'").sortable({
						handle : ".handle",
						update : function () {
							jQuery("input#cfum-log").val(jQuery("#cfum-list-'.$cfum_author_lvl_key.'").sortable("serialize"));
						}
					});
				});
			');
		}
	}
?>
	// When the document is ready set up our sortable with its inherant function(s)
	jQuery(document).ready(function() {
		jQuery("#cfum-list").sortable({
			handle : ".handle",
			update : function () {
				jQuery("input#cfum-log").val(jQuery("#cfum-list").sortable("serialize"));
			}
		});
	});
	function addLevel() {
		var id = new Date().valueOf();
		var section = id.toString();
		var html = jQuery('#newitem_SECTION').html().replace(/###SECTION###/g, section);
		jQuery('#cfum-list').append(html);
	}
	function addUser(userKey) {
		var id = new Date().valueOf();
		var section = id.toString();
		var html = jQuery('#newitem_SECTION').html().replace(/###SECTION###/g, section).replace(/###KEY###/g, userKey);
		jQuery('#cfum-list-'+userKey).append(html);
	}
	function deleteLevel(levelID) {
		if (confirm('Are you sure you want to delete this?')) {
			jQuery('#listitem_'+levelID).remove();
		}
	}
	function deleteAuthor(key, item) {
		if (confirm('Are you sure you want to delete this?')) {
			jQuery('#listitem_'+key+'_'+item).remove();
		}
	}
<?php	
	die();
}

function cfum_regular_user_js() {
	header('Content-type: text/javascript');
	?>
	jQuery(document).ready(function() {
		jQuery("#description").parents('tr').attr("style","display:none;");
	});
	<?php
	die();
}

function cfum_regular_user_css() {
	header('Content-type: text/css');
	?>
	#cfum-bio-container-display {
		padding:10px;
	}
	<?php
	die();
}

function cfum_admin_user_js() {
	header('Content-type: text/javascript');
	?>
	jQuery(document).ready(function() {
		jQuery("#description").parents('tr').attr("style","display:none;");
		jQuery("a.cfum-use-this").click(function() {
			var id = jQuery(this).attr('id').split('bio-');
			var content = jQuery('#'+id[1]).html();
			CKEDITOR.instances.cfum_bio.setData(content);
			return false;
		});
		jQuery("a.cfum-show-bio").click(function() {
			var id = jQuery(this).attr('id').split('_');
			var showhide = id[0];
			var box_id = id[1];
			
			if (showhide == 'show') {
				cfumShowBio(box_id);
			}
			else {
				cfumHideBio(box_id);
			}
			return false;
		});
	});
	function cfumShowBio(id) {
		jQuery('#box-'+id).slideDown();
		jQuery('#hide_'+id).attr('style','');
		jQuery('#show_'+id).attr('style','display:none;');
		return false;
	}
	function cfumHideBio(id) {
		jQuery('#box-'+id).slideUp();
		jQuery('#hide_'+id).attr('style','display:none;');
		jQuery('#show_'+id).attr('style','');
		return false;
	}
	
	// Get the WYSIWYG In place
	jQuery(function($) {
		CKEDITOR.replace("cfum_bio", {
			customConfig : "/index.php?cf_action=cfum_ckeditor_toolbar_config"
		});
	});
	<?php
	die();
}

function cfum_admin_user_css() {
	header('Content-type: text/css');
	?>
	#cfum-bio-otherblog {
		padding:20px;
		border-top:1px solid #DFDFDF;
	}
	#cfum-bio-otherblog .cfum_alternate_bio {
		display:none;
		padding:0 10px 10px;
	}
	#cfum-bio-otherblog h3 {
		margin-top:0;
	}
	#cfum-bio-otherblog h4 {
		padding-bottom:5px;
		margin:0;
	}
	#cfum-bio-otherblog h4 a {
		font-size:10px;
	}
	#cfum-bio-otherblog a.cfum-use-this {
		font-weight:bold;
	}
	
	<?php	
	die();
}

function cfum_ckeditor_toolbar_config() {
	header('Content-type: text/javascript');
	?>
	CKEDITOR.editorConfig = function( config )
	{
		config.entities = false;
		config.toolbar = "CFUMToolbar";

		config.toolbar_CFUMToolbar =
		[
		    ["Format"],
		    ["Bold","Italic","Strike"],
		    ["NumberedList","BulletedList","-","Outdent","Indent"],
		    ["Link","Unlink","Image","HorizontalRule","SpecialChar"],
		    ["PasteText","PasteFromWord"],
		    ["Undo","Redo","-","SelectAll","RemoveFormat"],
			["Source"]
		];
	};
	<?php
	die();
}


/**
 * 
 * CF Author Levels Admin Display Functions
 * 
 */

function cfum_options_form() {
	global $cfum_allowedtags;
	$levels = cfum_get_levels();
	
	if ( isset($_GET['cfum_message']) && $_GET['cfum_message'] = 'updated' ) {
		print('
			<div id="message" class="updated fade">
				<p>'.__('Settings updated.', 'cf-links').'</p>
			</div>
		');
	}
	print('
		<div class="wrap">
			'.cfum_nav('main').'
			<form action="" method="post" id="cfum-main-form">');
			if (is_array($levels)) {
				foreach ($levels as $level_key => $level) {
					print('
						<h3 class="cfum_list_name">'.htmlspecialchars($level['title']).'</h3>
						<div id="cfum-description">
							<p>
								<span class="description">'.__('Description: ','cfum_author_lvl').'</span>'.wpautop(wptexturize(wp_kses(stripslashes($level['description']),$cfum_allowedtags))).'
							</p>
						</div>
						<table class="widefat">
							<thead>
								<tr>
									<th scope="col" width="80px" style="text-align: center;">'.__('Order','cfum_author_lvl').'</th>
									<th scope="col">'.__('Author','cfum_author_lvl').'</th>
									<th scope="col" width="80px" style="text-align: center;">'.__('Delete','cfum_author_lvl').'</th>
								</tr>
							</thead>
						</table>
						<ul id="cfum-list-'.$level_key.'" class="cfum-author-list">');
					if (is_array($level['list'])) {
						foreach ($level['list'] as $author_key => $author) {
							$userdata = get_userdata($author);
							print('<li id="listitem_'.$level_key.'_'.$author_key.'">
								<table class="widefat">
									<tr>
										<td width="80px" style="text-align: center;"><img src="'.plugins_url('cf-author-levels/images/arrow_up_down.png').'" class="handle" alt="move" /></td>
										<td><a href="'.esc_url("user-edit.php?user_id=$userdata->ID").'">'.htmlspecialchars($userdata->display_name).'</a></td>
										<td width="80px" style="text-align: center;"><input type="button" class="button" id="cfum_delete_'.$author.'" value="'.__('Delete', 'cfum_author_lvl').'" onClick="deleteAuthor(\''.$level_key.'\',\''.$author_key.'\')" /></td>
										<input type="hidden" value="'.$author.'" name="cfum_author_list['.$level_key.'][]" />
									</tr>
								</table>
							</li>');
						}
					}
					print('</ul>
					<table class="widefat">
						<tr>
							<td>
								<p class="submit" style="border-top: none; padding:0; margin:0;">
									<input type="button" class="button" name="cfum_add_user" id="cfum_add_user" value="'.__('Add New User', 'cfum_author_lvl').'" onClick="addUser(\''.$level_key.'\')" />
								</p>
							</td>
						</tr>
					</table>');
					print('
						<p class="submit" style="border-top: none;">
							<input type="hidden" name="cf_action" value="cfum_update_author_lists" />
							<input type="submit" class="button-primary button" name="submit" id="cfum-submit" value="'.__('Update Settings', 'cfum_author_lvl').'" />
						</p>
					');
				}
			}
			else {
				print('
				<br /><br />
				<div id="cfum-no-lists">
					<p>
						'.__('No lists have been created.  Go <a href="'.get_bloginfo('wpurl').'/wp-admin/users.php?page=cf-author-levels.php&cfum_page=edit" '.$edit_class.'>here</a> to create new lists.','cfum_author_lvl').'
					</p>
				</div>
				');
			}
			print('
			</form>
			');
			print('<div id="newitem_SECTION" style="display:none;">
				<li id="listitem_###KEY###_###SECTION###">
					<table class="widefat">
						<tr>
							<td width="80px" style="text-align: center;"><img src="'.plugins_url('cf-author-levels/images/arrow_up_down.png').'" class="handle" alt="move" /></td>
							<td>
								<select name="cfum_author_list[###KEY###][]" style="max-width:500px; width:100%;">
									'.cfum_get_authors_list_select().'
								</select>
							</td>
							<td width="80px" style="text-align: center;"><input type="button" class="button" id="cfum_delete_###SECTION###" value="'.__('Delete', 'cfum_author_lvl').'" onClick="deleteAuthor(\'###KEY###\', \'###SECTION###\')" /></td>
						</tr>
					</table>
				</li>
			</div>');
			print('
		</div>
	');
}

function cfum_edit_form() {
	global $cfum_allowedtags;
	$cfum_author_lvls = cfum_get_levels();
	
	if ( isset($_GET['cfum_message']) && $_GET['cfum_message'] = 'updated' ) {
		print('
			<div id="message" class="updated fade">
				<p>'.__('Settings updated.', 'cf-links').'</p>
			</div>
		');
	}
	print('
		<div class="wrap">
			'.cfum_nav('edit').'
			<form action="" method="post" id="cfum-edit-form">
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" width="80px" style="text-align: center;">'.__('Order','cfum_author_lvl').'</th>
							<th scope="col" width="300px">'.__('Name','cfum_author_lvl').'</th>
							<th scope="col">'.__('Description','cfum_author_lvl').'</th>
							<th scope="col" width="80px" style="text-align: center;">'.__('Delete','cfum_author_lvl').'</th>
						</tr>
					</thead>
				</table>
				<ul id="cfum-list">');
					if (is_array($cfum_author_lvls)) {
						foreach ($cfum_author_lvls as $key => $level) {
							print('<li id="listitem_'.$key.'">
								<table class="widefat">
									<tr>
										<td width="80px" style="text-align: center;"><img src="'.plugins_url('cf-author-levels/images/arrow_up_down.png').'" class="handle" alt="move" /></td>
										<td width="300px"><input type="text" name="cfum_author_lvls['.$key.'][title]" size="30" value="'.htmlspecialchars($level['title']).'" /><br />Keyname: <code>'.htmlspecialchars($key).'</code></td>
										<td><textarea rows="2" style="width:100%;" name="cfum_author_lvls['.$key.'][description]">'.wp_kses(stripslashes($level['description']),$cfum_allowedtags).'</textarea></td>
										<td width="80px" style="text-align: center;"><input type="button" class="button" id="cfum_delete_'.$key.'" value="'.__('Delete', 'cfum_author_lvl').'" onClick="deleteLevel(\''.$key.'\')" /></td>
									</tr>
								</table>
							</li>');
						}
					}
				print('</ul>
				<table class="widefat">
					<tr>
						<td>
							<input type="button" class="button" name="cfum_add" id="cfum_add" value="'.__('Add New List Type', 'cfum_author_lvl').'" onClick="addLevel()" />
						</td>
					</tr>
				</table>
				<p class="submit" style="border-top: none;">
					<input type="hidden" name="cf_action" value="cfum_update_author_lvls" />
					<input type="submit" class="button-primary button" name="submit" id="cfum-submit" value="'.__('Update Settings', 'cfum_author_lvl').'" />
				</p>
			</form>');
			print('<div id="newitem_SECTION" style="display:none;">
				<li id="listitem_###SECTION###">
					<table class="widefat">
						<tr>
							<td width="80px" style="text-align: center;"><img src="'.plugins_url('cf-author-levels/images/arrow_up_down.png').'" class="handle" alt="move" /></td>
							<td width="300px"><input type="text" name="cfum_author_lvls[###SECTION###][title]" size="30" value="" /></td>
							<td><textarea rows="2" style="width:100%;" name="cfum_author_lvls[###SECTION###][description]"></textarea></td>
							<td width="80px" style="text-align: center;"><input type="button" class="button" id="cfum_delete_###SECTION###" value="'.__('Delete', 'cfum_author_lvl').'" onClick="deleteLevel(###SECTION###)" /></td>
						</tr>
					</table>
				</li>
			</div>');
			print('
		</div>
	');
}

function cfum_nav($page = '') {
	$cfum_nav = '';
	$main_class = '';
	$edit_class = '';
	
	$cfum_nav .= '<div id="cfum_nav">';
	$cfum_nav .= '<div class="icon32" id="icon-users"><br/></div><h2>'.__('Manage CF Author Levels','cfum_author_lvl').'</h2>';
	switch ($page) {
		case 'main':
			$main_class = 'class="current"';
			break;
		case 'edit':
			$edit_class = 'class="current"';
	}
	$cfum_nav .= '
		<ul class="subsubsub">
			<li>
				<a href="'.get_bloginfo('wpurl').'/wp-admin/users.php?page=cf-author-levels.php&cfum_page=main" '.$main_class.'>'.__('Lists','cfum_author_lvl').'</a> |
			</li>
			<li>
				<a href="'.get_bloginfo('wpurl').'/wp-admin/users.php?page=cf-author-levels.php&cfum_page=edit" '.$edit_class.'>'.__('List Types','cfum_author_lvl').'</a>
			</li>
		</ul>
	';
	$cfum_nav .= '</div>';
	return($cfum_nav);
}

function cfum_show_user_information() {
	global $profileuser;
	$user_info = get_usermeta($profileuser->ID, 'cfum_user_data');
	$logged_in_user = wp_get_current_user();
	if (empty($user_info[sanitize_title(get_bloginfo('name')).'feedburner_link']) && empty($user_info['photo_url']) && empty($user_info[sanitize_title(get_bloginfo('name')).'-cfum-bio'])) { return; }
	?>
	<h3><?php _e('User Information', 'cfum_author_lvl'); ?></h3>
	<?php if (!empty($user_info[sanitize_title(get_bloginfo('name')).'-cfum-bio'])) { ?>
	<table class="form-table">
	<tbody>
		<tr>
			<th>
				<label for="content"><?php _e('User Bio','cfum_author_lvl') ?></label>
			</th>
			<td>
				<div style="border: 1px solid #DFDFDF;">
					<div id="cfum-bio-container-display">
						<?php echo $user_info[sanitize_title(get_bloginfo('name')).'-cfum-bio']; ?>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
	</table>
	<?php } ?>
	<?php if (!empty($user_info['photo_url']) && !empty($user_info[sanitize_title(get_bloginfo('name')).'feedburner_link'])) { ?>
		<table class="form-table">
		<tbody>
			<?php if (!empty($user_info['photo_url'])) { ?>
			<tr>
				<th><label for="cfum_photo_url"><?php _e('Photo URL','cfum_author_lvl') ?></label></th>
				<td>
					<img src="<?php echo $user_info['photo_url']; ?>" border="0" />
				</td>
			</tr>
			<?php } ?>
			<?php if (!empty($user_info[sanitize_title(get_bloginfo('name')).'feedburner_link'])) { ?>
			<tr>
				<th><label for="cfum_photo_url"><?php _e('Feedburner RSS Link','cfum_author_lvl') ?></label></th>
				<td>
					<a href="<?php echo $user_info[sanitize_title(get_bloginfo('name')).'feedburner_link']; ?>"><?php _e('Feedburner Link', 'cfum_author_lvl'); ?></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
		</table>
	<?php
	}
}

function cfum_show_user_form_fields() {
	global $profileuser;
	$user_info = get_usermeta($profileuser->ID, 'cfum_user_data');
	$logged_in_user = wp_get_current_user();
	?>
	<style type="text/css">
		#your-profile tbody input[type=checkbox] {
			width:inherit;
		}
	</style>
	<input type="hidden" name="user-id" id="user-id" value="<?php echo $logged_in_user->ID; ?>">
	<h3><?php _e('User Information','cfum_author_lvl') ?></h3>
	<table class="form-table">
	<tbody>
		<tr>
			<th>
				<label for="content"><?php _e('User Bio','cfum_author_lvl') ?></label>
			</th>
			<td>
				<div style="border: 1px solid #DFDFDF;">
					<div id="cfum-bio-container">
						<textarea id="cfum_bio" class="cfum-bio-textarea" name="cfum-bio"><?php echo $user_info[sanitize_title(get_bloginfo('name')).'-cfum-bio']; ?></textarea>
					</div>
					<?php 
					$other_blog_content = '';
					if (is_array($user_info) && !empty($user_info)) { 
						foreach($user_info as $key => $info) {
							if (strstr($key,'-cfum-bio') !== false) {
								$this_blog_key = sanitize_title(get_bloginfo('name')).'-cfum-bio';
								if ($key != $this_blog_key) {
									if (!empty($info)) {
										$other_blog_content .= '
										<h4 id="'.$key.'-blog">
											'.str_replace('-cfum-bio','',$key).__('\'s bio','cfum_author_lvl').' <a href="#" class="cfum-show-bio" id="show_'.$key.'">'.__('Show','cfum_author_lvl').'</a><a href="#" class="cfum-show-bio" id="hide_'.$key.'" style="display:none;">'.__('Hide','cfum_author_lvl').'</a>
										</h4>
										<div id="box-'.$key.'" class="cfum_alternate_bio">
											<div id="'.$key.'">
												'.$info.'
											</div>
											<br />
											<a href="#" class="cfum-use-this" id="bio-'.$key.'">'.__('Use this bio','cfum_author_lvl').'</a>
										</div>
										';
									}
								}
							}
						}
					}
					if (!empty($other_blog_content)) {
						?>
						<div id="cfum-bio-otherblog">
							<h3><?php _e('Would you like to use another blogs bio?','cfum_author_lvl'); ?></h3>
							<?php echo $other_blog_content; ?>
						</div>
						<?php
					}
					?>
				</div>
			</td>
		</tr>
	</tbody>
	</table>
	<table class="form-table">
	<tbody>
		<tr>
			<th><label for="cfum_photo_url"><?php _e('Photo URL','cfum_author_lvl') ?></label></th>
			<td>
				<input id="cfum_photo_url" name="cfum_photo_url" value="<?php echo $user_info['photo_url']; ?>" type="text" />
				<p><?php _e('Photos should be around 80 x 110 pixels and should be in .jpg or .png format.','cfum_author_lvl') ?></p> 
				<p><?php _e('If your photo is located in the /wp-content/author-photos/ folder, you can just enter 
				the name of the file. Otherwise, specify an absolute URL, such as http://flickr.com/photos/somephoto.jpg.','cfum_author_lvl') ?></p>
				<p><?php _e('If a photo is larger than 80 pixels wide, the image will be scaled down to 80 pixels.','cfum_author_lvl') ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="cfum_photo_url"><?php _e('Feedburner RSS Link','cfum_author_lvl') ?></label></th>
			<td>
				<input id="feedburner_link" name="feedburner_link" value="<?php echo $user_info[sanitize_title(get_bloginfo('name')).'feedburner_link']; ?>" type="text" />
			</td>
		</tr>
	</tbody>
	</table>
<?php
}

/**
 * 
 * CF Author Levels Data Handlers
 * 
 */

function cfum_profile_edited_by_admin() {
	global $user_id;

	$user_info = get_usermeta($user_id, 'cfum_user_data');
	if (isset($_POST['cfum_photo_url'])) {
		$user_info['photo_url'] = stripslashes($_POST['cfum_photo_url']);
	} else {
		$user_info['photo_url'] = '';
	}
	if (isset($_POST['feedburner_link'])) {
		$user_info[sanitize_title(get_bloginfo('name')).'feedburner_link'] = stripslashes($_POST['feedburner_link']);
	} else {
		$user_info[sanitize_title(get_bloginfo('name')).'feedburner_link'] = '';
	}
	if (isset($_POST['cfum-bio'])) {
		$user_info[sanitize_title(get_bloginfo('name')).'-cfum-bio'] = stripslashes($_POST['cfum-bio']);
	}
	else {
		$user_info[sanitize_title(get_bloginfo('name')).'-cfum-bio'] = '';
	}
	return update_usermeta($user_id, 'cfum_user_data', $user_info);
}
add_action('profile_update', 'cfum_profile_edited_by_admin');

function cfum_update_author_lvls($levels = array()) {
	$data = array();
	if(is_array($levels)) {
		foreach ($levels as $key => $level) {
			if (!empty($level['title'])) {
				$data[sanitize_title($level['title'])] = array(
					'title' => $level['title'],
					'description' => $level['description'],
				);
			}
		}	
	}
	if (!get_option('cfum_author_lvls')) {
		add_option('cfum_author_lvls', $data, false, 'no');
	}
	else {
		update_option('cfum_author_lvls',$data);
	}
}

function cfum_update_author_list($lists = array()) {
	if (!get_option('cfum_author_lists')) {
		add_option('cfum_author_lists', $lists, false, 'no');
	}
	else {
		update_option('cfum_author_lists',$lists);
	}
}

/**
 * 
 * CF Author Levels Data Retrieval
 * 
 */

function cfum_get_author_levels($key = '', $args = array()) {
	global $cfum_allowedtags;
	$return = '';
	if(!empty($key)) {
		$ul_key = 'cfum-list-'.$key;
	}
	$defaults = array(
		'show_list_title' => true,
		'show_list_description' => false,
		'list_before' => '<ul class="cfum-list '.$ul_key.'">',
		'list_after' => '</ul>',
		'list_item_before' => '<li>',
		'list_item_after' => '</li>',
		'quiet' => false
	);
	$args = array_merge($defaults, apply_filters('cfum-get-author-levels-args', $args, $key));
	extract($args, EXTR_SKIP);

	if (empty($key)) {
		$levels = cfum_get_levels();
	}
	else {
		$levels = cfum_get_level($key);
	}
	$levels = apply_filters('cfum_get_author_levels_data',$levels);
	if (is_array($levels)) {
		foreach ($levels as $level_key => $level) {
			if (is_array($level['list'])) {
				$return .= '<div id="cfum-author-lvl-'.$level_key.'">';
				if($show_list_title) {
					$return .= '<h2 id="cfum-author-lvl-'.$level_key.'-title" class="cfum-author-lvl-title">'.htmlspecialchars($level['title']).'</h2>';
				}
				$return .= $list_before;
				foreach ($level['list'] as $list_key => $author) {
					$return .= $list_item_before.cfum_get_author_info($author,$args).$list_item_after;
				}
				$return .= $list_after;
				if($show_list_description) {
					$return .= '
						<div id="cfum-author-lvl-'.$level_key.'-description">
							'.wpautop(wptexturize(wp_kses(stripslashes($level['description']),$cfum_allowedtags))).'
						</div>
					';
				}
				$return .= '</div>';
			}
		}
	}
	else {
		$return = ($quiet ? null : 'Could not find author level: '.$key);
	}
	$return = apply_filters('cfum_get_author_levels',$return);
	return $return;
}

function cfum_author_levels($key = '',$args = array()) {
	echo cfum_get_author_levels($key, $args);
}

function cfum_author_levels_shortcode($atts) {
	$atts = extract(shortcode_atts(array('key'=>''),$atts));
	return cfum_get_author_levels($key);
}
add_shortcode('cfum','cfum_author_levels_shortcode');

function cfum_get_author_info($author, $args = array()) {
	$return = '';
	$defaults = array(
		'show_author_title' => true,
		'show_bio' => true,
		'show_link' => true,
		'show_image' => true,
		'show_image_link' => true,		
		'add_clear_div' => true,
		'author_title_before' => '<h3 class="authorname authorname-'.$author.'">',
		'author_title_after' => '</h3>'
	);
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);	
	
	$userdata = get_userdata($author);
	$usermeta = get_usermeta($author, 'cfum_user_data');
	
	$display_name = apply_filters('cfum_author_display_name', $userdata->display_name, $author);
	$photo_url = apply_filters('cfum_author_photo_url', cfum_get_photo_url($userdata->ID), $author);
	$posts_url = apply_filters('cfum_author_posts_url', get_author_posts_url($author), $author);
	$bio = apply_filters('cfum_author_bio', $usermeta[sanitize_title(get_bloginfo('name')).'-cfum-bio'], $author);
	
	$return .= '
		<div id="'.$userdata->user_nicename.'" class="aboutauthor aboutauthor-'.$author.'">';
			if($show_image) {
				$return .= '
					<div class="authorimage authorimage-'.$author.'">
					';
						if ($show_image_link) {
							$return .= '<a href="'.esc_attr($posts_url).'">';
						}
						$return .= '<img src="'.esc_attr($photo_url).'" width="80px" alt="Author Image for '.esc_attr($display_name).'" />';
						if ($show_image_link) {
							$return .= '</a>';
						}
				$return .= '
					</div>
				';
			}
			$return .= '
			<div class="authordata authordata-'.$author.'">
				<div class="authorbio authorbio-'.$author.'">
				';
				if ($show_author_title) {
					$return .= $author_title_before.'<a href="'.esc_attr($posts_url).'">'.esc_html($display_name).'</a>'.$author_title_after;
				}
				if($show_bio) {
					if (function_exists('cfcn_get_context')) {
						global $cfum_author_id;
						$cfum_author_id = $author;
						add_filter('cfcn_context', 'cfum_add_context');
					}
					$return .= do_shortcode($bio);
					if (function_exists('cfcn_get_context')) {
						if (isset($_GET['cfcn_display']) && $_GET['cfcn_display'] == 'true') {
							$return .= '
							<div class="cfum-author-bio-context">
								<p><b>NOTE:</b> The following items have been added to the CF Context for this user bio.</p>
								<p>
									Name: cfum_author_id
									<br />
									Value: '.$cfum_author_id.'
								</p>
								<p>
									Name: cfum_username
									<br />
									Value: '.$userdata->user_login.'
								</p>
							</div>
							';
						}
						$context['cfum_author_id'] = $cfum_author_id;
						$context['cfum_username'] = $userdata->user_login;
						remove_filter('cfcn_context', 'cfum_add_context');
					}
				}
			$return .= '
				</div>
			';
			if($show_link) {
				$return .= '
					<p class="authorlink authorlink-'.$author.'">
						'.__('View ','cfum_author_lvl').'<a rel="author" href="'.esc_attr($posts_url).'">'.__('articles by ','cfum_author_lvls').esc_html($display_name).'</a>
					</p>
				';
			}
			$return .= '</div>';
			if($add_clear_div) {
				$return .= '
					<div class="clear"></div>
				';
			}
			$return = apply_filters('cfum_get_author_info_after',$return,$author);
			$return .= '
		</div>
	';
	return $return;
}

function cfum_author_info($author,$args = array()) {
	echo cfum_get_author_info($author,$args);
}

function cfum_get_authors_list_select() {
	global $wpdb;
	$return = '';
	$authors = get_users_of_blog($wpdb->blog_id);
	foreach ($authors as $author) {
		// Remove all subscribers from the list
		if (strpos($author->meta_value, 'administrator') === false && strpos($author->meta_value, 'editor') === false && strpos($author->meta_value, 'author') === false && strpos($author->meta_value, 'contributor') === false) { continue; }
		$return .= '<option value="'.attribute_escape($author->user_id).'">'.attribute_escape($author->display_name).'</option>';
	}
	return $return;
}

function cfum_get_photo_url($author = 0) {
	if (!$author) {
		$author = get_the_author_ID();
	}
	$userinfo = get_usermeta($author, 'cfum_user_data');
	if ($userinfo && isset($userinfo['photo_url']) && $userinfo['photo_url'] != '') {
		$url = $userinfo['photo_url'];
		if (strpos($url, 'http://') !== false) {
			return htmlspecialchars($url);
		}
		if (file_exists(ABSPATH.'/wp-content/author-photos/'.htmlspecialchars($userinfo['photo_url']))) {
			return get_bloginfo('wpurl').'/wp-content/author-photos/'.htmlspecialchars($userinfo['photo_url']);
		}
	}
	return apply_filters('cfum-get-author-photo',get_bloginfo('wpurl').'/'.PLUGINDIR.'/cf-author-levels/images/mystery.png');
}

function cfum_get_levels($include_users=true) {
	$levels = maybe_unserialize(get_option('cfum_author_lvls'));

	$return = '';
	if (is_array($levels)) {
		foreach ($levels as $level_key => $level) {
			if ($level_key != '') {
				$info = array(
					'title' => $level['title'], 
					'description' => $level['description'], 
				);
				if($include_users) {
					$list = cfum_get_list($level_key);
					$info['list'] = $list;
				}
				$return[$level_key] = $info;
			}
		}
	}
	return $return;
}

function cfum_get_level($key = '') {
	$levels = maybe_unserialize(get_option('cfum_author_lvls'));

	if (!empty($key) && is_array($levels)) {
		if ($levels[$key] != '') {
			$list = cfum_get_list($key);
			if (!is_array($list)) { return ''; }
			$levels[$key]['list'] = $list;
			$return[$key] = $levels[$key];
		}
	}
	return $return;
}

function cfum_get_list($key = '') {
	if (!empty($key)) {
		$lists = maybe_unserialize(get_option('cfum_author_lists'));
		if(!is_array($lists[$key])) { return ''; }
		$return = $lists[$key];
		return $lists[$key];
	}
}

/**
 * 
 * CF Author Levels Functions for CF Links
 * 
 */

function cfum_links_filter($links) {
	foreach($links['data'] as $key => $link_info) {
		if($link_info['type'] == 'author_rss') {
			$author = $link_info['link'];
			$userdata = get_userdata($author);
			$usermeta = get_usermeta($author, 'cfum_user_data');
			if (!empty($usermeta[sanitize_title(get_bloginfo('name')).'feedburner_link']) ) {
				$links['data'][$key]['link'] = $usermeta[sanitize_title(get_bloginfo('name')).'feedburner_link'];
				$links['data'][$key]['href'] = $usermeta[sanitize_title(get_bloginfo('name')).'feedburner_link'];
				$links['data'][$key]['title'] = $userdata->display_name;
				$links['data'][$key]['type'] = 'rss';
			}
		}
	}
	return $links;
}
add_filter('cflk_get_links_data','cfum_links_filter',10,3);

/**
 * 
 * CF Author Levels Hidden Data Handlers
 * 
 */

function cfum_user_description_to_bio($user_id) {
	if (!current_user_can('manage_options')) { return false; }
	if (!isset($user_id) || $user_id == 0) { return false; }
	
	$user_info = get_usermeta($user_id,'cfum_user_data');
	$description = get_usermeta($user_id,'description');
	if ($description == '') { return false; }
	$user_info[sanitize_title(get_bloginfo('name')).'-cfum-bio'] = $description;
	return update_usermeta($user_id, 'cfum_user_data', $user_info);
}

function cfum_description_process() {
	global $wpdb;
	$cfum_authors = get_users_of_blog($wpdb->blog_id);
	foreach ($cfum_authors as $cfum_author) {
		print('UserID: '.$cfum_author->user_id);
		$result = cfum_user_description_to_bio($cfum_author->user_id);
		if ($result) { print(' || bio added!<br />'); }
		else { print(' || no bio to add.<br />'); }
	}
}

/**
 * 
 * CF Author Levels Widgets
 *
 */

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class cfum_Widget extends WP_Widget {
	
    /**
     * Constructor
     *
     * @return void
     **/
	function cfum_Widget() {
		$widget_ops = array('classname' => 'cfum-widget', 'description' => 'Widget for displaying CF Author Levels lists.');
		$this->WP_Widget('cfum-widget', 'CF Author Levels', $widget_ops);
	}

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme 
     * @param array  An array of settings for this widget instance 
     * @return void Echoes it's output
     **/
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		$title = apply_filters('cfum-widget-title', $instance['title'], $instance['list']);
		$list = apply_filters('cfum-widget-list', $instance['list']);

		if (empty($list) || $list == '0') { return; }
		
		$args = apply_filters('cfum-widget-args', array(
			'show_list_title' => false,
			'show_list_description' => false,
			'quiet' => true,
			'show_author_title' => true,
			'show_bio' => true,
			'show_link' => true,
			'show_image' => false,
			'show_image_link' => false,		
			'add_clear_div' => true,
		), $list);
		
		echo $before_widget;
		if (!empty($title)) {
			echo $before_title.$title,$after_title;
		}
		echo '<div class="cfum_widget">'.cfum_get_author_levels($list, $args).'</div>';
		echo $after_widget;
	}

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings 
     * @return array The validated and (if necessary) amended settings
     **/
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['list'] = strip_tags($new_instance['list']);
		return $instance;
	}

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
	function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => '', 'list' => ''));
		$title = esc_attr($instance['title']);
		$author_levels = cfum_get_levels(false);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('list'); ?>"><?php _e('List:')?></label>
			<select id="<?php echo $this->get_field_id('list'); ?>" name="<?php echo $this->get_field_name('list'); ?>" class="widefat">
				<option value="0"<?php selected($instance['list'], '0'); ?>><?php _e('Select List:'); ?></option>
				<?php
				if (is_array($author_levels) && !empty($author_levels)) {
					foreach ($author_levels as $key => $data) {
						?>
						<option value="<?php echo attribute_escape($key); ?>"<?php selected($instance['list'], attribute_escape($key)); ?>><?php echo attribute_escape($data['title']); ?></option>
						<?php
					}
				}
				?>
			</select>
		</p>
		<p>
			<a href="<?php echo admin_url('users.php?page=cf-author-levels.php'); ?>"><?php _e('Edit Author Lists','cfsp') ?></a>
		</p>
		<?php
	}
}
add_action('widgets_init', create_function('', "register_widget('cfum_Widget');"));

// Registers each instance of our widget on startup
function cfum_widgets_init() {
	if (!$options = get_option('cfum_widgets'))
		$options = array();

	$widget_ops = array('classname' => 'cfum_widgets', 'description' => __('Make Widgets from Author Levels Lists. (Version 1.0, please use the new version)'));
	$control_ops = array('width' => 250, 'height' => 350, 'id_base' => 'cfum_widgets');
	$name = __('CF Author Levels 1.0');

	$registered = false;
	foreach(array_keys($options) as $o) {
		// Old widgets can have null values for some reason
		if (!isset($options[$o]['title'])) { // we used 'something' above in our exampple.  Replace with with whatever your real data are.
			continue;
		}

		// $id should look like {$id_base}-{$o}
		$id = "cfum_widgets-$o"; // Never never never translate an id
		$registered = true;
		wp_register_sidebar_widget($id, $name, 'cfum_widgets', $widget_ops, array('number' => $o));
		wp_register_widget_control($id, $name, 'cfum_widgets_control', $control_ops, array('number' => $o));
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget('cfum-widgets-1', $name, 'cfum_widgets', $widget_ops, array('number' => -1));
		wp_register_widget_control('cfum-widgets-1', $name, 'cfum_widgets_control', $control_ops, array('number' => -1));
	}
}
add_action('widgets_init','cfum_widgets_init');

function cfum_widgets($args, $widget_args = 1) {
	extract($args,EXTR_SKIP);
	if (is_numeric($widget_args)) {
		$widget_args = array( 'number' => $widget_args );
	}
	$widget_args = wp_parse_args($widget_args, array('number' => -1));
	extract( $widget_args, EXTR_SKIP );
	
	// get widget options, return if none present
	$options = get_option('cfum_widgets');
	if(!isset($options[$number])) {
		return;
	}
	extract($options[$number]);
	
	// get author list, return if not present
	$level_list = cfum_get_level($key);
	if(empty($level_list)) {
		return;
	}
	else {
		// pre-trim and fill the array with authordata
		$level = $level_list[$key];
		foreach($level['list'] as $k => $v) {
			$level['list'][$k] = get_userdata($v);
		}
	}

	echo $before_widget.
		$before_title.$title.$after_title.
		'<div class="author-list">';
	$list = '
			<ul>';
	foreach($level['list'] as $k => $userdata) {
		$list .= '
				<li><a href="'.get_author_posts_url($userdata->ID).'">'.$userdata->display_name.'</a></li>';
	}
	$list .= '
			</ul>';
	echo apply_filters('cfum_widget_author_list',$list,$key,$level);
	echo '
		</div>'.
		$after_widget;
}

function cfum_widgets_control($widget_args = 1) {
	global $wp_registered_widgets;
	static $updated = false;
	
	if (is_numeric($widget_args)) {
		$widget_args = array('number' => $widget_args);
	}
	$widget_args = wp_parse_args($widget_args, array('number' => -1));
	extract($widget_args, EXTR_SKIP);
	
	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('cfum_widgets');
	if (!is_array($options)) {
		$options = array();
	}

	// We need to update the data
	if (!$updated && !empty($_POST['sidebar'])) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];
		$sidebars_widgets = wp_get_sidebars_widgets();
		if (isset($sidebars_widgets[$sidebar])) {
			$this_sidebar =& $sidebars_widgets[$sidebar];
		}
		else {
			$this_sidebar = array();
		}
		
		foreach($this_sidebar as $_widget_id) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ('cfum_widgets' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if (!in_array( "cfum_widgets-$widget_number", $_POST['widget-id'])) { // the widget has been removed.
					unset($options[$widget_number]);
				}
			}
		}

		foreach((array) $_POST['cfum_widgets'] as $widget_number => $widgets_instance) {
			// compile data from $widgets_instance
			if (!isset($widgets_instance['key']) && isset($options[$widget_number])) { // user clicked cancel
				continue;
			}
			$key = strip_tags($widgets_instance['key']);
			$title = wp_specialchars( $widgets_instance['title'] );
			$options[$widget_number] = array(
				'title' => $title,
				'key' => $key
			);  // Even simple widgets should store stuff in array, rather than in scalar
		}
		update_option('cfum_widgets', $options);
		$updated = true; // So that we don't go through this more than once
	}
	
	// set options for display
	$options = get_option('cfum_widgets',array('',''));
	if(-1 == $number) {
		$title = '';
		$number = '%i%';
	}
	else {
		$key = attribute_escape($options[$number]['key']);
		$title = attribute_escape($options[$number]['title']);
	}
	
	// show form
	echo '
		<p>
			<label for="">Title</label>
			<input type="text" name="cfum_widgets['.$number.'][title]" value="'.$title.'" />
		</p>
		<p>
			<select name="cfum_widgets['.$number.'][key]" id="oc_executive_member_lists">
				<option value="">--- select author list ---</option>';
	$author_levels = cfum_get_levels(false);
	foreach($author_levels as $l_key => $l_data) {
		echo '
				<option value="'.$l_key.'"'.($key == $l_key ? ' selected="selected"' : null).'>'.$l_data['title'].'</option>'; 
	}
	echo '
			</select>
		</p>
	';
}

/**
 * 
 * CF Author Levels/CF Context Integration
 * 
 */
function cfum_add_context($context) {
	global $cfum_author_id;
	$userdata = get_userdata($cfum_author_id);
	
	$context['cfum_author_id'] = $cfum_author_id;
	$context['cfum_username'] = $userdata->user_login;
	
	return $context;
}

/**
 * 
 * CF Author Levels Deprecated Functions
 *
 */

function cfum_print_list($key = '') {
	echo cfum_get_author_levels($key);
}

// CF README HANDLING

/**
 * Enqueue the readme function
 */
function cfum_add_readme() {
	if(function_exists('cfreadme_enqueue')) {
		cfreadme_enqueue('cf-author-levels','cfum_readme');
	}
}
add_action('admin_init','cfum_add_readme');

/**
 * return the contents of the links readme file
 * replace the image urls with full paths to this plugin install
 *
 * @return string
 */
function cfum_readme() {
	$file = realpath(dirname(__FILE__)).'/readme/README.txt';
	if(is_file($file) && is_readable($file)) {
		$markdown = file_get_contents($file);
		$markdown = preg_replace('|!\[(.*?)\]\((.*?)\)|','![$1]('.WP_PLUGIN_URL.'/cf-author-levels/readme/$2)',$markdown);
		return $markdown;
	}
	return null;
}

?>
