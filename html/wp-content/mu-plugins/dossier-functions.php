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
 * Extra form content for signup blog form
 * @param $errors
 *
 * @author Sara Arjona
 */
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

/**
 * Custom function to replace signup_another_blog() in wp-signup.php
 *
 * @author Toni Ginard
 */
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
        <?php do_action( 'signup_hidden_fields', 'create-another-site' ); // Required for nonce check ?>
        <?php show_blog_form($blogname, $blog_title, $errors); ?>
        <p class="submit"><input type="submit" name="submit" class="submit" value="<?php esc_attr_e( 'Create Site' ) ?>" /></p>
    </form>
    <?php
}

/**
 * Custom function to replace validate_another_blog_signup() in wp-signup.php
 *
 * @author Toni Ginard
 * @author Sara Arjona
 */
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

    $meta_defaults = array(
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
        $blog_title = ( isset( $current_user->user_firstname ) ? $current_user->user_firstname : '' ) . ' '. ( isset( $current_user->user_lastname ) ? $current_user->user_lastname : '' );
        if ( empty($blog_title) ) { $blog_title = $current_user->display_name; }

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

        // Save value of xtec_blog_public chosen by the user to wp_options
        switch_to_blog( $blog_id );
        update_option( 'xtec_blog_public', $_POST['xtec_blog_public'] );
        restore_current_blog();

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
 * Block access to blog creation if the user already have their allowed blog
 *
 * @param string $active_signup Registration type. The value can be 'all', 'none', 'blog', or 'user'.
 * @return string $active_signup Registration type, modified if condition is met
 *
 * @author Toni Ginard
 */
function dossier_one_blog_only($active_signup) {
    $current_user_id = get_current_user_id();
    $user_login = get_userdata( $current_user_id )->data->user_login;
    $blogs = get_blogs_of_user( $current_user_id );

    foreach ( $blogs as $blog ){
        if ( trim( $blog->path, '/' ) == $user_login ) {
            $active_signup = 'none';
            echo '<div id="signup-not-allowec" class="dossier-signup-not-allowed">';
            _e( 'You already have your personal blog', 'dossier-functions');
            echo ': <a href="' . $blog->siteurl . '" target="_blank">' . $blog->siteurl . '</a>';
            echo '</div>';
        }
    }

    return $active_signup;
}
add_filter('wpmu_active_signup', 'dossier_one_blog_only');


/**
 * Class to add a new field to the options | reading page
 *
 * @author Toni Ginard
 */
class dossier_add_settings_field {

    public function __construct() {
        add_action( 'admin_init', array( $this , 'register_fields' ));
        add_action( 'signup_blogform', array( $this, 'fields_html' ), 1 );
    }

    public function register_fields() {
        register_setting( 'reading', 'extra_blog_description', 'esc_attr' );
        add_settings_field( 'extra_blog_desc_id', __( 'Privacy' ), array( $this, 'fields_html' ), 'reading' );
    }

    /**
     * HTML for the extra setting
     */
    public function fields_html() {

        $xtec_blog_public = get_option( 'xtec_blog_public', false );
        $xtec_blog_public = ( false === $xtec_blog_public ) ? 1 : $xtec_blog_public;

        ?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e( 'Privacy' ); ?></span></legend>
        <label class="checkbox" for="blog-private-1">
            <input id="blog-private-1" type="radio" name="xtec_blog_public" value="1" <?php if ( '1' == $xtec_blog_public ) { echo 'checked="checked"' ; } ?> />
            <?php _e( 'Visible to everybody (public)', 'dossier-functions' ); ?>
        </label>
        <br/>
        <label class="checkbox" for="blog-private-2">
            <input id="blog-private-2" type="radio" name="xtec_blog_public" value="2" <?php if ( '2' == $xtec_blog_public ) { echo 'checked="checked"' ; }  ?> />
            <?php _e( 'Visible only to XTEC users (restricted)', 'dossier-functions' ); ?>
        </label>
        <br/>
        <label class="checkbox" for="blog-private-3">
            <input id="blog-private-3" type="radio" name="xtec_blog_public" value="3" <?php if ( '3' == $xtec_blog_public ) { echo 'checked="checked"' ; }  ?> />
            <?php _e( 'Visible only to the owner (private)', 'dossier-functions' ); ?>
        </label>
        </fieldset>
        <?php
    }

}
new dossier_add_settings_field();

/**
 * Save extra param to wp_options
 *
 * @param $whitelist_options
 * @return mixed
 *
 * @author Toni Ginard
 */
function dossier_save_extra_options( $whitelist_options ) {

    $whitelist_options['reading'][] = 'xtec_blog_public';

    return $whitelist_options;
}
add_filter( 'whitelist_options', 'dossier_save_extra_options' );

/**
 * Access control to the blog. Blocks access depending on the privacy configuration
 *
 * @author Toni Ginard
 */
function dossier_access_control() {
    $xtec_blog_public = get_option( 'xtec_blog_public' );

    // Block access to anonymous access. Done here to avoid duplication of code
    if ( ( 2 == $xtec_blog_public ) || ( 3 == $xtec_blog_public )) {
        $current_user_id = get_current_user_id();
        if ( 0 == $current_user_id ) {
            add_action( 'template_redirect', 'auth_redirect' ); // auth_redirect is a WordPress core function
            add_action( 'login_form', 'dossier_redirect_login_message' );
            return ;
        }
        $user = wp_get_current_user();
        $is_validator = ( in_array( 'validator', (array) $user->roles ) );
    }

    switch ($xtec_blog_public) {
        case '1': // Public (Nothing done at the moment)
            break;

        case '2': // Access only for xtec users and network admins
            $is_xtec_address = ( substr( get_userdata( $current_user_id )->data->user_email, -( strlen( '@xtec.cat' ))) === '@xtec.cat' );

            // Allow access to XTEC Users and network admins
            if ( !$is_xtec_address && !is_super_admin() && !$is_validator ) {
                wp_die( sprintf( __( 'This site is only available to XTEC users. Please go to <a href="%1$s">main site</a> to log in again.', 'dossier-functions' ), network_site_url() ));
            }
            break;

        case '3': // Access only to owner and network admins
            $user_name = get_userdata( $current_user_id )->data->user_login;
            $owner = trim( get_blog_details( get_current_blog_id() )->path, '/' );

            if ( ( $owner !== $user_name ) && !is_super_admin() && !$is_validator ) {
                wp_die( sprintf( __( 'You are logged as %1$s, but this site is only available to its owner (%2$s). Go to <a href="%3$s">main site</a>.', 'dossier-functions' ), $user_name, $owner, network_site_url() ));
            }
            break;
    }

    return ;
}
add_action( 'init', 'dossier_access_control' );
remove_action ( 'wp_login', 'dossier_access_control' ); // Deactivate access control in login page

/**
 * Shows a message to inform about the reason why the blog cannot be accessed. Called from dossier_access_control
 *
 * @author Toni Ginard
 */
function dossier_redirect_login_message() {
    $xtec_blog_public = get_option( 'xtec_blog_public' );

    switch ($xtec_blog_public) {
        case '2':
            $message = __( 'Access to this site is restricted to XTEC users. Please log in using an XTEC account to continue.', 'dossier-functions' );
            break;
        case '3':
            $message = __( 'Access to this site is restricted to the site owner. Please log in using your XTEC account if you are the owner.', 'dossier-functions' );
            break;
        default:
            $message = '';
    }

    echo '<div style="margin: 5px 0 10px 0; padding: 5px; border: 2px solid #ffb900; color: #ffb900; font-weight: bold;">' . $message . '</div>';

    return ;
}

/**
 * Adds the validator role
 *
 * @author Toni Ginard
 */
function add_validator() {
    add_role( 'validator', __( 'Validator', 'dossier-functions' ), array(
        'read' => true,
        'read_private_pages' => true,
        'read_private_posts' => true,
    ) );
}
add_action( 'init', 'add_validator' );

/**
 * If user is validator (global flag) automatically add this role to the user in the blog where they are logging in
 * @param $user_login
 * @param $user
 *
 * @author Toni Ginard
 */
function dossier_set_as_validator( $user_login, $user ) {
    $xtec_is_validator = get_user_meta( $user->ID, 'xtec_is_validator' );

    if ($xtec_is_validator) {
        if ( ! in_array( 'editor', $user->roles) && ! in_array( 'administrator', $user->roles) || ! is_super_admin() ) {
            $user->add_role( 'validator' );
        }
    }
}
add_action( 'wp_login', 'dossier_set_as_validator', 999, 2 );

/**
 * When a user is validator and visits another blog after logging, add the validator role in the new blog
 *
 * @author Toni Ginard
 */
function dossier_switch_blog() {
    $current_user_id = get_current_user_id();
    $xtec_is_validator = get_user_meta($current_user_id, 'xtec_is_validator');

    if ( $xtec_is_validator ) {
        $user = get_user_by('id', $current_user_id);
        if ( ! in_array( 'editor', $user->roles) && ! in_array( 'administrator', $user->roles) || ! is_super_admin() ) {
            $user->add_role( 'validator' );
        }
    }
}
add_action( 'switch_blog', 'dossier_switch_blog', 999 );

/**
 * When a user is validator and logs out, remove the role from all the blogs where they have a role assigned
 *
 * @author Toni Ginard
 */
function dossier_unset_as_validator() {
    $current_user_id = get_current_user_id();
    $xtec_is_validator = get_user_meta($current_user_id, 'xtec_is_validator');

    if ( $xtec_is_validator ) {
        $user = get_user_by( 'id', $current_user_id );
        $user_blogs = get_blogs_of_user( $current_user_id );

        // When user logs out, remove their validator role from all the blogs (in case is set)
        foreach ( $user_blogs as $blog ) {
            switch_to_blog( $blog->userblog_id );
            if (in_array( 'validator', $user->roles )) {
                $user->remove_role( 'validator' );
            }
            restore_current_blog();
        }
    }
}
add_action( 'wp_logout', 'dossier_unset_as_validator' );

/**
 * Add column validator in wp-admin/network/users.php
 *
 * @param $users_columns
 * @return mixed
 *
 * @author Toni Ginard
 */
function dossier_ms_users_list_add_column ($users_columns) {
    $users_columns['validator'] = __( 'Validator', 'dossier-functions' );
    return $users_columns;
}
add_filter( 'wpmu_users_columns', 'dossier_ms_users_list_add_column' );

/**
 * Add option to grant validator privileges in wp-admin/network/user-edit.php
 *
 * @param $profileuser
 *
 * @author Toni Ginard
 */
function dossier_add_validator_option_form ( $profileuser ) {
    $validator = get_user_meta( $profileuser->ID, 'xtec_is_validator' );
    $xtec_is_validator = ( is_array( $validator )) ? reset( $validator ) : '0';
    ?>
    <table class="form-table">
    <tr>
        <th><?php _e( 'Validator', 'dossier-functions' ); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text"></legend>
                <label class="checkbox" for="is-validator">
                <input id="is-validator" type="checkbox" name="is_validator" <?php if ( '1' == $xtec_is_validator ) { echo 'checked="checked"' ; } ?> />
                <?php _e( 'Grant this user validator capabilities', 'dossier-functions' ); ?>
                </label>
            </fieldset>
        </td>
    </tr>
    </table>
    <?php
}
add_action( 'edit_user_profile' , 'dossier_add_validator_option_form' );

/**
 * Save validator flag in wp_usermeta
 *
 * @param $user_id
 *
 * @author Toni Ginard
 */
function dossier_update_validator_flag( $user_id ) {
    if ( isset( $_POST['is_validator'] ) && ( $_POST['is_validator'] == 'on' )) {
        update_user_meta( $user_id, 'xtec_is_validator', '1');
    } else {
        update_user_meta( $user_id, 'xtec_is_validator', '0');
    }
}
add_action ( 'edit_user_profile_update', 'dossier_update_validator_flag' );
