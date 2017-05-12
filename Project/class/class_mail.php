<?php

/**
* Spencer Brydges 
* Shefali Chohan 
* class_mail.php
* Mailbox class provides functionality required for user mailbox system
* Methods are provided in order to send messages, read messages, and delete messages
* Creation of the composition window and listing/sorting of messages is handled by the caller class
*/

class MailBox
{
	/**
	mailID: Contains value of specific mail message ID to be retrieved from database and displayed
	DB: DatabaseDriver object for communicating with the database
	*/

	var $mailboxID;
	var $DB;
	var $sendUser;
	var $sendSubject;
	var $sendBody;
	var $sendFrom;
	
	/**
	 *Initialize all member variables in constructor. Ref paramater is to be used
	 *whenever an object already containing a DatabaseDriver object must initialize and use this class
	 *This ensures that the database connection is properly closed
	*/

	function __construct($ref = null)
	{
		if($ref == null)
		{
			$this->DB = new DatabaseDriver();
		}
		else
		{
			$this->DB = $ref;
		}
		
		$this->sendFrom = (session_get('username')) ? $this->get_mailbox_id() : false;
		$this->sendSubject = null;
		$this->sendBody = null;
	}
	
	function __destruct()
	{
	}
	
	/*
	 * Method sets the "to" in a sent email to the given parameter
	*/

	function set_sendto($to)
	{
		$check = $this->DB->database_select('users', 'username', array('username' => $to), 1);
		
		if($check != FALSE)
		{
			$mailbox = $this->DB->database_select('mailbox', 'mid', array('owner' => $to), 1);
			$this->sendUser = $mailbox['mid'];
			return true;
		}
		
		return false;
	}
	
	/*
	 * Method sets the "from" in a sent email to the given parameter
	*/
	
	function set_sendfrom($user)
	{
		$send_id = $this->get_mailbox_id($user);
		$this->sendFrom = $send_id;
	}

	function set_subject($subject)
	{
		$this->sendSubject = htmlentities($subject);
	}

	function set_body($body)
	{
		$this->sendBody = htmlentities($body);
	}
	
	function update_read()
	{
		
	}

	function get_mail($mailID = null)
	{
		global $debugging_mode;
		$this->get_mailbox_id();
		if($mailID == null) //The user has not specified a specific message to be read, so display all messages accordingly
		{
			$mail = $this->DB->database_select('mail', array('messageID', 'subject', 'body',
									 'mail_from', 'mail_date', 'wasread'), 
			array('mid' => $this->mailboxID), 0, array('option' => 'desc', 'value' => 'messageID'));
			
			if($mail === FALSE)
			{
				if($this->DB->is_error())
				{
					if($debugging_mode)
					{
						$errors = $this->DB->get_error();
						for($i = 0; $i < count($errors); $i++)
						{
							echo $errors[$i];
						}
					}
				}
				return false;
			}
			return $mail;
		}
		else
		{
			$message = $this->DB->database_select('mail', array('subject', 'body', 'mail_from', 'mail_date'),
			array('mid' => $this->mailboxID, 'messageID' => $mailID), 1);
			return $message;
		}
	}
	
	/**
	 * Fetches the mailbox ID associated with a given user
	 * special_id is used when a system mail message is sent to a user, i.e., automated messages or announcements
	*/

	function get_mailbox_id($special_id = null)
	{
		//If special_id is set, then the mailbox associated with special_id will be fetched and returned instead
		$owner = ($special_id == null) ? session_get('username') : $special_id;
		$mid = $this->DB->database_select('mailbox', 'mid', array('owner' => $owner), 1);
			
		if($mid != FALSE)
		{
			$this->mailboxID = $mid['mid'];
			return $this->mailboxID;
		}
		if($this->DB->is_error())
		{
			$errors = $this->DB->get_error();
			for($i = 0; $i < count($errors); $i++)
			{
				echo $errors[$i];
			}
			die('');
		}
		return false;
	}
	
	/*
	 * Get the username associated with the given mailbox ID
	*/
	
	function get_mailbox_username($mid)
	{
		$owner = $this->DB->database_select('mailbox', 'owner', array('mid' => $mid), 1);
		return $owner['owner'];
	}
	
	/*
	 * Get the username associated with a given mailbox BY their user ID
	*/
	
	function get_mailbox_username_byid($id)
	{
		$owner = $this->DB->database_select('users', 'username', array('uid' => $id), 1);
		return $owner['username'];
	}
	
	/*
	 * Method sends an email...no need to comment further
	*/

	function send_mail()
	{

		if($this->sendFrom) //Should also check sendTo.......change and test if time permits
		{
			$ins = array('mid' => $this->sendUser, 'subject' => $this->sendSubject,
				     'body' => $this->sendBody, 'mail_from' => $this->sendFrom,
				     'mail_date' => $this->get_time_stamp());
			$this->DB->database_insert('mail', $ins) or die(mysql_error());
			return true;
		}
		return false;
	}
	
	/*
	 * Method deletes a given mail
	*/

	function delete_mail($messageID)
	{
		if($this->mailboxID == null) //Ensure that the message delete request belongs to the user's mailbox
		{
			$this->get_mailbox_id();
		}
		
		if($this->DB->database_delete('mail', array('messageID' => $messageID, 'mid' => $this->mailboxID), 1))
		{
			return true;
		}
		return false;
	}
	
	/*
	 * Mark a mail as read
	*/
	
	function mark_read($messageID, $read = true)
	{
		$value = ($read) ? 1 : 0; //Determine if we are marking as read or unread
		
		if($this->mailboxID == null) //Ensure that the message read/unread request belongs to the user's mailbox
		{
			$this->get_mailbox_id();
		}
		
		$this->DB->database_update('mail', array('wasread' => $value),
					   array('messageID' => $messageID, 'mid' => $this->mailboxID));
	}
	
	/*
	 * Retrieved mail sent by *this* user
	*/
	
	function get_sent_mail()
	{
		$id = $this->get_mailbox_id();
		$mail_sent = $this->DB->database_select('mail', '*', array('mail_from' => $id), 0, array('option' => 'desc', 'value' => 'messageID'));
		return $mail_sent;
	}
	
	/*
	 *Remove in future unless unique timestamp is made. get_timestamp() now supplied by functions.php
	*/
	
	function get_time_stamp()
	{
		return date("Y-m-d h:i:s ");
	}
	
	/*
	 * Get total unread emails sent to *this* user
	*/
	
	function get_mail_count($mailID = null)
	{
		$this->get_mailbox_id();
		return $this->DB->database_count('mail', array('mid' => $this->mailboxID, 'wasread' => 0));
	}

}

?>
