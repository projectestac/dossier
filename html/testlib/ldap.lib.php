<?php

function test_gicar($username = false) {

    if (!empty($_REQUEST['gicarusername'])) {
        $username = $_REQUEST['gicarusername'];
    } else if (!empty($_REQUEST['user'])) {
        $username = $_REQUEST['user'];
    } else if (!$username) {
        show_warning('User not set, using agora');
        $username = 'agora';
    }
    $userpwd = !empty($_REQUEST['gicaruserpwd']) ? $_REQUEST['gicaruserpwd'] : sp_get_gicar_default_password();

    show_header('Test Gicar for '.$username);

    $gicarconfig = sp_get_gicar_config();
    if (!$gicarconfig) {
        return;
    }

    $host = $gicarconfig->gicar_host_url;
    $version = $gicarconfig->gicar_ldap_version;
    $binddn = $gicarconfig->gicar_bind_dn;
    $bindpw = $gicarconfig->gicar_bind_pw;
    $contexts = $gicarconfig->gicar_contexts;
    $nifattribute = $gicarconfig->gicar_nif_attribute;
    $userattribute = $gicarconfig->gicar_user_attribute;
    $ldap = testapp_ldap::instance_gicar($host, $version, 'default', $binddn, $bindpw);
    if (!$ldap) {
        show_error('Error connecting GICAR');
        return;
    }

    $result = ldap_search($ldap->connection, $contexts, '(' . $nifattribute . '=' . $username . ')', array($userattribute));
    if (!$result) {
        show_error('Result not found');
        return;
    }
    $entry = ldap_first_entry($ldap->connection, $result);
    if (!$entry) {
        show_error('Entry not found');
        return;
    }

    if (!$idarr = ldap_get_values($ldap->connection, $entry, $userattribute)) {
        show_error('Error on ldap_get_values');
        return;
    }
    $id = $idarr[0];

    show_success('Usuari trobat '. $id);

    $userdn = $ldap->find_userdn($id, $contexts, $userattribute);
    if (!$userdn) {
        show_error('Error on ldap_find_userdn');
        return;
    }
    show_success($userdn);

    $ldaplogin = $ldap->login($userdn, $userpwd);
    if (!$ldaplogin) {
        show_error('Login incorrecte');
    }
    show_success('Logged in!');

    $ldap->close();
}

function test_ldap($username = false, $env = 'INT') {
    if (!empty($_REQUEST['ldapusername'])) {
        $username = $_REQUEST['ldapusername'];
    } else if (!empty($_REQUEST['user'])) {
        $username = $_REQUEST['user'];
    } else if (!$username) {
        show_warning('User not set, using agora');
        $username = 'agora';
    }
    $userpwd = !empty($_REQUEST['ldapuserpwd']) ? $_REQUEST['ldapuserpwd'] : sp_get_ldap_default_password();

    show_header('Test LDAP for '.$username);

    $ldapconfig = sp_get_ldap_config($env);
    if (!$ldapconfig) {
        return;
    }
    $host = $ldapconfig->host_url;
    $version = $ldapconfig->ldap_version;
    $binddn = $ldapconfig->bind_dn;
    $bindpw = $ldapconfig->bind_pw;
    $contexts = $ldapconfig->contexts;
    $userattribute = $ldapconfig->user_attribute;
    $binddnxtec = $userattribute . '=' . $username . ',' . $binddn;
    $ldap = testapp_ldap::instance_ldap($host, $version, 'default', $binddnxtec, $userpwd);
    if (!$ldap) {
        show_error('Error connecting LDAP');
        return;
    }

    show_success('Usuari trobat '. $username);

    $userdn = $ldap->find_userdn($username, $contexts, $userattribute);
    if (!$userdn) {
        show_error('Error on ldap_find_userdn');
        return;
    }
    show_success($userdn);
    $ldaplogin = $ldap->login($userdn, $userpwd);
    if (!$ldaplogin) {
        show_error('Login incorrecte');
    }
    show_success('Logged in!');

    $ldap->close();
}



