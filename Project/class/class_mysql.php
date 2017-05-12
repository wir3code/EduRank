<?php

/*
 * Spencer Brydges 
 * Shefali Chohan 
 * class_mysql.php
 * Driver for database communication and database error handling
 * Wrapper class allows for all input to be sanitized without the caller having to worry about
 * passing input through mysql_real_escape_string. This effectively bulletproofs the system against SQL injection attacks
 * Errors are set through the master error class. Classes using this object must call object's get_error() in order to
 * output any database errors that have occurred from malformed queries.
 * Helper methods exist for dynamically constructing queries so that the caller need to be concerned with the database structure
 * ADD: Helper method to automatically split data separated by commas
*/

if(!defined('IN_EDU'))
{
	die('');
}
	include_once 'class_files.php';

	class DatabaseDriver
	{
		var $conn; //Link to database
		var $query; //Holds current query string
		var $rows; //Holds rows returned from select statement
		var $error; 
		var $errorsrv;
		var $isErrror; //Set to true if a non-database error has occurred
		
		/**
		 * Constructor. A master error object MUST be provided, as this class will be the most error-prone.
		*/
		
		function __construct($error)
		{
			$this->database_connect();
			$this->errorsrv = $error;
			$this->isError = false;
		}
		
		function __destruct()
		{
			$this->database_close();
		}
		
		function get_error()
		{
			return $this->errordriver->return_error();
		}
		
		/*
		 *Are we dealing with a database error, or input error?
		*/
		
		function is_error()
		{
			return $this->isError;
		}
		
		/**
		 * Connect to the server AND database using the variables in config.php
		*/
		
		function database_connect()
		{
			global $db_server;
			global $db_db;
			global $db_user;
			global $db_pass;
			@$this->conn = mysql_connect($db_server, $db_user, $db_pass);
			mysql_select_db($db_db);
		}
		
		/**
		 * Special wrapper functions for performing SELECT queries.
		 * Data is automatically sanitized from the WHERE array, thus eliminating the need
		 * for sanity check elsewhere :)
		*/
		
		function database_select($table, $what = "*", $where = array(), $limit = 0, $sort = array())
		{
			global $debugging_mode;
			$this->error = null; //Reset any previous errors
			$this->query = "SELECT "; //Begin query build
			$index = 0;
			if(is_array($what)) //More than one column being selected
			{
				for($i = 0; $i < count($what); $i++)
				{
					if($index != 0) //There is another column after this one, separate with comma
					{
						$this->query .= ',';
					}
					$this->query .= $what[$index]; 
					$index++;
				}
			}
			else //Only one column is being selected
			{
				$this->query .= "$what";
			}
			
			$this->query .= " FROM $table "; //Define table to select from
			
			if(!empty($where)) //Query contains where constraints
			{
				$this->query .= " WHERE ";
				$index = 0;
				foreach($where as $key=>$value)
				{
					if($index > 0)
					{
						$this->query .= " AND ";
					}
					
					//Security checkpoint: Automatically perform sanitization on all input
					$this->query .= "$key='".mysql_real_escape_string($value)."'";
					$index++;
				}		
			}
			
			if(!empty($sort))
			{
				$this->query .= " ORDER BY " . $sort['value'] . " " . $sort['option'];
			}
			
			if($limit != 0) //A limit was supplied, add to end of query string
			{
				$this->query .= " LIMIT ".intval($limit);
			}
			
			if($debugging_mode)
			{
				echo $this->query . "<br /><br />";
			}
						
			$try_line = __LINE__;
			$try_file = __FILE__;
			$query_result = mysql_query($this->query);
			
			if($query_result === false)
			{
				$this->isError = true; //Set error so that caller can distinguish between DB error vs empty return
				$this->errorsrv->set_error(mysql_error(), 'DATABASE', $this->query, array($try_file, $try_line));
				return false;
			}
			
			if(mysql_num_rows($query_result) < 1) //Nothing was returned, do not set error
			{
				return 0;
			}
			
			if($limit == 1) //Do not need to use 2d array as only one row is returned
			{
				$this->rows = mysql_fetch_assoc($query_result);
			}
			else 
			{
				$this->rows = array();
				while($row = mysql_fetch_assoc($query_result))
				{
					$this->rows[] = $row;
				}
			}	
			
			return $this->rows;
		}
		
		/**
		 * Special wrapper functions for performing INSERT queries.
		 * Data is automatically sanitized from the DATA array, thus eliminating the need
		 * for sanity check elsewhere :)
		*/
		
		function database_insert($table, $data)
		{
			global $debugging_mode;
			$this->query = "INSERT INTO $table (";
			
			$keys = array_keys($data);
			$values = array_values($data);
			
			for($i = 0; $i < count($keys); $i++)
			{
				if($i != 0)
				{
					$this->query .= ',';
				}
				$this->query .= "`$keys[$i]`";
			}
			
			$this->query .= ") VALUES (";
			
			for($i = 0; $i < count($values); $i++)
			{
				if($i != 0)
				{
					$this->query .= ',';
				}
				//Security checkpoint: automatically sanitize user input data
				$value = mysql_real_escape_string($values[$i]);
				$this->query .= "'$value'";
			}
			
			$this->query .= ')';
			
			if($debugging_mode)
			{
				echo $this->query;
			}
			
			$try_line = __LINE__;
			$try_file = __FILE__;
			$res = mysql_query($this->query);
			
			if($res === false)
			{
				$this->isError = true; //Set error so that caller can distinguish between DB error vs empty return
				$this->errorsrv->set_error(mysql_error(), 'DATABASE', $this->query, array($try_file, $try_line));
				return false;
			}
			
			return $res;
		}
		
		/**
		 * Special wrapper function for performing UPDATE queries.
		 * Data is automatically sanitized from the WHERE and SET_VALUES array, thus eliminating the need
		 * for sanity check elsewhere :)
		*/
		
		function database_update($table, $set_values, $where)
		{
			global $debugging_mode;
			$this->query = "UPDATE $table SET ";
			$index = 0;
			
			foreach($set_values as $key => $value)
			{
				if($index != 0)
				{
					$this->query .= ",";
				}
				//Security checkpoint: automatically sanitize data
				$value = mysql_real_escape_string($value);
				$this->query .= "$key = '$value'";
				$index++;
			}
			
			$this->query .= " WHERE ";
			
			$index = 0;
						
			foreach($where as $key => $value)
			{
				if($index != 0)
				{
					$this->query .= ' AND ';
				}
				//Security checkpoint: automatically sanitize data
				$value = mysql_real_escape_string($value);
				$this->query .= "$key = '$value'";
				$index++;
			}
			
			if($debugging_mode)
			{
				echo $this->query;
			}
			
			$try_line = __LINE__;
			$try_file = __FILE__;
			
			$res = mysql_query($this->query);
			
			if($res === false)
			{
				$this->isError = true; //Set error so that caller can distinguish between DB error vs empty return
				$this->errorsrv->set_error(mysql_error(), 'DATABASE', $this->query, array($try_file, $try_line));
				return false;
			}
			
			return (mysql_affected_rows() > 0) ? true : 0;
		}
		
		/**
		 * Special wrapper function for performing DELETE queries.
		 * Data is automatically sanitized from the WHAT array, thus eliminating the need
		 * for sanity check elsewhere :)
		*/
		
		function database_delete($table, $what, $limit = 1)
		{
			global $debugging_mode;
			$this->query = "DELETE FROM $table WHERE ";
			$index = 0;
			
			foreach($what as $key => $value)
			{
				if($index != 0)
				{
					$this->query .= " AND ";
				}
				//Security checkpoint: automatically sanitize data
				$value = mysql_real_escape_string($value);
				$this->query .= "$key = '$value'";
				$index++;
			}
			
			$this->query .= " LIMIT $limit";
			
			if($debugging_mode)
			{
				echo $this->query;
			}
			
			$try_line = __LINE__;
			$try_file = __FILE__;
			$res = mysql_query($this->query);
			
			if($res === false)
			{
				$this->isError = true; //Set error so that caller can distinguish between DB error vs empty return
				$this->errorsrv->set_error(mysql_error(), 'DATABASE', $this->query, array($try_file, $try_line));
				return false;
			}
			
			return (mysql_affected_rows() > 0) ? true : false;
		}
		
		/**
		 * Count number of rows
		*/
		
		function database_count($table, $where = null)
		{
			global $debugging_mode;
			$this->query = "SELECT COUNT(*) as total FROM $table";
			if($where != null)
			{
				$this->query .= " WHERE ";
				$index = 0;
				foreach($where as $key => $value)
				{
					if($index != 0)
					{
						$this->query .= ' AND ';
					}
					$value = mysql_real_escape_string($value);
					$this->query .= "$key = '" . $value . "'";
					$index++;
				}
			}
			
			if($debugging_mode)
			{
				echo $this->query;
			}
			
			$result = mysql_query($this->query);
			if($result == false)
			{
				return 0;
			}
			$result = mysql_fetch_assoc($result);
		
			return $result['total'];
		}
		
		/**
		 * Special wrapper function that reads from the database.schema file in order to
		 * automatically build queries depending on the context in which it is called
		*/
		
		function database_build_query($context, $type = 'SELECT')
		{
			global $db_structure;
			$fh = new FileDriver();
			if(($fh->openfile($db_structure, 'r')) == null)
			{
				$fh->print_error_and_exit();
			}
			$builder = array();
			$index = 0;
			$contents = $fh->readfile();
			
			if($contents === false)
			{
				$fh->print_error_and_exit();
			}
			
			$split = explode("\n", $contents);
			
			for($i = 0; $i < count($split); $i++)
			{
				if(substr($split[$i], 0, strlen($context)) == $context)
				{
					$pos_s = strpos(trim($split[$i]), "{", 0);
					$pos_e = strrpos(trim($split[$i]), "}", 0);
					
					$pos_s++;
					
					$contents = substr($split[$i], $pos_s, ($pos_e-$pos_s));
					
					if($type == 'SELECT')
					{
						$builder = explode(',', $contents);
					}
					else 
					{
						$tmp = explode(',', $contents);
						for($j = 0; $j < count($tmp); $j++)
						{
							$key = trim($tmp[$j]);
							$builder[$key] = '';
						}	
					}
					break;
				}
			}
			
			$fh->closefile();
			return $builder;
		}
		
		/**
		 * Special method for executing queries not otherwise handled by
		 * the defined wrapper methods.
		 * It is very important that the caller class sanitizes and handles details itself
		 * ( and to generally avoid using this method whenever user input is involved )
		 * To date, one callers use(s) this method
		*/
		
		function special_query($query)
		{
			$this->query = $query;
			$resource = mysql_query($this->query);
			return $resource;
		}
		
		/**
		 *Close the DB connection
		*/
		
		function database_close()
		{
			mysql_close($this->conn); 
		}
	}
	
?>
