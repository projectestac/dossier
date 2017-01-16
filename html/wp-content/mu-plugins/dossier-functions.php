<?php
/*
Plugin Name: DossierFunctions
Plugin URI: https://github.com/projectestac/dossier
Description: A pluggin to include specific functions which affects only to Dossier
Version: 1.0
Author: Àrea TAC - Departament d'Ensenyament de Catalunya
*/

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