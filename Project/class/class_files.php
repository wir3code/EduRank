<?php

/*
 * Spencer Brydges
 * Shefali Chohan 
 * class_files.php
 * This class is used for encapsulating file I/O operations, which mainly occur for logging and log processing purposes.
*/

include_once 'class_logs.php';

class FileDriver
{
	var $bufferedreader;
	var $bufferedwriter;
	var $openfile;
	var $filename;
	var $errormsg;
	
	function __construct()
	{
	}
	
	/**
	 *Opens a given file, returning null if the file:
	 *A) Does not exist
	 *B) Not readable
	 *C) Cannot be opened for whatever reason
	 *It is not prudent to report back any errors, as they cannot be written out in the logs anyways...
	 */
	
	function openfile($file, $mode)
	{
		if(!file_exists($file))
		{
			return null;
		}
		if(!is_readable($file))
		{
			return null;
		}
		
		$this->openfile = fopen($file, $mode);
		if($this->openfile == null)
		{
			return null;
		}
		$this->filename = $file;
		return true;
	}
	
	/**
	 *Reads a given file. Constants allow for reading of only certain sections of the file
	*/
	
	function readfile($beginning = 0, $end = 'FILE_END')
	{
		$true_end = intval($end);
		$true_beginning = intval($beginning);
		
		switch($beginning)
		{
			case 'FILE_END':
				$true_beginning = 0;
				break;
			case 'FILE_MIDDLE':
				$true_beginning = (file_size($this->filename) / 2);
				break;
			case 'FILE_THIRD':
				$true_beginning = (file_size($this->filename) * (3/4));
				break;
			case 'FILE_FIRST':
				$true_beginning = (file_size($this->filename) * (1/4));
				break;
		}
		
		fseek($this->openfile, $true_beginning);
		
		switch($end)
		{
			case 'FILE_END':
				$true_end = filesize($this->filename);
				break;
			case 'FILE_MIDDLE':
				$true_end = (file_size($this->filename) / 2);
				break;
			case 'FILE_THIRD':
				$true_end = (file_size($this->filename) * (3/4));
				break;
			case 'FILE_FIRST':
				$true_end = (file_size($this->filename) * (1/4));
				break;
		}
		
		$contents = fread($this->openfile, filesize($this->filename));
		
		return $contents;
	}
	
	/**
	 *Writesa given file, returning null if not writable
	*/
	
	function writefile($contents)
	{
		if(!is_writable($this->filename))
		{
			return;
		}
		fwrite($this->openfile, $contents, strlen($contents));
	}
	
	/**
	 *Close the currently open file
	*/
	
	function closefile()
	{
		fclose($this->openfile);
	}
	
	/**
	 * Get error message via master error object
	*/
	
	function get_error()
	{
		return $this->errormsg;
	}
	
	/**
	 * Display errors and exit -- never used.
	*/
	
	function print_error_and_exit()
	{
		for($i = 0; $i < count($this->errormsg); $i++)
		{
			echo $this->errormsg[$i] . "<br />";
		}
		die("");
	}
	
	/**
	 *Create a given file
	*/
	
	function create_file($file, $directory = null)
	{
		if($directory == null)
		{
			$directory = realpath(getcwd());
		}
		if(!file_exists($directory))
		{
			return;
		}
		if(!is_writable($directory))
		{
			return;
		}
		
		$this->openfile = fopen($directory . '/' . $file, 'w');
		fwrite($this->openfile, '', 0);
	}
	
	/**
	 *This is not used. Ignore.
	*/
	
	function list_directory($directory = null)
	{
		if($directory == null)
		{
			$directory = realpath(getcwd());
		}
		if(!file_exists($directory))
		{
			return;
		}
		if(!is_readable($directory))
		{
			return;
		}
	}
}

?>