class testapp_ldap {

    public $connection;

    public static function instance_ldap($hosturl, $ldapversion, $usertype, $binddn, $bindpw, $optderef = LDAP_DEREF_NEVER, $starttls=false) {
        static $instldap = null;
        if ($instldap === null) {
            $instldap = self::get_instance($hosturl, $ldapversion, $usertype, $binddn, $bindpw, $optderef, $starttls);
        }
        return $instldap;
    }

    public static function instance_gicar($hosturl, $ldapversion, $usertype, $binddn, $bindpw, $optderef = LDAP_DEREF_NEVER, $starttls=false) {
        static $instgicar = null;
        if ($instgicar === null) {
            $instgicar = self::get_instance($hosturl, $ldapversion, $usertype, $binddn, $bindpw, $optderef, $starttls);
        }
        return $instgicar;
    }

    private static function get_instance($hosturl, $ldapversion, $usertype, $binddn, $bindpw, $optderef, $starttls) {
        if (empty($hosturl) || empty($ldapversion) || empty($usertype)) {
            show_error('No LDAP Host URL, Version or User Type specified in your LDAP settings');
            return false;
        }
        $urls = explode(';', $hosturl);
        foreach ($urls as $server) {
            $server = trim($server);
            if (empty($server)) {
                continue;
            }

            $connresult = ldap_connect($server); // ldap_connect returns ALWAYS true

            if (!empty($ldapversion)) {
                ldap_set_option($connresult, LDAP_OPT_PROTOCOL_VERSION, $ldapversion);
            }

            // Fix MDL-10921
            if ($usertype === 'ad') {
                ldap_set_option($connresult, LDAP_OPT_REFERRALS, 0);
            }

            if (!empty($optderef)) {
                ldap_set_option($connresult, LDAP_OPT_DEREF, $optderef);
            }

            if ($starttls && (!ldap_start_tls($connresult))) {
                show_error("Server: '$server', Connection: '$connresult', STARTTLS failed.");
                continue;
            }

            if (!empty($binddn)) {
                $bindresult = ldap_bind($connresult, $binddn, $bindpw);
            } else {
                // Bind anonymously
                $bindresult = ldap_bind($connresult);
            }

            if ($bindresult) {
                return new testapp_ldap($connresult);
            }
            show_error("Server: '$server', Connection: '$connresult', Bind result: '$bindresult'");
        }
        return false;
    }


    private function __construct($connresult) {
        $this->connection = $connresult;
    }

    public function find_userdn($username, $contexts, $searchattrib, $searchsub = "") {
        $objectclass = '(objectClass=*)';

        if (empty($this->connection) || empty($username) || empty($contexts) || empty($objectclass) || empty($searchattrib)) {
            return false;
        }

        // Default return value
        $userdn = false;
        $contexts = explode(';', $contexts);
        // Get all contexts and look for first matching user
        foreach ($contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }

            if ($searchsub) {
                $result = ldap_search($this->connection, $context,
                                            '(&'.$objectclass.'('.$searchattrib.'='.self::filter_addslashes($username).'))',
                                            array($searchattrib));
            } else {
                $result = ldap_list($this->connection, $context,
                                          '(&'.$objectclass.'('.$searchattrib.'='.self::filter_addslashes($username).'))',
                                          array($searchattrib));
            }

            if (!$result) {
                continue; // Not found in this context.
            }

            $entry = ldap_first_entry($this->connection, $result);
            if ($entry) {
                $userdn = ldap_get_dn($this->connection, $entry);
                break;
            }
        }

        return $userdn;
    }

    public function login($userdn, $userpwd) {
        return @ldap_bind($this->connection, $userdn, $userpwd);
    }

    public function close() {
        ldap_close($this->connection);
    }

    private static function filter_addslashes($text) {
        $text = str_replace('\\', '\\5c', $text);
        $text = str_replace(array('*',    '(',    ')',    "\0"),
                            array('\\2a', '\\28', '\\29', '\\00'), $text);
        return $text;
    }
}
