<?php

/*
 * Spencer Brydges 
 * Shefali Chohan 
 * class_logs.php
 * This class is used for logs as you can imagine. Database errors and hacking attempts are recorded through this class.
 * Not really commented as the code here is relatively self-explanatory and simplistic.
*/

date_default_timezone_set("America/New_York");

include_once 'class_error.php';
include_once 'config.php';
include_once 'functions.php';

class LogDriver
{
	var $time;
	var $message;
	var $location;
	var $suspect_string;
	var $ip_address;
	var $user_agent;
	var $log_file;
	var $filedriver;
	
	function __construct()
	{
		$this->filedriver = new FileDriver();
	}
	
	function record_ip_address()
	{
		$this->ip_address = $_SERVER['REMOTE_ADDR'];
	}
	
	function record_user_agent()
	{
		$this->user_agent = htmlentities($_SERVER['HTTP_USER_AGENT']);
	}

	function record_ban()
	{
		global $log_security;
		$this->log_file = $log_security;
		$this->message = $_SERVER['REMOTE_ADDR'] . " REQUEST BAN\n";
		$this->filedriver->openfile($this->log_file, 'a');
                $this->filedriver->writefile($this->message);
	}
	
	function record_activity($type, $loc, $str)
	{
		$this->time = get_timestamp();
		
		if($type == 'SECURITY')
		{
			global $log_security;
			$this->suspect_string = $str;
			$this->location = $loc;
			$this->message = "$this->time " . "Hacking attempt detected at location: " . 
			$this->location . " (String: " . $this->suspect_string . ") FROM "
			. $this->ip_address . " (AGENT:  " . $this->user_agent . ")\n"; 
			
			$this->ip_address = null;
			$this->user_agent = null;
			$this->log_file = $log_security;
		}
		
		if($type == 'ADMINISTRATIVE')
		{
			global $log_admin;
			$this->message = "$this->time " . "Action performed by $loc: " . $str . "\n";
			$this->log_file = $log_admin;
		}
		
		
		$this->message = $this->message;
		
		$this->filedriver->openfile($this->log_file, 'a');
		$this->filedriver->writefile($this->message);
	}
	
	function record_database_error($msg, $object, $file = array())
	{
		global $log_database;
		global $debugging_mode;
		$time = get_timestamp();
		
		if($debugging_mode)
		{
			echo $msg . ":" . $object;
		}
		
		$file = (!empty($file)) ? " FILE: $file[0], LINE: $file[1]" : "";
		
		$message = "$time $msg (QUERY: $object)$file\n";
		
		//We're really in trouble if this can not occur -- how do we log file errors???
		
		if($this->filedriver->openfile($log_database, 'a')) 
		{
			$this->filedriver->writefile($message);
		}
		
		$this->filedriver->closefile();
	}
	
}

?>
