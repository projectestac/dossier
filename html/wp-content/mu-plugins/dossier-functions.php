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


function dossier_signup_blogform ( $errors ) {
	// Block blogname and blogtitle to avoid user edit them
	echo '<script type="text/javascript">
			document.forms["setupform"]["blogname"].readOnly = true;
			document.forms["setupform"]["blog_title"].readOnly = true;
		  </script>';

	// Show the option to accept the terms of use
    ?>
    <p>
        <label for="terms_of_use">
            <input type="checkbox" name="terms_of_use" id="terms_of_use" class="input" />
            <?php _e( 'I accept the terms of use', 'dossier-functions' ); ?>
        </label>
    </p>
    <?php
}
add_action('signup_blogform', 'dossier_signup_blogform');


function dossier_signup_another_blog( $blogname = '', $blog_title = '', $errors = '' ) {
    $current_user = wp_get_current_user();

    if ( ! is_wp_error($errors) ) {
        $errors = new WP_Error();
    }

    echo '<h2>' . __( 'Get your PLE blog', 'dossier-functions') . '</h2>';

    if ( $errors->get_error_code() == 'terms_of_use' ) {
        echo '<p>' . __( 'You must accept the terms of use to create the blog' ) . '</p>';
    } else {
        printf(__('Welcome %s. By filling out the form below, you can create your PLE blog.'), $current_user->display_name);
    }

    // Fill default information (URL is the username and title is the first name and last name)
    $blog_title = (isset($current_user->user_firstname)?$current_user->user_firstname:'') . ' '. (isset($current_user->user_lastname)?$current_user->user_lastname:'');

    if ( empty($blog_title) ) $blog_title = $current_user->display_name;
    $signup_defaults['blogname'] = $current_user->user_login;
    $signup_defaults['blog_title'] = $blog_title;

    // Avoid error (because in this point, for dossier, blog is not created from a form)
    remove_filter( 'wpmu_validate_blog_signup', 'signup_nonce_check' );

    // Check if the user has created their blog
    $filtered_results = wpmu_validate_blog_signup( $signup_defaults['blogname'], $signup_defaults['blog_title'], $current_user );

    $blogname = $filtered_results['blogname'];
    $blog_title = $filtered_results['blog_title'];
    $errors = $filtered_results['errors'];
    ?>

    <form id="setupform" method="post" action="wp-signup.php">
        <input type="hidden" name="stage" value="gimmeanotherblog" />
        <?php
        /**
         * Hidden sign-up form fields output when creating another site or user.
         *
         * @since MU
         *
         * @param string $context A string describing the steps of the sign-up process. The value can be
         *                        'create-another-site', 'validate-user', or 'validate-site'.
         */
        do_action( 'signup_hidden_fields', 'create-another-site' );
        ?>
        <?php show_blog_form($blogname, $blog_title, $errors); ?>
        <p class="submit"><input type="submit" name="submit" class="submit" value="<?php esc_attr_e( 'Create Site' ) ?>" /></p>
    </form>
    <?php
}



