<?php

function checkMySQL ($dbhost, $dbport, $dbname, $user, $dbpass, $table = '') {

    if (empty($dbhost)) {
        $dbhost = 'localhost';
    }
    if (empty($dbport)) {
        $dbport = 3306;
    }
    show_header('checkMySQL '.$dbname);
    $con = new mysqli($dbhost, $user, $dbpass, $dbname, $dbport);
    if ($con->connect_error) {
        $error = $con->connect_error;
        show_error('Error checkMySQL'. $error);
        return;
    }

    if (!empty($table)) {
        $sql = 'SELECT * FROM `'.$table .'` LIMIT 0, 1';

        $pid = $con->query($sql);
        if (!empty($pid)) {
            show_success('Connected to DB ' . $dbname . ' - table ' . $table);
        } else {
            show_error('Error connecting to DB ' . $dbname . ' ' . $con->error);
        }
    } else {
        show_success('Connected to DB ' . $dbname . '. No table checked');
    }

    $con->close();
}

function checkOracle ($dbname, $user, $dbpass, $dbprefix) {
    show_header('checkOracle '.$dbname.'-'.$user);
    try {
        $isok = false;
        $con = oci_pconnect($user, $dbpass, $dbname);

        // If connection was established successfully, check access to tables
        if (!$con) {
            show_error('No s\'ha pogut connectar a la inst&agrave;ncia '.$dbname.' (usuari ' . $user. ')');
            return;
        }

        if ($con) {
            $sql = 'SELECT count(*) FROM ' . $dbprefix . 'course WHERE category = 0';

            $stmt = oci_parse($con, $sql);

            if (!oci_execute($stmt, OCI_DEFAULT)) {
                return false;
            }

            while (oci_fetch($stmt)) {
                $result = oci_result($stmt, 1);
                if (is_numeric($result) && $result > 0) {
                    $isok = true;
                } else {
                    show_error('No s\'ha pogut accedir a la taula ' . $dbprefix . 'course de l\'usuari '.$user);
                    oci_close($con);
                    return;
                }
            }

            oci_close($con);
        }

        if ($isok) {
            show_success('Connected to '.$dbname.'-'.$user);
        } else {
            show_error('Error connecting to '.$dbname.'-'.$user);
        }
    } catch (Exception $e) {
        show_error('Error checkOracle '. $e->getMessage());
    }
}

