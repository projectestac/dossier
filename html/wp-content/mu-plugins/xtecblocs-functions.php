<?php
/*
Plugin Name: XTECBlocsFunctions
Plugin URI: https://github.com/projectestac/xtecblocs
Description: A pluggin to include specific functions which affects only to XTECBlocs
Version: 1.0
Author: Àrea TAC - Departament d'Ensenyament de Catalunya
*/

/**
 * Hide screen option's items. Best for usability
 * @author Sara Arjona
 */
function blocs_hidden_meta_boxes($hidden) {
	$hidden[] = 'postimagediv';
	return $hidden;
}

/**
 * Add the 'State' column at the end of the table, to manage the invitations.
 * @param  array $columns The columns of the table.
 * @return array $columns The same array with the column 'Estat' added.
 * @author vsaavedra
 */
function manage_users_columns( $columns ) {
	$columns['user_status'] = 'Estat';
	return $columns;
}
add_filter('manage_users_columns', 'manage_users_columns');


/**
 * Loads XTEC custom CSS
 * @author jmiro227 (2014.11.06)
 */
function register_xtec_common_styles() {
	wp_register_style( 'xtec_common_styles', get_site_url(1).'/xtec-style.css' );
	wp_enqueue_style( 'xtec_common_styles' );
}
add_action( 'wp_enqueue_scripts', 'register_xtec_common_styles' );


/**
* Replace "es.scribd.com" per "www.scribd.com" cause es.scribd.com doesn't work as a oEmbed provider
* I try to add as a oEmbed provider via wp_oembed_add_provider but doesn't work
*
* @author Xavi Meler
*/
function fix_spanish_scribd_oembed ($filtered_data, $raw_data){
	$filtered_data['post_content'] = str_replace('es.scribd.com', 'www.scribd.com', $filtered_data['post_content']);
	return $filtered_data;
}
add_filter('wp_insert_post_data', 'fix_spanish_scribd_oembed', 10, 2);

/**
* To avoid problems with BloggerImporter pluggin when the blog contains embed objects (like Picasa albums)
*
* @author sarjona
*/
remove_filter('force_filtered_html_on_import', '__return_true');

function dossier_duplicate_blog ($blog_id, $user_id, $domain, $path, $site_id, $meta) {
	echo "$blog_id, $user_id, $domain, $path, $site_id, $meta";
}
// TODO: Not used at the moment. Remove if finally it's not necessary;
//add_action( 'wpmu_new_blog', 'dossier_duplicate_blog', 10,  6);

/**
 * To initialitze some information in the form to create de blog
 *
 * @param array $signup_defaults {
 *     An array of default site sign-up variables.
 *
 *     @type string $blogname   The site blogname.
 *     @type string $blog_title The site title.
 *     @type array  $errors     An array possibly containing 'blogname' or 'blog_title' errors.
 * }
 * @author sarjona
 */
function dossier_signup_another_blog_init( $signup_defaults ) {
	global $current_user;

	$blog_title = (isset($current_user->user_firstname)?$current_user->user_firstname:'') . ' '. (isset($current_user->user_lastname)?$current_user->user_lastname:'');
	if ( empty($blog_title) ) $blog_title = $current_user->display_name;
	$signup_defaults['blogname'] = $current_user->user_login;
	$signup_defaults['blog_title'] = $blog_title;

	return $signup_defaults;
}
add_filter('signup_another_blog_init', 'dossier_signup_another_blog_init', 10, 1);

/**
 * Fires after the site sign-up form.
 *
 * @param array $errors An array possibly containing 'blogname' or 'blog_title' errors.
 *
 * @author sarjona
 */
function dossier_signup_blogform ( $errors ) {
	echo '<script type="text/javascript">
			document.forms["setupform"]["blogname"].readOnly = true;
			document.forms["setupform"]["blog_title"].readOnly = true;
		  </script>';
	echo 'CONDICIONS D\'ÚS -- PENDENT';
}
add_action('signup_blogform', 'dossier_signup_blogform');