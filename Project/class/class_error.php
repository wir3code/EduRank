<?php

/*
 * Spencer Brydges 
 * Shefali Chohan 
 * class_error.php
 * This class acts as a global error object -- other classes may use it for error handling and reporting
 * Class was unfortunately not used too often, only serving a good purpose in class_user.php and class_logs/files
 * It is however versatile enough to extend to other classes (if time permits)
 * Not commented due to inherent simplicity and tiny size.
*/

include_once 'config.php';
include_once 'class_logs.php';

class ErrorDriver
{
	var $errormessage;
	var $errortype;
	var $recorderror;
	var $size;
	var $log;
	
	function __construct()
	{
		global $record_errors_basic;
		$this->errormessage = array();
		$this->errortype = null;
		$this->size = 0;
		$this->recorderror = $record_errors_basic;
		$this->log = new LogDriver();
	}
	
	function set_error($msg, $TYPE = null, $object = null, $additional_info = array())
	{
		global $debugging_mode;
		global $logging_mode;
		
		if($TYPE == 'DATABASE')
		{
			if($debugging_mode)
			{
				$this->errormessage[$this->size] = $msg . " (Query executed: $object)";
			}
			else 
			{
				$this->errormessage[$this->size] = "A database error has occurred";
			}
			
			if($logging_mode)
			{
				$this->log->record_database_error($msg, $object, $additional_info);
			}
		}
		else if($TYPE == 'FILE')
		{
			switch($msg)
			{
			case 'FILE_EXISTS':
				$this->errormessage[$this->size] = "Failed to open file $object (Reason: file does not exist)";
				break;
			case 'FILE_UNREADABLE':
				$this->errormessage[$this->size] = "Failed to open file $object (Reason: file is not readable)";
				break;
			case 'FILE_UNWRITABLE':
				$this->errormessage[$this->size] = "Failed to write to file $object (Reason: file is not writable)";
				break;
			case 'DIRECTORY_EXISTS':
				$this->errormessage[$this->size] = "Failed to open $directory (Reason: directory does not exist)";
				break;
			case 'DIRECTORY_UNWRITABLE':
				$this->errormessage[$this->size] = "Failed to write file to $directory (Reason: directory is not writable)";
				break;
			case 'DIRECTORY_UNREADABLE':
				$this->errormessage[$this->size] = "Failed to read from $directory (Reason: directory is not readable)";
				break;
			default:
				$this->errormessage[$this->size] = "A file I/O error has occurred, exiting...";
			}
		}
		else 
		{
			$this->errormessage[$this->size] = $msg;
		}
		$this->size++;
	}
	
	function get_num_errors()
	{
		return $this->size;
	}
	
	function return_error()
	{
		return $this->errormessage;
	}
}

?>
