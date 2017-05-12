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

$log_security = './logs/security.txt';
$log_database = './logs/database.txt';
$log_admin = './logs/admin.txt';
$banned_users = './logs/banned.txt';

$db_structure = './logs/structure.schema';

$file_upload_path = '';

$security_pattern = "/\-[0-9]|union|http:\/\/|'|\/etc\/passwd|\/etc|<script>|\[\]/";

$record_errors_basic = true;

$debugging_mode = false;

$logging_mode = true;

$max_login_attempts = 10;
$login_wait_penalty = 15;

?>
