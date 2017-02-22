<?php
/*
Plugin Name: DossierFunctions
Plugin URI: https://github.com/projectestac/dossier
Description: A plugin to include specific functions which affects to Dossier only
Version: 1.0
Author: Àrea TAC - Departament d'Ensenyament de Catalunya
*/

CONST NUM_ALLOWED_BLOGS_PER_USER = 1;

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
 * Check the number of blogs of a user and disable the signup if they already have the allowed blog
 *
 * @param string $active_signup Registration type. The value can be 'all', 'none', 'blog', or 'user'.
 * @return string $active_signup Registration type, modified if condition is met
 *
 * @author Toni Ginard
 */
function dossier_one_blog_only($active_signup) {
    // Get the array of the current user's blogs
    $blogs = get_blogs_of_user(get_current_user_id());

    // All users may be members of blog 1 so remove it from the list
    if ( !empty ($blogs) && isset( $blogs[ '1' ] )) {
        unset ( $blogs[ '1' ]);
    }

    // If the user still has blogs, disable sign up else continue with existing active_signup rules at SiteAdmin->Options
    $n = count( $blogs );
    if ( $n >= NUM_ALLOWED_BLOGS_PER_USER ) {
        $active_signup = 'none';
        echo '<div id="signup-not-allowec" class="dossier-signup-not-allowed">' . __( 'You already have your personal blog', 'dossier-functions') . '</div>';
    } else {
        $active_signup = $active_signup;
    }
    return $active_signup;
}
add_filter('wpmu_active_signup', 'dossier_one_blog_only');
