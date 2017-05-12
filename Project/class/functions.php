<?php

/**
 * Spencer Brydges 
 * Shefali Chohan 
 * functions.php
 * Miscellanous functions used throughout the project
 * the most important being input validation in order to detect hacking attempts
*/

include_once 'config.php';


/**
 * Before executing any function, check the user by their IP and username
 * and ensure that they were not banned
*/

function _validate_user()
{
	global $db;
	
	//Check ip-level ban
	
	if(($check = $db->database_select('bans', 'ban_reason', array('ban_ip' => $_SERVER['REMOTE_ADDR']), 1)) != 0)
	{
		die("You have been banned from the site (Reason: ".$check['ban_reason'].")");
	}
	
	//Could be a user-level ban, check if they are logged on and banned
	
	if(session_get('username'))
	{
		if(($check = $db->database_select('bans', 'ban_reason', array('ban_user' => session_get('username')), 1)) != 0)
		{
			die("You have been banned from the site (Reason: ".$check['ban_reason'].")");
		}
	}
}

/**
 * Method to check for hacking attempts
*/

function _validate_input($what = array(), $method, $assoc = false)
{
	global $security_pattern;
	global $db;
	$log = new LogDriver();
	
	/**
	 * Check if the values we want to validate are the name of the indexes in the POST/GET
	 * array we are checking. If they are not, then 'keys' will simply by every index in POST/GET
	 */
	
	$keys = ($assoc) ? array_keys($what) : null;
	
	for($i = 0; $i < count($what); $i++)
	{
		$index = ($keys == null) ? trim($what[$i]) : trim($keys[$i]); //PHP annoyingly adds whitespace to variables...take care of that
		if(isset($method[$index]))
		{
			//Get the current index that we want to check
			if(preg_match($security_pattern, $method[$index])) //An invalid/illegal/suspicious input was detected, set a warn
			{
				$log->record_ip_address();
				$log->record_user_agent();
				$log->record_activity('SECURITY', $_SERVER['PHP_SELF'], $method[$index]);
				$attempt = $db->database_select('activity', 'activity_strikes',
								array('activity_ip' => $_SERVER['REMOTE_ADDR'], 'activity_type' => 'hacking'), 1);
				if($attempt == false)
				{
					$db->database_insert('activity',
								array('activity_ip' => $_SERVER['REMOTE_ADDR'],
									'activity_type' => 'hacking',
									'activity_strikes' => 1));
					$attempt = 1;
				}
				else
				{
					$attempt = $attempt['activity_strikes'];
					$attempt++;
					if($attempt >= 10)
					{
						$db->database_insert('bans', array('ban_ip' => $_SERVER['REMOTE_ADDR'], 'ban_reason' => 'Repeat hacking attempts'));
						$log->record_ban();
						die("You have been banned from the site (Reason: Repeat hacking attempts)");
					}
					$db->database_update('activity',
								array('activity_strikes' => $attempt),
								array('activity_ip' => $_SERVER['REMOTE_ADDR'], 'activity_type' => 'hacking'));
				}
				echo "Hacking attempt detected (supplied string: ".htmlentities($method[$index]).")...input processing terminated. ";
				echo "This is your attempt #".$attempt." at hacking into the site. Further attempts may result in your 
				IP address being blacklisted. If you are requesting legitimate resources and this error keeps occurring,
				please email an admin and we will review your activity accordingly<br />";
				die('');
			}
		}
	}
}

function _validate_input_xss($what)
{
	return false;
}

/**
 * Global timestammping function. Is applied everywhere
*/

function get_timestamp()
{
	return date("Y-m-d h:i:s ");
}

/**
 * Format a user's comment so that there is no more than 40 characters per line
 * Note: the algorithm I made sucks...improve it later...
*/

function format_comment($input)
{
	$split_input = array();

	while(strlen($input) > 40)
	{
		$next_word = strrpos(substr($input, 0, 40), " ");
		if($next_word <= 0 || $next_word > 40)
		{
			$next_word = 40;
		}
		$split_input[] = substr($input, 0, $next_word);
		$input = substr($input, $next_word, strlen($input));
	}
	
	$split_input[] = $input;
	
	$split_input = implode("\n", $split_input);
	
	return $split_input;
}

/**
 * Translate the database timestamp format into a nicer, cleaner format
*/ 

function format_timestamp($timestamp, $format = null)
{
	$formatted_data = '';
	$time = array('hour' => '', 'minute' => '', 'second' =>'');
	$date = array('year' => '', 'month' => '', 'day' => '');
		
	$months = array(
		'01' => 'January',
		'02' => 'February',
		'03' => 'March',
		'04' => 'April',
		'05' => 'May',
		'06' => 'June',
		'07' => 'July',
		'08' => 'August',
		'09' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December'
	);
	
	$days = array(
		'01' => '1st',
		'02' => '2nd',
		'03' => '3rd',
		'04' => '4th',
		'05' => '5th',
		'06' => '6th',
		'07' => '7th',
		'08' => '8th',
		'09' => '9th',
		'10' => '10th',
		'11' => '11th',
		'12' => '12th',
		'13' => '13th',
		'14' => '14th',
		'15' => '15th',
		'16' => '16th',
		'17' => '17th',
		'18' => '18th',
		'19' => '19th',
		'20' => '20th',
		'21' => '21th',
		'22' => '22nd',
		'23' => '23rd',
		'24' => '24th',
		'25' => '25th',
		'26' => '26th',
		'27' => '27th',
		'28' => '28th',
		'29' => '29th',
		'30' => '30th'
	);
	
	if($format == 'comment')
	{
		list($date_array, $time_array) = explode(" ", $timestamp);
		$time_array = explode(':', $time_array);
		$date_array = explode('-', $date_array);
		$time['hour'] = $time_array[0];
		$time['minute'] = $time_array[1];
		$formatted_time = $time['hour'] . ':' . $time['minute'];
		$convertMonth = $date_array[1];
		$convertDate = $date_array[2];
		$formatted_date['day'] = $days[$convertDate];
		$formatted_date['month'] = $months[$convertMonth];
		$formatted_data = $formatted_date['month'] . ' ' . $formatted_date['day'] . ', ' . $date_array[0] . ' ' . $formatted_time;
	}
	
	return $formatted_data;
}

