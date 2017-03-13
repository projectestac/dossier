<?php

function test_memcache() {
    show_header('test_memcache');
    try {
        $meminstance = new Memcache();
        $meminstance->pconnect('localhost', 11211);
        $meminstance->set('test_memcache', 'Memcache Ok', 0, 600);
        $result = $meminstance->get('test_memcache');
        if ($result) {
            show_success($result);
        } else {
            show_error('Memcache is not working');
        }
    } catch (Exception $e) {
        show_error('Error memcache '. $e->getMessage());
        return;
    }
}

function test_session() {
    show_header('test_session');
    @session_start();

    if (isset($_SESSION['test'])) {
        show_success($_SESSION['test']);
    } else {
        show_warning('Session not initialized, F5 to test');
        $_SESSION['test'] = 'Session OK';
    }
}

function test_cli($path = "") {
    show_header('test_cli');
    echo 'Variable PATH: '.$_SERVER['PATH'].'</br>';
    $comando = 'php '.$path;
    echo 'Comando: '.$comando.'</br>';

    try {
        exec($comando, $resultado, $returnvar);
        if ($returnvar != 0) {
            show_error('Error cli');
            show_object($resultado);
        } else {
            show_success('Ok');
            show_object($resultado);
        }
    } catch (Exception $e) {
        show_error('Error cli '. $e->getMessage());
        return;
    }

    if ($resultado == null) {
        show_error('Error executant CLI');
    }
}

function test_proxy($url) {
    show_header('test_proxy with curl');
    $proxy = sp_get_proxy();
    if (!$proxy) {
        return;
    }
    try {
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HEADER, 1);
        curl_setopt($process, CURLOPT_NOBODY , 1);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_PROXY, $proxy);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($process);
        curl_close($process);
        show_object($return);
        show_success('Ok');
    } catch (Exception $e) {
        show_error('Error proxy '. $e->getMessage());
        return;
    }
}

function test_server($correct_timezone='Europe/Madrid') {
    show_header('test_server');
    show_object($_SERVER);

    // Check timezone and date
    $timezone = date_default_timezone_get();
    if ($timezone === $correct_timezone) {
        show_success('Server timezone is OK: '.$timezone);
    } else {
        show_error('Server timezone is KO: '.$timezone);
    }
    $date = date('d/m/Y H:i:s');
    show_object('<br/>The current server date is: '. $date);

    phpinfo();
}

function telnet($server, $port) {
    $telnet = 'telnet '.$server.' '.$port;
    show_header($telnet);
    try {
        echo exec($telnet).'<br/>';
    } catch (Exception $e) {
        show_error('Error telnet'. $e->getMessage());
    }
}

function test_ftp($server, $user = false, $pass = false, $dir = false, $port = 21) {
    show_header('test_ftp server '.$server.' port '.$port);

    try {
        $connid = ftp_connect($server, $port, 5);
        if (!$connid) {
            show_error("Couldn't ftp_connect to $server:$port");
            telnet($server, $port);
            return;
        }
        show_success('OK, connected');

        if ($user && $pass) {
            $loginresult = ftp_login($connid, $user, $pass);
            if (!$loginresult) {
                show_error('Cannot login');
                return;
            }
            show_success('OK, logged in');
            if ($dir) {
                if (!ftp_chdir($connid, $dir)) {
                    show_error("Chdir $dir error");
                } else {
                    show_success('OK, Directori '.$dir);
                }
                $pwd = ftp_pwd($connid);
                echo 'Fitxers disponibles: a '.$pwd;
                show_object(ftp_nlist($connid, $pwd));
            }
        }

        ftp_close($connid);
    } catch (Exception $e) {
        show_error('Error ftp_connect'. $e->getMessage());
    }
}