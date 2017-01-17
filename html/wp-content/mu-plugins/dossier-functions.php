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

	// For filling default information (URL is the username and title is the first name and last name)
	$blog_title = (isset($current_user->user_firstname)?$current_user->user_firstname:'') . ' '. (isset($current_user->user_lastname)?$current_user->user_lastname:'');
	if ( empty($blog_title) ) $blog_title = $current_user->display_name;
	$signup_defaults['blogname'] = $current_user->user_login;
	$signup_defaults['blog_title'] = $blog_title;

	// To avoid error (because in this point, for dossier, blog is not created from a FORM)
	remove_filter( 'wpmu_validate_blog_signup', 'signup_nonce_check' );

	// For checking if the user has created his/her blog
	$result = wpmu_validate_blog_signup( $signup_defaults['blogname'], $signup_defaults['blog_title'], $current_user );

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
	// Block blogname and blogtitle to avoid user edit them
	echo '<script type="text/javascript">
			document.forms["setupform"]["blogname"].readOnly = true;
			document.forms["setupform"]["blog_title"].readOnly = true;
		  </script>';

	// TODO Show terms of use
	echo 'CONDICIONS D\'ÚS -- PENDENT';
}
add_action('signup_blogform', 'dossier_signup_blogform');

	/**
	 * Filter site details and error messages following registration.
	 *
	 * @param array $result {
	 *     Array of domain, path, blog name, blog title, user and error messages.
	 *
	 *     @type string         $domain     Domain for the site.
	 *     @type string         $path       Path for the site. Used in subdirectory installs.
	 *     @type string         $blogname   The unique site name (slug).
	 *     @type string         $blog_title Blog title.
	 *     @type string|WP_User $user       By default, an empty string. A user object if provided.
	 *     @type WP_Error       $errors     WP_Error containing any errors found.
	 * }
     * @author sarjona
	 */
function dossier_wpmu_validate_blog_signup( $result ) {
	// TODO check if user has it's own blog created or not

	return $result;
}
add_filter( 'wpmu_validate_blog_signup', 'dossier_wpmu_validate_blog_signup', 10, 1 );