function dossier_validate_another_blog_signup() {
    global $wpdb, $blogname, $blog_title, $errors, $domain, $path;
    $current_user = wp_get_current_user();
    if ( ! is_user_logged_in() ) {
        die();
    }

    $terms = ( isset( $_POST['terms_of_use'] ) && ( $_POST['terms_of_use'] == 'on' )) ? true : false;

    if ( false === $terms ) {
        $terms_error = new WP_Error( 'terms_of_use', __( 'You must accept the terms of use', 'dossier-functions' ));
        dossier_signup_another_blog($blogname, $blog_title, $terms_error);
        return false;
    } else {
        update_user_meta( get_current_user_id(), 'terms_of_use', 'accepted' );
    }

    $result = validate_blog_form();

    // Extracted values set/overwrite globals.
    $domain = $result['domain'];
    $path = $result['path'];
    $blogname = $result['blogname'];
    $blog_title = $result['blog_title'];
    $errors = $result['errors'];

    if ( $errors->get_error_code() ) {
        dossier_signup_another_blog($blogname, $blog_title, $errors);
        return false;
    }

    $public = (int) $_POST['blog_public'];

    $blog_meta_defaults = array(
        'lang_id' => 1,
        'public'  => $public
    );

    // Handle the language setting for the new site.
    if ( ! empty( $_POST['WPLANG'] ) ) {

        $languages = signup_get_available_languages();

        if ( in_array( $_POST['WPLANG'], $languages ) ) {
            $language = wp_unslash( sanitize_text_field( $_POST['WPLANG'] ) );

            if ( $language ) {
                $blog_meta_defaults['WPLANG'] = $language;
            }
        }

    }

    /**
     * Filter the new site meta variables.
     *
     * @since MU
     * @deprecated 3.0.0 Use the 'add_signup_meta' filter instead.
     *
     * @param array $blog_meta_defaults An array of default blog meta variables.
     */
    $meta_defaults = apply_filters( 'signup_create_blog_meta', $blog_meta_defaults );

    /**
     * Filter the new default site meta variables.
     *
     * @since 3.0.0
     *
     * @param array $meta {
     *     An array of default site meta variables.
     *
     *     @type int $lang_id     The language ID.
     *     @type int $blog_public Whether search engines should be discouraged from indexing the site. 1 for true, 0 for false.
     * }
     */
    $meta = apply_filters( 'add_signup_meta', $meta_defaults );

    if ( defined( 'DOSSIER_MASTER_BLOG' ) ) {
        require_once MUCD_COMPLETE_PATH . '/lib/duplicate.php';
        $blog_title = (isset($current_user->user_firstname)?$current_user->user_firstname:'') . ' '. (isset($current_user->user_lastname)?$current_user->user_lastname:'');
        if ( empty($blog_title) ) $blog_title = $current_user->display_name;
        // Form Data
        $path = '/'.$current_user->user_login.'/';
        $data = array(
            'from_site_id'  => DOSSIER_MASTER_BLOG,  // The ID of the master blog to duplicate
            'domain'        => $current_user->user_login,
            'newdomain'     => $domain,
            'path'          => $path,
            'title'         => $blog_title,
            'email'         => $current_user->user_email,
            'copy_files'    => 'yes',
            'keep_users'    => 'no',
            'public'        => true,
            'log'           => 'no',
            'log-path'      => '',
            'advanced'      => 'hide-advanced-options',
            'network_id'    => $wpdb->siteid
        );
        // Duplicate blog
        $form_message = MUCD_Duplicate::duplicate_site($data);

        // Check if there were errors during creation
        if ( isset($form_message['error']) ) {
            new WP_Error('signup_duplication', $form_message['error'], $form_message['error']);
            return false;
        }

        $blog_id = isset( $form_message['site_id'] ) ? $form_message['site_id'] : 0;

    } else {
        $blog_id = wpmu_create_blog( $domain, $path, $blog_title, $current_user->ID, $meta, $wpdb->siteid );

        if ( is_wp_error( $blog_id ) ) {
            return false;
        }
    }

    confirm_another_blog_signup( $domain, $path, $blog_title, $current_user->user_login, $current_user->user_email, $meta, $blog_id );
    die();
}

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
        $blog_url = reset($blogs)->siteurl; // $blogs is an array of objects
        echo '<div id="signup-not-allowec" class="dossier-signup-not-allowed">';
        _e( 'You already have your personal blog', 'dossier-functions');
        echo ':<br /><a href="' . $blog_url . '" target="_blank">' . $blog_url . '</a>';
        echo '</div>';
    } else {
        $active_signup = $active_signup;
    }
    return $active_signup;
}
add_filter('wpmu_active_signup', 'dossier_one_blog_only');


/**
 * Class for adding a new field to the options-general.php page
 */
class Add_Settings_Field {

    /**
     * Class constructor
     */
    public function __construct() {
        add_action( 'admin_init' , array( $this , 'register_fields' ) );
    }

    /**
     * Add new fields to wp-admin/options-general.php page
     */
    public function register_fields() {
        register_setting( 'reading', 'extra_blog_description', 'esc_attr' );
        add_settings_field(
            'extra_blog_desc_id',
            __( 'Privacy' ),
            array( $this, 'fields_html' ),
            'reading'
        );
    }

    /**
     * HTML for extra settings
     */
    public function fields_html() {
        ?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e( 'Privacy' ); ?></span></legend>
        <label class="checkbox" for="blog-private-1">
            <input id="blog-private-1" type="radio" name="blog_public" value="-1" <?php checked( '-1', get_option( 'blog_public' ) ); ?> />
            <?php _e( 'Visible only to registered users of this network', 'dossier-functions' ); ?>
        </label>
        <br/>
        <label class="checkbox" for="blog-private-2">
            <input id="blog-private-2" type="radio" name="blog_public" value="-2" <?php checked( '-2', get_option( 'blog_public' ) ); ?> />
            <?php _e( 'Visible only to registered users of this site', 'dossier-functions' ); ?>
        </label>
        <br/>
        <label class="checkbox" for="blog-private-3">
            <input id="blog-private-3" type="radio" name="blog_public" value="-3" <?php checked( '-3', get_option( 'blog_public' ) ); ?> />
            <?php _e( 'Visible only to administrators of this site', 'dossier-functions' ); ?>
        </label>
        </fieldset>
        <?php
    }

}
new Add_Settings_Field();
