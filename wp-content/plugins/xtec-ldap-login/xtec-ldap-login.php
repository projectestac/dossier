<?php
/**
 * Plugin Name: XTEC LDAP Login
 * Plugin URI: https://github.com/projectestac/wordpress-xtec-ldap-login
 * Description: Overrides the core WordPress authentication method to allow the user authentication and registration through LDAP Server. It also adds an API function for a web service authentication
 * Version: 2.1
 * Author: Francesc Bassas & Toni Ginard
 * Author URI:
 */

const XTEC_DOMAIN = '@xtec.cat';

add_action('init', 'xtec_ldap_login_init');
add_filter('send_password_change_email', '__return_false');

/**
 * Plugin initialization
 */
function xtec_ldap_login_init() {
    // Localization
    load_plugin_textdomain('xtec-ldap-login', false, basename(dirname(__FILE__)) . '/languages/');

    // Check for WordPress multisite and add option to Dashboard
    if (is_multisite()) {
        add_action('network_admin_menu', 'xtec_ldap_login_network_admin_menu');
    } else {
        add_action('admin_menu', 'xtec_ldap_login_admin_menu');
    }

    add_filter('authenticate', 'xtec_ldap_authenticate', 10, 3); // Executed before standard filter
}

/**
 * Add plugin to settings in network admin menu
 */
function xtec_ldap_login_network_admin_menu() {
    add_submenu_page('settings.php', __('LDAP Login', 'xtec-ldap-login'), __('LDAP Login', 'xtec-ldap-login'), 'manage_network_options', 'ms-ldap-login', 'xtec_ldap_login_options');
}

/**
 * Add plugin to tools in admin menu
 */
function xtec_ldap_login_admin_menu() {
    add_submenu_page('tools.php', __('LDAP Login', 'xtec-ldap-login'), __('LDAP Login', 'xtec-ldap-login'), 'manage_options', 'ldap-login', 'xtec_ldap_login_options');
}

/**
 * Create options form and save data
 */
