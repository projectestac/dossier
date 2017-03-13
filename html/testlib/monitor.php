<?php
require_once('monitor.lib.php');

/***
 * Needed variables
 */
$scriptname = __FILE__;
$interval = 300; //test interval in seconds
$file = 'ocimdb.tmp'; //file to save result
$temp_dir = '/tmp';
$current_date = date('YmdHis');
$file2open = $temp_dir.'/'.$file;
$file2openLK = $file2open.'.lock';
// Only for Agora
$phpbin = '/opt/rh/php54/root/usr/bin/php';


$special = '';
switch (APP) {
	case 'IOC':
		$config = __DIR__ . '/../config-moodle2.php';
		$isagora = FALSE;
		break;

	case 'IOC-secre':
		$config = 'ioc/lib/config.php';
		break;

	case 'agora':
		$config = __DIR__.'/../config/env-config.php';
		$config2 = __DIR__.'/../config/dblib-mysql.php';
		$isagora = TRUE;
		break;

	case 'odissea':
		$config = 'config.php';
		$isagora = FALSE;
		break;

	case 'blocs':
		$config = 'wp-config.php';
		define('SHORTINIT', true);
		$special = 'blocs';
		break;

	default:
		$config = __DIR__.'/../config/env-config.php';
		$config2 = __DIR__.'/../config/dblib-mysql.php';
		break;
}

require_once($config);
if (isset($config2)) {
	require_once($config2);
}

//Special config requirements:
if ($special == 'blocs') {
	unset($CFG);
	global $CFG;
	$CFG = new stdClass();
	$CFG->dbhost = DB_HOST;
	$CFG->dbuser = DB_USER;
	$CFG->dbpass = DB_PASSWORD;
	$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
	switch (ENVIRONMENT) {
		case 'DES':
			$_SERVER['HTTP_HOST'] = 'agora';
			break;
		case 'INT':
			$_SERVER['HTTP_HOST'] = 'integracio.blocs.xtec.cat';
			break;
		case 'ACC':
			$_SERVER['HTTP_HOST'] = 'preproduccio.blocs.xtec.cat';
			break;
		case 'PRO':
		default:
			$_SERVER['HTTP_HOST'] = 'blocs.xtec.cat';
			break;
	}
} elseif ($isagora) {
	global $CFG;
	$CFG = new stdClass();
	$CFG->dbhost = $agora['admin']['host'];
	$CFG->dbuser = $agora['nodes']['username'];
	$CFG->dbpass = $agora['nodes']['userpwd'];
}


//CLI execution for background process:
if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == '-c') {
       global $agora;
       $result = moodle_session_DB($agora, 'moodle2', 'm2','oracle',$file2open);
	   echo $result;
	   exit;
}

//validate user
$request = $_POST;
$token = empty($request['token'])?'':$request['token'];
if (md5($token)!= '38a1c2f3f1fe1155cee4d6cf93139a49') {
	 //for testing: $request = $_GET; if ($request['token']!='cacti'){
	echo 'OK';
	exit;
}
//validate host
$host = empty($request['host'])?'localhost':$request['host'];
if ($isagora) {
	$CFG->dbhost = $host=='localhost' ? $agora['admin']['host']:$host;
}
//monitor selector
switch ($request['type']) {
	case 'memcache':
		$result = dumpMemcache($host);
		break;
	case 'mymdb':
		$dsn = 'mysql:host='.$CFG->dbhost.';dbname:'.$CFG->dbname;
		try {
			$DB = new PDO ($dsn,$CFG->dbuser,$CFG->dbpass);
			$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Connection failed: '. $e->getMessage();
			exit;
		}
		$result = moodle_session_DB($DB, $CFG->dbname, $CFG->prefix);
		break;

	case 'apache':
		$result = ss_apache_stats($host);
		break;

	case 'mysql':
		$result = mysql_php_monitor();
		break;

	case 'msf':
		//Count php session files
		$result = moodle_session_files(FALSE, $isagora);
		break;

	case 'multi_msf':
		//count php session files from multiple servers
		$result = moodle_session_files(TRUE, $isagora);
		break;

	case 'ocimdb':
		global $agora;
		//Tests last execution
		$data = last_exec($file2open, $file2openLK, $current_date, $interval);
		if (!$data) {
			$result = moodle_session_DB($agora, 'moodle2', 'm2','oracle',$file2open);

		} else {
			$result = $data;
		}
		break;

	default:

		break;
}

//Show result
echo $result;