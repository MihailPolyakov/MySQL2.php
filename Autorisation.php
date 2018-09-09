<?php
define('USER_LOGIN', 'admin');
define('USER_PASS', 'pass');
if (empty($_SERVER['PHP_AUTO_USER'])&&empty($_SERVER['PHP_AUTO_PW'])) {
	header('WWW-Authenticate: Basic realm="admin"');
	die;
}