function xtec_ldap_login_options() {
	if (isset($_GET['action']) && $_GET['action'] == 'siteoptions') {
        if (isset($_POST['xtec_ldap_host'])) {
            $xtec_ldap_host = sanitize_text_field($_POST['xtec_ldap_host']);
            update_site_option('xtec_ldap_host', $xtec_ldap_host);
        }
        if (isset($_POST['xtec_ldap_port'])) {
            $xtec_ldap_port = sanitize_text_field($_POST['xtec_ldap_port']);
            update_site_option('xtec_ldap_port', $xtec_ldap_port);
        }
        if (isset($_POST['xtec_ldap_version'])) {
            $xtec_ldap_version = sanitize_text_field($_POST['xtec_ldap_version']);
            update_site_option('xtec_ldap_version', $xtec_ldap_version);
        }
        if (isset($_POST['xtec_ldap_base_dn'])) {
            $xtec_ldap_base_dn = sanitize_text_field($_POST['xtec_ldap_base_dn']);
            update_site_option('xtec_ldap_base_dn', $xtec_ldap_base_dn);
        }
        if (isset($_POST['xtec_ldap_login_type'])) {
            $xtec_ldap_login_type = sanitize_text_field($_POST['xtec_ldap_login_type']);
            update_site_option('xtec_ldap_login_type', $xtec_ldap_login_type);
        }
        ?>
        <div id="message" class="updated notice is-dismissible"><p><?php _e('Options saved.', 'xtec-ldap-login') ?></p></div>
    <?php
    }
    ?>
    <div class="wrap">
        <?php $page = (is_multisite()) ? 'ms-ldap-login' : 'ldap-login'; ?>
        <form method="post" action="?page=<?php echo $page; ?>&action=siteoptions">
            <h2><?php _e('XTEC LDAP Login', 'xtec-ldap-login') ?></h2>
            <table class="form-table">
                <tbody>
                    <?php if (is_xtec_super_admin()) { ?>
                    <tr valign="top">
                        <th scope="row"><?php _e('LDAP Host', 'xtec-ldap-login') ?></th>
                        <td><input type="text" size="50" name="xtec_ldap_host" value="<?php echo get_site_option('xtec_ldap_host'); ?>" /></td>
                    </tr>         
                    <tr valign="top">
                        <th scope="row"><?php _e('LDAP Port', 'xtec-ldap-login') ?></th>
                        <td><input type="text" size="50" name="xtec_ldap_port" value="<?php echo get_site_option('xtec_ldap_port'); ?>" /></td>
                    </tr>        
                    <tr valign="top">
                        <th scope="row"><?php _e('LDAP Version', 'xtec-ldap-login') ?></th>
                        <td><input type="text" size="50" name="xtec_ldap_version" value="<?php echo get_site_option('xtec_ldap_version'); ?>" /></td>
                    </tr>        
                    <tr valign="top">
                        <th scope="row"><?php _e('Base DN', 'xtec-ldap-login') ?></th>
                        <td><input type="text" size="50" name="xtec_ldap_base_dn" value="<?php echo get_site_option('xtec_ldap_base_dn'); ?>" /></td>
                    </tr>
                    <?php } ?>
                    <tr valign="top">
                        <th scope="row"><?php _e('Validation Type', 'xtec-ldap-login') ?></th>
                        <?php
                        $xtec_ldap_login_type = get_site_option('xtec_ldap_login_type', 'LDAP');
                        ?>
                        <td>
                            <p>
                                <label>
                                    <input type="radio" name="xtec_ldap_login_type" value="LDAP" 
                                    <?php if ($xtec_ldap_login_type == 'LDAP') {
                                        echo 'checked="checked"';
                                    } ?>
                                    />
                                    <?php _e('LDAP', 'xtec-ldap-login'); ?>
                                </label>
                            </p>
                            <p class="description">
                                <?php _e('The user is validated through the LDAP server. If the user enters for the first time and validates, the application registers it. First attempt to validate as user of LDAP server and then if fails attempt to validate as user of the application. <strong>IMPORTANT: When LDAP is on, any XTEC user can log in.</strong>', 'xtec-ldap-login'); ?>
                            </p>
                            <br />
                            <p>
                                <label>
                                    <input type="radio" name="xtec_ldap_login_type" value="Application Data Base"
                                    <?php if ($xtec_ldap_login_type == 'Application Data Base') {
                                        echo 'checked="checked"'; 
                                    } ?>
                                    />
                                </label>
                                <?php _e('Application Data Base', 'xtec-ldap-login'); ?>
                            </p>
                            <p class="description">
                                <?php _e('The user is validated through Application Data Base', 'xtec-ldap-login'); ?>
                            </p>
                        </td>
                    </tr>        
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-large button-primary" value="<?php _e('Save', 'xtec-ldap-login'); ?>" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Checks a user's login information and it tries to log them in through LDAP 
 * server or locally depending on plugin configuration. Usernames longer than 
 * 8 chars or having edu365 domain or called 'admin', always log in locally.
 * Any existing user whose e-mail is XTEC, will always log in through LDAP if
 * it is activated. Users that validate successfully via LDAP who doesn't exist 
 * locally, are created using WordPress API.
 *
 * @param WP_User $user
 * @param string $username User's username
 * @param string $password User's password
 * @return WP_Error|WP_User WP_User object if login successful, otherwise WP_Error object.
 */
function xtec_ldap_authenticate($user, $username, $password) {

    if (is_a($user, 'WP_User')) {
        return $user;
    }
    
    // Remove standard authentication only in XTECBlocs.
    if (is_xtecblocs()) {
        remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
    }

    if (empty($username) || empty($password)) {
        $error = new WP_Error();

        if (empty($username)) {
            $error->add('empty_username', __('The username is empty.', 'xtec-ldap-login'));
        }
        if (empty($password)) {
            $error->add('empty_password', __('The password is empty.', 'xtec-ldap-login'));
        }

        return $error;
    }

    // Filter username to remove trailing '@xtec.cat' in case it exists
    if (strpos($username, XTEC_DOMAIN)) {
        $username = substr($username, 0, -strlen(XTEC_DOMAIN));
    }

    // Check if user exists in wp_users
    $user_info = get_user_by('login', $username);
    
    // If cannot find user_login in wp_users, look for any user with @xtec.cat e-mail
    if ($user_info === false) {
        $user_info = get_user_by('email', $username . XTEC_DOMAIN);
    }
    
    // In some cases always do local login (admin and @edu365.cat)
    if ($user_info &&
            ((strlen($username) > 8) ||
            ($username == 'admin') ||
            (is_xtecblocs() && preg_match("/^.+@edu365\.cat$/", $user_info->user_email)))
            ) {
        if (!wp_check_password($password, $user_info->user_pass, $user_info->ID)) {
            do_action('wp_login_failed', $username);
            return new WP_Error('incorrect_password', __('The password is not correct', 'xtec-ldap-login'));
        }

        return new WP_User($user_info->ID);
    }

    $xtec_ldap_login_type = get_site_option('xtec_ldap_login_type');
    $xtec_ldap_host = get_site_option('xtec_ldap_host');
    $xtec_ldap_port = get_site_option('xtec_ldap_port');
    $xtec_ldap_version = get_site_option('xtec_ldap_version');
    $xtec_ldap_base_dn = get_site_option('xtec_ldap_base_dn');

    if ($xtec_ldap_login_type == 'LDAP') {
        // Verify credentials through LDAP
        $ldap_conn = ldap_connect($xtec_ldap_host, $xtec_ldap_port);
        if ($ldap_conn === false) {
            return new WP_Error('ldap_connection', __('Could not connect to LDAP server', 'xtec-ldap-login'));
        }

        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, $xtec_ldap_version);

        $ldap_bind = @ldap_bind($ldap_conn, 'cn=' . $username . ',' . $xtec_ldap_base_dn, $password);

        if ($ldap_bind === false) {
            // If LDAP fails, in Nodes, this do_action activates the local login. In XTECBlocs, 
            // it was previously removed, so this do_action has no effect
            do_action('wp_login_failed', $username);
            return new WP_Error('incorrect_password', __('Could not bind to the LDAP directory. The username, the password or both are not correct', 'xtec-ldap-login'));
        }

        $result = ldap_search($ldap_conn, $xtec_ldap_base_dn, '(cn=' . $username . ')', array('cn', 'sn', 'givenname', 'mail'));
        $ldap_user = ldap_get_entries($ldap_conn, $result);

        // If user does not exist in wp_users and the credentials are valid in LDAP, create the local user
        if (!$user_info && ($ldap_user['count'] == 1)) {
            // Create user using wp standard API
            $user_data = array(
                'user_pass' => $password,
                'user_login' => $username,
                'user_nicename' => $ldap_user[0]['givenname'][0] . ' ' . $ldap_user[0]['sn'][0],
                'user_email' => $ldap_user[0]['mail'][0],
                'display_name' => $ldap_user[0]['givenname'][0] . ' ' . $ldap_user[0]['sn'][0],
                'first_name' => $ldap_user[0]['givenname'][0],
                'last_name' => $ldap_user[0]['sn'][0],
            );

            // In Àgora will use the default role of WordPress
            if (is_xtecblocs()) {
                $user_data['role'] = strtolower('subscriber');
            }

            // Insert the user into the database (creates it)
            $user_id = wp_insert_user($user_data);

            // Set user metadata required for XTECBlocs
            $domain = strstr($ldap_user[0]['mail'][0], '@');
            if ($domain == XTEC_DOMAIN) {
                update_user_meta($user_id, 'xtec_user_creator', 'LDAP_XTEC');
            }

            if (is_wp_error($user_id)) {
                return new WP_Error($user_id->get_error_code(), $user_id->get_error_message());
            }

            // Do the actual validation
            return new WP_User($user_id);
        } else {
            if (is_multisite()) {
                // Is user marked as spam?
                if (1 == $user_info->spam) {
                    return new WP_Error('invalid_username', __('Your account has been marked as a spammer', 'xtec-ldap-login'));
                }
                // Is a user's blog marked as spam?
                if (!is_super_admin($user_info->ID) && isset($user_info->primary_blog)) {
                    $details = get_blog_details($user_info->primary_blog);
                    if (is_object($details) && $details->spam == 1) {
                        return new WP_Error('blog_suspended', __('Site Suspended', 'xtec-ldap-login'));
                    }
                }
            }

            $user_info = apply_filters('wp_authenticate_user', $user_info, $password);

            if (is_wp_error($user_info)) {
                return new WP_Error($user_info->get_error_code(), $user_info->get_error_message());
            }

            // Update the password if it has changed
            if (!wp_check_password($password, $user_info->user_pass, $user_info->ID)) {
                wp_update_user(array('ID' => $user_info->ID, 'user_pass' => $password));
            }

            if ($ldap_user['count'] == 1) {
                $domain = strstr($ldap_user[0]['mail'][0], '@');
                if ($domain == XTEC_DOMAIN) {
                    // Ensure the user metadata is set, as it is required to create blogs in XTECBlocs
                    update_user_meta($user_info->ID, 'xtec_user_creator', 'LDAP_XTEC');
                }
            }

            // Do the actual validation
            return new WP_User($user_info->ID);
        }
    } else { // get_site_option('xtec_ldap_login_type') == "Application Data Base")
        if (!wp_check_password($password, $user_info->user_pass, $user_info->ID)) {
            return new WP_Error('incorrect_password', sprintf(__('The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?', 'xtec-ldap-login'), $username, site_url('wp-login.php?action=lostpassword', 'login')));
        }

        // Do the actual validation
        return new WP_User($user_info->ID);
    }
}

/**
 * This function is only used by xtec-external-authentication.php in XTECBlocs. 
 * Checks a user login information and tries to authenticate them in through the
 * LDAP Server or through the application database if it fails.
 *
 * @param string $username User's username
 * @param string $password User's password
 * @return '1$$usermail' if user is a XTEC user, 
 *         '2$$usermail' if user is not a XTEC user, 
 *         '101' if username's empty, 
 *         '102' if password's empty, 
 *         '103' if username's incorrect,
 *         '104' if password's incorrect.
 */
function xtec_authenticate($username, $password) {

    if ('' == $username) {
        return 101;
    }

    if ('' == $password) {
        return 102;
    }

    $user = get_userdatabylogin($username);

    if (!$user || (strtolower($user->user_login) != strtolower($username) )) {
        return 103;
    }

    if (!wp_check_password($password, $user->user_pass, $user->ID)) {
        return 104;
    } else {
        if (get_user_meta($user->ID, 'xtec_user_creator', true) == 'LDAP_XTEC') {
            return 1 . '$$' . $user->user_email;
        } else {
            return 2 . '$$' . $user->user_email;
        }
    }
}