/**
 * Compute difference between two dates
*/

function computeDateDifference($dateA, $dateB, $computeOption)
{
	$differences = array('years' => '', 'months' => '', 'days' => '');
	$diff = abs(strtotime($dateA) - strtotime($dateB));
	$differences['years'] = floor($diff / (365*60*60*24));
	$differences['months'] = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
	$differences['days'] = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	return $differences;
}

/**
 * Compute difference between two times
*/

function computeTimeDifference($timeA, $timeB)
{
	$differences = array('hours' => '', 'minutes' => '', 'seconds' => '');
	$t1 = strtotime($timeA);
	$t2 = strtotime($timeB);
	$differences['seconds'] = round((abs($t1 - $t2))); //Make absolute incase timeB > timeA..avoid negative values
	$differences['minutes'] = (round(($differences['seconds'] / 60), 1));
	$differences['hours'] = (round(($differences['minutes'] / 60), 1));
	return $differences;
}

/**
 * Create session variables
*/

function session_create($context, $value = '')
{
	if($context == 'USER')
	{
		$_SESSION['username'] = $value;
		$_SESSION['loggedin'] = true;
	}
	else
	{
		$_SESSION[$context] = $value;
	}
}

/**
 * Grab session variables
*/

function session_get($context)
{
	return (isset($_SESSION[$context])) ? $_SESSION[$context] : false;
}

/**
 * Remove session variables
*/

function session_terminate($context = '')
{
	if($context == '')
	{
		session_destroy();
	}
	else 
	{
		$_SESSION[$context] = null;
		unset($_SESION[$context]);
	}
}


/**
 * Sanitize and upload user-supplied images
*/

function upload_image()
{
	$allowed_extensions = array('.jpg', 'jpeg', 'gif', '.png');
	$bad_extensions = array('.php', '.php3', '.htmls', '.pl', '.py', '.java', '.exe', '.c', '.cpp', '.cc', '.asp');
	
	$ext = strtolower(strrchr($_FILES['profile_picture']['name'], '.'));
	
	$name = '';
	
	if(!in_array($ext, $allowed_extensions))
	{
		if(in_array($ext, $bad_extensions))
		{
			global $db;
			$log = new LogDriver();
			$log->record_ip_address();
			$log->record_user_agent();
			$log->record_activity('SECURITY', $_SERVER['PHP_SELF'], "FILE UPLOAD: $ext");
			$attempt = $db->database_select('activity', 'activity_strikes',
								array('activity_ip' => $_SERVER['REMOTE_ADDR'], 'activity_type' => 'hacking'), 1);
			if($attempt == false)
			{
				$db->database_insert('activity',
							array('activity_ip' => $_SERVER['REMOTE_ADDR'],
								'activity_type' => 'hacking',
								'activity_strikes' => 1));
				$attempt = 1;
			}
			else
			{
				$attempt = $attempt['activity_strikes'];
				$attempt++;
				if($attempt >= 10)
				{
					$db->database_insert('bans', array('ban_ip' => $_SERVER['REMOTE_ADDR'], 'ban_reason' => 'Repeat hacking attempts'));
					die("You have been banned from the site (Reason: Repeat hacking attempts)");
				}
				$db->database_update('activity',
						array('activity_strikes' => $attempt),
						array('activity_ip' => $_SERVER['REMOTE_ADDR'], 'activity_type' => 'hacking'));
			}
			echo "Hacking attempt detected (supplied string: ".htmlentities($method[$index]).")...input processing terminated. ";
			echo "This is your attempt #".$attempt." at hacking into the site. Further attempts may result in your 
			IP address being blacklisted. If you are requesting legitimate resources and this error keeps occurring,
			please email an admin and we will review your activity accordingly<br />";
			die('');
		}
		echo "Image type not allowed<br />";
		return false;
	}
	
	switch($_FILES['profile_picture']['error'])
	{
		case 0:
			global $file_upload_path;
			$name = substr($_FILES['profile_picture']['name'], 0, strlen($_FILES['profile_picture']['name']) - strlen($ext)) . '_' . time() . '_' . rand(1, 10000) . '_USR' . $ext;
			move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_upload_path . $name);
			break;
	}
	
	return $name;
}

function compute_trail()
{
	$trail_total = null;
	$i = 0;
	foreach($_GET as $key => $value)
	{
		if($value == 'home')
		{
			break;
		}
		if($i != 0)
		{
			$trail_total .= '&';
		}
		$trail_total .= $key . '=' . $value;
		
		if($value == 'course')
		{
			$value = 'Courses';
		}
		if($value == 'profile')
		{
			$value = 'Profile Viewer';
		}
		if($value == 'grades')
		{
			$value = 'Grade Viewer';
		}
		if($value == 'mail')
		{
			$value = 'Mail System';
		}
		if($value == 'news')
		{
			$value = 'News Article';
		}
		if($value == 'ranks')
		{
			$value = 'Ranking Ladder';
		}
		echo " > <a href='?$trail_total'>".$value."</a>";
		$i++;
	}
}

?>
