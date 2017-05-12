<?php

/*
 * Spencer Brydges 
 * Shefali Chohan 
*/

$db_server = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_db = 'edu';

$site_active = false;
$site_theme = 1;

$log_security = 'C:\WWW\www\logs\security.txt';
$log_database = 'C:\WWW\www\logs\database.txt';
$log_admin = 'C:\WWW\www\logs\admin.txt';
$banned_users = 'C:\WWW\www\logs\banned.txt';

$db_structure = 'C:\WWW\www\logs\structure.schema';

$file_upload_path = './images/User/';

$security_pattern = "/\-[0-9]|union|http:\/\/|'|\/etc\/passwd|\/etc|<script>|\[\]/";

$record_errors_basic = false;

$debugging_mode = false;

$logging_mode = true;

$max_login_attempts = 10;
$login_wait_penalty = 15;

?>
