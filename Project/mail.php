<?php

/**
* Spencer Brydges 
* Shefali Chohan 
* mail.php
* Somewhat tiny file that loads mail data and passes it off the front-end handler (class_content.php)
*/

if(!defined('IN_EDU'))
{
	die('');
}

if(!session_get('username'))
{
	die("You must be a registered user in order to view this page. EIther register or login");
}

$mail = new MailBox($db);

if(isset($_GET['msg']))
{
	if($_GET['msg'] == 'delete')
	{
		echo "Message deleted!<br />";
	}
	if($_GET['msg'] == 'failed')
	{
		echo "Failed to delete message<br />";
	}
}

if(isset($_POST['doSend']))
{
	$sent_successfully = true;
	$mail->set_body($_POST['body']);
	$mail->set_subject($_POST['subject']);
	if(!$mail->set_sendto($_POST['sendto']))
	{
		echo "Cannot send mail (Reason: Supplied user does not exist)<br />";
		$sent_successfully = false;
	}
	if(!$mail->send_mail())
	{
		$sent_successfully = false;
	}
	echo ($sent_successfully) ? "Sent mail!<br />" : "Failed to send mail<br />";
}

if(isset($_POST['doDelete']))
{
	if($mail->delete_mail($_POST['mid']))
	{
		header('Location: home.php?page=mail&msg=delete');
	}
	else
	{
		header('Location: home.php?page=mail&msg=failed');
	}
}

if(isset($_POST['doCompose']) || isset($_GET['compose']))
{
	if(!isset($_POST['doSend']))
	{
		$sendTo = (isset($_GET['compose'])) ? $mail->get_mailbox_username_byid($_GET['compose']) : '';
		$content->_mail_compose($sendTo);
	}
}
else
{
	if(!isset($_GET['id'])) //The user has not requested a specific message, so display all messages instead
	{
		
		//The user requested a multi delete/mark (un)read. process request
		
		if(isset($_POST['multiDelete']) || isset($_POST['multiRead']) || isset($_POST['multiUnread']))
		{
			$actionArray = $_POST;
			array_pop($actionArray); //We don't need the last post value, only the post values containing the ids
			
			if(count($actionArray) > 0)
			{
				foreach($actionArray as $key => $value)
				{
					if($value == 'action') //We want to perform the requested action on this message
					{
						//This is a problem...message gets acted upon AFTER messages are displayed
						if(isset($_POST['multiDelete']))
						{
							$mail->delete_mail($key);
						}
						else if(isset($_POST['multiRead']))
						{
							$mail->mark_read($key);
						}
						else
						{
							$mail->mark_read($key, false);
						}
					}
				}
			}
		}
		
		if(isset($_GET['view']))
		{
			if($_GET['view'] == 'sent')
			{
				$messages = $mail->get_sent_mail();
				if(count($messages) > 0)
				{
					for($i = 0; $i < count($messages); $i++)
					{
						$user = $mail->get_mailbox_username($messages[$i]['mail_from']);
						$user = $db->database_select('users', 'profile_image', array('username' => $user), 1);
						$picture = ($user['profile_image'] == '')
						? $content->_image_profile_default()
						: $user['profile_image'];
						$messages[$i]['profile_image'] = $picture;
					}
				}
				$content->_mail_viewmail($messages, $mail, false);
			}
		}
		else
		{
			$messages = $mail->get_mail(); //Fetch messages
			if(count($messages) > 0 && $messages != 0)
			{
				for($i = 0; $i < count($messages); $i++)
				{
					$user = $mail->get_mailbox_username($messages[$i]['mail_from']);
					$user = $db->database_select('users', 'profile_image', array('username' => $user), 1);
					$picture = ($user['profile_image'] == '')
					? $content->_image_profile_default()
					: $user['profile_image'];
					$messages[$i]['profile_image'] = $picture;
				}
			}
			$content->_mail_viewmail($messages, $mail);
		}
	}
	elseif(isset($_GET['id']))
	{
		$messages = $mail->get_mail($_GET['id']);
		$data['subject'] = htmlentities($messages['subject']);
		$data['body'] = htmlentities($messages['body']);
		$data['user'] = $mail->get_mailbox_username($messages['mail_from']);
		$data['mail_date'] = $messages['mail_date'];
		$user = $mail->get_mailbox_username($messages['mail_from']);
		$user = $db->database_select('users', 'profile_image', array('username' => $user), 1);
		$picture = ($user['profile_image'] == '')
						? $content->_image_profile_default()
						: $user['profile_image'];
		$data['profile_image'] = $picture;
			
		$mail->mark_read($_GET['id']);
			
		$content->_mail_viewmessage($data);
	}
}

?>
