<?php
/*
Plugin Name: CF Author Levels
Plugin URI: http://crowdfavorite.com
Description: Advanced options for author levels
Version: 1.1b1
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

load_plugin_textdomain('cfum_author_lvl');

/**
 * 
 * CF Author Levels Admin Handlers
 * 
 */

function cfum_menu_items() {
	if (current_user_can('manage_options')) {
		add_options_page(
			__('CF Author Levels','cfum_author_lvl')
			, __('CF Author Levels','cfum_author_lvl')
			, 10
			, basename(__FILE__)
			, 'cfum_check_page'
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
	if (current_user_can('manage_options')) {
		if (!empty($_POST['cf_action'])) {
			switch($_POST['cf_action']) {
				case 'cfum_update_author_lvls':
					if (isset($_POST['cfum_author_lvls']) && is_array($_POST['cfum_author_lvls'])) {
						cfum_update_author_lvls($_POST['cfum_author_lvls']);
						wp_redirect(get_bloginfo('wpurl').'/wp-admin/options-general.php?page=cf-author-lvls.php&cfum_page=edit&cfum_message=updated');
					}
					break;
				case 'cfum_update_author_lists':
					if (isset($_POST['cfum_author_list']) && is_array($_POST['cfum_author_list'])) {
						cfum_update_author_list($_POST['cfum_author_list']);
						wp_redirect(get_bloginfo('wpurl').'/wp-admin/options-general.php?page=cf-author-lvls.php&cfum_page=main&cfum_message=updated');
					}
					break;
				default:
					break;
			}
		}
	}
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfum_admin_js':
				cfum_admin_js();
				break;
			case 'cfum_admin_user_js':
				cfum_admin_user_js();
				break;
			case 'cfum_regular_user_js':
				cfum_regular_user_js();
				break;
			case 'cfum_admin_css':
				cfum_admin_css();
				break;
		}
	}
	if (current_user_can('manage_options') && basename($_SERVER['SCRIPT_FILENAME']) == 'profile.php') {
		add_action('admin_head','cfum_admin_user_head');
		add_action('show_user_profile','cfum_show_user_form_fields');
	}
}
add_action('init','cfum_request_handler');
add_action('wp_ajax_cfum_update_settings','cfum_request_handler');

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
		jQuery('#listitem_'+section).attr('style','');
	}
	function addUser(userKey) {
		var id = new Date().valueOf();
		var section = id.toString();
		var html = jQuery('#newitem_SECTION').html().replace(/###SECTION###/g, section).replace(/###KEY###/g, userKey);
		jQuery('#cfum-list-'+userKey).append(html);
		jQuery('#listitem_'+userKey+'_'+section).attr('style','');
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

function cfum_admin_head() {
	echo '<link rel="stylesheet" type="text/css" href="'.trailingslashit(get_bloginfo('url')).'?cf_action=cfum_admin_css" />';
	echo '<script src="'.trailingslashit(get_bloginfo('url')).'?cf_action=cfum_admin_js" type="text/javascript"></script>';
}
if (isset($_GET['page']) && $_GET['page'] == basename(__FILE__)) {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
	if (!function_exists('wp_prototype_before_jquery')) {
		function wp_prototype_before_jquery( $js_array ) {
			if ( false === $jquery = array_search( 'jquery', $js_array ) )
				return $js_array;
			if ( false === $prototype = array_search( 'prototype', $js_array ) )
				return $js_array;
			if ( $prototype < $jquery )
				return $js_array;
			unset($js_array[$prototype]);
			array_splice( $js_array, $jquery, 0, 'prototype' );
			return $js_array;
		}
	    add_filter( 'print_scripts_array', 'wp_prototype_before_jquery' );
	}
	
	add_action('admin_head', 'cfum_admin_head');
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

function cfum_admin_user_js() {
	header('Content-type: text/javascript');
	?>
	jQuery(document).ready(function() {
		jQuery("#description").parents('tr').attr("style","display:none;");
	});
	//<![CDATA[
		// must init what we want and run before the WordPress onPageLoad function.
		// After this function redo the WordPress init so the main editor picks up the WordPress config. 
		// that is the only way I could get this to work.
		tinyMCE.init({
				mode:"exact",
				elements:"cfum-bio", 
				onpageload:"", 
				width:"100%", 
				theme:"advanced", 
				skin:"wp_theme", 
				theme_advanced_buttons1:"bold,italic,underline,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,charmap,spellchecker,code,wp_help", 
				theme_advanced_buttons2:"", 
				theme_advanced_buttons3:"", 
				theme_advanced_buttons4:"", 
				language:"en", 
				spellchecker_languages:"+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv", 
				theme_advanced_toolbar_location:"top", 
				theme_advanced_toolbar_align:"left", 
				theme_advanced_statusbar_location:"", 
				theme_advanced_resizing:"", 
				theme_advanced_resize_horizontal:"", 
				dialog_type:"modal", 
				relative_urls:"", 
				remove_script_host:"", 
				convert_urls:"", 
				apply_source_formatting:"", 
				remove_linebreaks:"1", 
				paste_convert_middot_lists:"1", 
				paste_remove_spans:"1", 
				paste_remove_styles:"1", 
				gecko_spellcheck:"1", 
				entities:"38,amp,60,lt,62,gt", 
				accessibility_focus:"1", 
				tab_focus:":prev,:next", 
				content_css:"'.get_bloginfo('wpurl').'/wp-includes/js/tinymce/wordpress.css", 
				save_callback:"", 
				wpeditimage_disable_captions:"", 
				plugins:"safari,inlinepopups,spellchecker,paste"
			});
	//]]>
	<?php
	die();
}

function cfum_admin_user_head() {
	echo '<script src="'.trailingslashit(get_bloginfo('url')).'/wp-includes/js/tinymce/tiny_mce.js" type="text/javascript"></script>';	
	echo '<script src="'.trailingslashit(get_bloginfo('url')).'?cf_action=cfum_admin_user_js" type="text/javascript"></script>';	
}
if (basename($_SERVER['SCRIPT_FILENAME']) == 'user-edit.php') {
	add_action('admin_head','cfum_admin_user_head');
}

function cfum_regular_user_head() {
	echo '<script src="'.trailingslashit(get_bloginfo('url')).'?cf_action=cfum_regular_user_js" type="text/javascript"></script>';	
}
if (basename($_SERVER['SCRIPT_FILENAME']) == 'profile.php') {
	add_action('admin_head','cfum_regular_user_head');
}

/**
 * 
 * CF Author Levels Admin Display Functions
 * 
 */

function cfum_options_form() {
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
			<form action="'.get_bloginfo('url').'/wp-admin/options-general.php" method="post" id="cfum-main-form">');
			if (is_array($levels)) {
				foreach ($levels as $level_key => $level) {
					print('
						<h3 class="cfum_list_name">'.htmlspecialchars($level['title']).'</h3>
						<div id="cfum-description">
							<p>
								<span class="description">'.__('Description: ','cfum_author_lvl').'</span>'.htmlspecialchars($level['description']).'
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
										<td width="80px" style="text-align: center;"><img src="'.get_bloginfo('url').'/wp-content/plugins/cf-links/images/arrow_up_down.png" class="handle" alt="move" /></td>
										<td>'.htmlspecialchars($userdata->display_name).'</td>
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
			print('
			</form>
			');
			print('<div id="newitem_SECTION">
				<li id="listitem_###KEY###_###SECTION###" style="display:none;">
					<table class="widefat">
						<tr>
							<td width="80px" style="text-align: center;"><img src="'.get_bloginfo('url').'/wp-content/plugins/cf-links/images/arrow_up_down.png" class="handle" alt="move" /></td>
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
			<form action="'.get_bloginfo('url').'/wp-admin/options-general.php" method="post" id="cfum-edit-form">
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
					if (is_array($cfum_author_lvls) > 0) {
						foreach ($cfum_author_lvls as $key => $level) {
							print('<li id="listitem_'.$key.'">
								<table class="widefat">
									<tr>
										<td width="80px" style="text-align: center;"><img src="'.get_bloginfo('url').'/wp-content/plugins/cf-links/images/arrow_up_down.png" class="handle" alt="move" /></td>
										<td width="300px"><input type="text" name="cfum_author_lvls['.$key.'][title]" size="30" value="'.htmlspecialchars($level['title']).'" /></td>
										<td><textarea rows="2" style="width:100%;" name="cfum_author_lvls['.$key.'][description]">'.htmlspecialchars($level['description']).'</textarea></td>
										<td width="80px" style="text-align: center;"><input type="button" class="button" id="cfum_delete_'.$key.'" value="'.__('Delete', 'cfum_author_lvl').'" onClick="deleteLevel('.$key.')" /></td>
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
			print('<div id="newitem_SECTION">
				<li id="listitem_###SECTION###" style="display:none;">
					<table class="widefat">
						<tr>
							<td width="80px" style="text-align: center;"><img src="'.get_bloginfo('url').'/wp-content/plugins/cf-links/images/arrow_up_down.png" class="handle" alt="move" /></td>
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
				<a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=cf-author-lvls.php&cfum_page=main" '.$main_class.'>'.__('Lists','cfum_author_lvl').'</a> |
			</li>
			<li>
				<a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=cf-author-lvls.php&cfum_page=edit" '.$edit_class.'>'.__('List Types','cfum_author_lvl').'</a>
			</li>
		</ul>
	';
	$cfum_nav .= '</div>';
	return($cfum_nav);
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
				<div style="clear:both">&nbsp;</div>
					<div style="border: 1px solid #DFDFDF;">
						<div id="cfum-bio_container">
							<textarea id="cfum-bio" name="cfum-bio"><?php echo $user_info[sanitize_title(get_bloginfo('name')).'-cfum-bio']; ?></textarea>
						</div>
					</div>
				<div style="clear:both">&nbsp;</div>
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
add_action('edit_user_profile', 'cfum_show_user_form_fields');

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
	foreach ($levels as $key => $level) {
		if (!empty($level['title'])) {
			$data[sanitize_title($level['title'])] = array(
				'title' => $level['title'],
				'description' => $level['description'],
			);
		}
	}
	if (is_array($data)) {
		update_option('cfum_author_lvls', $data);
	}
}

function cfum_update_author_list($lists = array()) {
	if (is_array($lists)) {
		update_option('cfum_author_lists', $lists);
	}
}

/**
 * 
 * CF Author Levels Data Retrieval
 * 
 */

function cfum_get_author_levels($key = '') {
	$return = '';
	if (empty($key)) {
		$levels = cfum_get_levels();
	}
	else {
		$levels = cfum_get_level($key);
	}
	if (is_array($levels)) {
		foreach ($levels as $level_key => $level) {
			if (is_array($level['list'])) {
				$return .= '<div id="cfum-author-lvl-'.$level_key.'">
						<div id="cfum-author-lvl-'.$level_key.'-title">
							'.htmlspecialchars($level['title']).'
						</div>
						<ul>
						';
						foreach ($level['list'] as $list_key => $author) {
							$return .= '<li>'.cfum_get_author_info($author).'</li>';
						}
						$return .= '
						</ul>
					</div>';
			}
		}
	}
	else {
		$return = 'Could not find author level: '.$key;
	}
	return $return;
}

function cfum_author_levels($key = '') {
	echo cfum_get_author_levels($key);
}

function cfum_get_author_info($author) {
	$return = '';
	$userdata = get_userdata($author);
	$usermeta = get_usermeta($author, 'cfum_user_data');
	$return .= '
		<div class="aboutauthor">
			<div class="authordata">
				'.apply_filters('the_content','<div class="authorname">'.htmlspecialchars($userdata->display_name).': </div>'.$usermeta[sanitize_title(get_bloginfo('name')).'-cfum-bio']).'
				<br /><br />
				<span class="authorlink">
					'.__('View all articles by ','cfum_author_lvl').'<a href="'.get_author_posts_url($author).'">'.htmlspecialchars($userdata->display_name).'</a>
				</span>
			</div>
			<div class="authorimage">
				<img src="'.cfum_get_photo_url($userdata->ID).'" width="80px" alt="Author Image for '.htmlspecialchars($userdata->display_name).'" />
			</div>
			<div class="clear"></div>
		</div>
	';
	return $return;
}

function cfum_author_info($author) {
	echo cfum_get_author_info($author);
}

function cfum_get_authors_list_select() {
	global $wpdb;
	$return = '';
	$authors = get_users_of_blog($wpdb->blog_id);
	foreach ($authors as $author) {
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
		return get_bloginfo('wpurl').'/wp-content/author-photos/'.htmlspecialchars($userinfo['photo_url']);
	}
	return get_bloginfo('wpurl').'/'.PLUGINDIR.'/cf-author-lvls/images/mystery.png';
}

function cfum_get_levels() {
	$levels = maybe_unserialize(get_option('cfum_author_lvls'));
	$return = array();
	foreach ($levels as $level_key => $level) {
		$list = cfum_get_list($level_key);
		if (is_array($list)) {
			$info = array(
				'title' => $level['title'], 
				'description' => $level['description'], 
				'list' => $list
			);
			$return[$level_key] = $info;
		}
	}
	return $return;
}

function cfum_get_level($key = '') {
	$levels = maybe_unserialize(get_option('cfum_author_lvls'));
	if (!empty($key)) {
		$levels = maybe_unserialize(get_option('cfum_author_lvls'));
		$list = cfum_get_list($key);
		if(!is_array($list)) { return ''; }
		$levels[$key]['list'] = $list;
		$return[$key] = $levels[$key];
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
			$links['data'][$key] = array(
				'link' => $usermeta[sanitize_title(get_bloginfo('name')).'feedburner_link'],
				'title' => $userdata->display_name,
				'type' => 'url',
				'cat_posts' => '',
			);
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
 * CF Author Levels Deprecated Functions
 *
 */

function cfum_print_list($key = '') {
	echo cfum_get_author_levels($key);
}

?>