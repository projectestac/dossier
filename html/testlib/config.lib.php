<?php

function sp_get_proxy() {
    global $CFG;
    if (isset($CFG->proxyhost)) {
        if (!empty($CFG->proxyhost)) {
            show_success('Moodle config taken');
            return $CFG->proxyhost.':'.$CFG->proxyport;
        } else {
            show_warning('Proxy not enabled in Moodle, not testing');
            return false;
        }
    }
    if (defined(WP_PROXY_HOST)) {
        $pr = WP_PROXY_HOST;
        if (!empty($pr)) {
            show_success('Wordpress config taken');
            return WP_PROXY_HOST.':'.WP_PROXY_PORT;
        } else {
            show_warning('Proxy not enabled in Wordpress, not testing');
            return false;
        }
    }
     show_warning('Proxy not configured, not testing');
    return false;
}

function sp_get_gicar_config() {
    if (function_exists('get_config') ) {
        $gicarconfig = get_config('auth/odissea');
        if (!empty($gicarconfig->gicar_host_url)) {
            return $gicarconfig;
        }
    }

    show_warning('Gicar not configured, not testing');
    return false;
}


function sp_get_ldap_config($env) {
    if (function_exists('get_config')) {
        $ldapconfig = get_config('auth/odissea');
        if (!empty($ldapconfig->host_url)) {
            show_success('Moodle odissea config taken');
            return $ldapconfig;
        }

        $ldapconfig = get_config('auth/ldap');
        if (!empty($ldapconfig->host_url)) {
            show_success('Moodle ldap config taken');
            return $ldapconfig;
        }
    }

    if (function_exists('get_site_option')) {
        $ldapconfig = new stdClass();
        $ldapconfig->host_url = get_site_option('xtec_ldap_host');
        $ldapconfig->ldap_version = get_site_option('xtec_ldap_version');
        $ldapconfig->bind_dn = get_site_option('xtec_ldap_base_dn');
        $ldapconfig->bind_pw = "";
        $ldapconfig->contexts = $ldapconfig->bind_dn;
        $ldapconfig->user_attribute = 'cn';
        if (!empty($ldapconfig->host_url)) {
            show_success('WordPress ldap config taken');
            return $ldapconfig;
        }
    }
    if (class_exists('ModUtil')) {
        $ldapconfig = new stdClass();
        $ldapconfig->host_url = ModUtil::getVar('AuthLDAP', 'authldap_serveradr');
        $ldapconfig->ldap_version = '3';
        $ldapconfig->bind_dn = ModUtil::getVar('AuthLDAP', 'authldap_basedn');
        $ldapconfig->bind_pw = "";
        $ldapconfig->contexts = ModUtil::getVar('AuthLDAP', 'authldap_searchdn');
        $ldapconfig->user_attribute = ModUtil::getVar('AuthLDAP', 'authldap_searchattr');
        if (!empty($ldapconfig->host_url)) {
            show_success('Zikula ldap config taken');
            return $ldapconfig;
        }
    }

    show_warning('LDAP not configured, not testing');
    return false;
}

function sp_get_mailsender_dir() {
    if (file_exists(__DIR__.'/../local/agora/mailer/')) {
        return __DIR__.'/../local/agora/mailer/';
    }
    if (file_exists(__DIR__.'/../moodle2/local/agora/mailer/')) {
        return __DIR__.'/../moodle2/local/agora/mailer/';
    }
    if (file_exists(__DIR__.'/../wp-content/plugins/xtec-mail/lib/')) {
        return __DIR__.'/../wp-content/plugins/xtec-mail/lib/';
    }
    if (file_exists(__DIR__.'/../modules/XtecMailer/includes/mailer/')) {
        return __DIR__.'/../modules/XtecMailer/includes/mailer/';
    }
    if (file_exists(__DIR__.'/../prestatgeria/modules/XtecMailer/includes/mailer/')) {
        return __DIR__.'/../prestatgeria/modules/XtecMailer/includes/mailer/';
    }
    show_warning('Mailsender not found, not testing...');
    return false;
}

function sp_get_ldap_default_password() {
    return 'Abcd1234';
}

function sp_get_gicar_default_password() {
    return '12345678';
}