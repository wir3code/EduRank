<?php

/*
 * Spencer Brydges 
 * Shefali Chohan 
 * class_user.php
 * This class is used for encapsulating the many details pertaining to users and their allowed tasks -- registering,
 * logging in/out, updating profile, adding points and courses, promoting to new group, etc...
*/

if(!defined('IN_EDU'))
{
	die('');
}

include "class_mysql.php"; //Need to heavily communicate with database

class UserDriver
{
	var $DB;
	var $errorsrv; //Object for error handler
	var $doPromote; //Determines if a user has accumulated enough points to increase rank group
	
	/**
	 *Constructor. If references aren't provided, then new database and error objects must be made
	 */
	
	function __construct($ref = null, $error = null)
	{
		$this->DB = ($ref == null) ? new DatabaseDriver() : $ref;
		$this->errorsrv = ($error == null) ? new ErrorDriver() : $error;
	}
	
	/**
	 * Set doPromote as so that a user's group rank is to be increased
	 */
	
	function switchPromote()
	{
		$this->doPromote = true;
	}
	
	/**
	 * Determine if a user recently made a new rank
	 */
	
	function getPromote()
	{
		return $this->doPromote;
	}
	
	/**
	 * Determine the cause of a user error via central error object
	 */
	
	function error()
	{
		return $this->errorsrv->return_error();
	}
	
	/**
	 * Determine if a user exists
	 */
	
	function userExists($uid)
	{
		$check = $this->DB->database_select('users', 'uid', array('uid' => $uid));
		return ($check == 0) ? false : true;
	}
	
	/**
	 * Login a user
	 */
	
	function user_login($username, $password)
	{
		global $max_login_attempts; //From config.php -- max number of login attempts before user with given IP is locked out
		global $login_wait_penalty; //How long a locked out user must wait before re-attempting login
		_validate_input(array('username', 'password'), $_POST); //Sanity check
		
		if($this->getLoginStrikes() >= $max_login_attempts) //User attempted too many incorrect logins -- lock them out
		{
			//Check first to see that enough time has passed -- if it has been 15 minutes, user may attempt to login again
			$lastTime = $this->DB->database_select('activity', 'activity_time',
							       array('activity_type' => 'logins',
								 'activity_ip' => $_SERVER['REMOTE_ADDR']), 1);
			$lastTime = $lastTime['activity_time'];
			$time = get_timestamp();
			$diff = computeTimeDifference($lastTime, $time);
			$minutesSince = $diff['minutes'];
			
			if($minutesSince < $login_wait_penalty)
			{
				echo "Too many username/password attempts. Please wait at least 15 minutes before attempting to login again<br />";
				return false;
			}
			else
			{
				$this->DB->database_delete('activity',
							   array('activity_type' => 'logins',
								 'activity_ip' => $_SERVER['REMOTE_ADDR']), 1);
			}
		}
		
		$salt = $this->DB->database_select('users', 'salt', array('username' => $username), 1);
		
		//Could choose any arbitrary value...user does not exist either way
		if($salt == FALSE)
		{
			$this->addLoginStrikes(); //User tried an incrrect username/password combination, add to login strikes
			return false;
		}
		
		$salt = $salt['salt'];
		
		$password = md5(sha1(md5(sha1($password . $salt))));
		
		$query = $this->DB->database_select('users', '*', array('username' => $username, 'password' => $password), 1);
		
		if($query != FALSE)
		{
			//Clear any existing login strikes against user
			if($this->getLoginStrikes() > 0)
			{
				$this->DB->database_delete('activity',
							   array('activity_type' => 'logins',
								 'activity_ip' => $_SERVER['REMOTE_ADDR']), 1);
			}
			
			//Create user session regardless of group ID
			session_create('USER', $username);
			session_create('uid', $query['uid']);
			
			//Now determine if this user is an administrator (professor)
			$isAdmin = $this->user_getgroup($username);
			
			if($isAdmin != FALSE)
			{
				if($isAdmin == 6) //User belongs to admin group
				{
					session_create('ADMIN', $username);
				}
			}
			
			$this->changeStatus(1); //Set user status to online
			
			return true;
		}
		else 
		{
			$this->addLoginStrikes(); //User tried an incrrect username/password combination, add to login strikes
			return false;
		}
	}
	
	/**
	 * Logout a user
	 */
	
	function user_logout()
	{
		session_terminate();
		$this->changeStatus(0); //Change user status in database to offline
	}
	
	/**
	 * Get how many login attempts the user has tried
	 */
	
	function getLoginStrikes()
	{
		$strikes = $this->DB->database_select('activity', 'activity_strikes',
						array('activity_ip' => $_SERVER['REMOTE_ADDR'], 'activity_type' => 'logins'), 1);
		return ($strikes == false) ? 0 : $strikes['activity_strikes'];
	}
	
	/**
	 * Add login strikes for incorrect login attempt
	 */
	
	function addLoginStrikes()
	{
		$currentAttempts = $this->getLoginStrikes();
		
		if($currentAttempts == false) //This is the first strike, so insert in DB
		{
			$this->DB->database_insert('activity',
						   array('activity_ip' => $_SERVER['REMOTE_ADDR'],
							'activity_type' => 'logins',
							'activity_strikes' => 1));
		}
		else //Update current strike count otherwise
		{
			$currentAttempts++;
			$time = get_timestamp(); //Time strike occurred. Offset against time required for user to wait
			$this->DB->database_update('activity', array('activity_strikes' => $currentAttempts, 'activity_time' => $time),
						   array('activity_type' => 'logins',
							 'activity_ip' => $_SERVER['REMOTE_ADDR']));
			
		}
	}
	
	/**
	 * Determine if a user recently made a new rank
	 */
	
	function changeStatus($status)
	{
		$where = array('uid' => session_get('uid'));
		$this->DB->database_update('users', array('online' => $status), $where);
	}
	
	/**
	 * Sanity/validty check user's input for registration
	 */
	
	function user_check_input()
	{
		$do_register = true;
		$cols = $this->DB->database_build_query('USER_INSERT');
		
		_validate_input($cols, $_POST, false);
		
		$rows = $this->DB->database_select('users', $cols, array('username' => $_POST['username']), 1); //Does username already exist

		if($rows != false)
		{
			$this->errorsrv->set_error("Username already exists");
			$do_register = false;
		}
		
		$rows = $this->DB->database_select('users', 'email', array('email' => $_POST['email'])); //Does email already exist
			
		if($rows != false)
		{
			$this->errorsrv->set_error("Email address already exists");
			$do_register = false;
		}
		
		if(empty($_POST['username']) || empty($_POST['password']) //Are primary fields empty
		   || empty($_POST['password_confirm']) || empty($_POST['email']))
		{
			$this->errorsrv->set_error("No fields can be left blank");
			$do_register = false;
		}
		
		if(!preg_match("/^[A-Za-z0-9_\-\.]+@[A-Z0-9a-z]+\.[a-zA-Z]{2,5}\.?[a-zA-Z]*$/", trim($_POST['email']))) //Check email format
		{
			$this->errorsrv->set_error("Invalid email address");
			$do_register = false;
		}
		
		if($_POST['password'] !== $_POST['password_confirm']) //Confirm that passwords match
		{
			$this->errorsrv->set_error("Passwords do not match");
			$do_register = false;
		}
		
		if(strlen($_POST['password']) < 6 || strlen($_POST['password']) > 12) //Confirm password validity
		{
			$this->errorsrv->set_error("Password must be between 6 and 12 characters");
			$do_register = false;
		}
		
		if(!preg_match("/([a-zA-Z])/", $_POST['password']) || !preg_match("/([0-9])/", $_POST['password']))
		{
			$this->errorsrv->set_error("Password must be contain both numbers and letters");
			$do_register = false;
		}
		
		if (empty($_SESSION['captcha']) || trim(strtolower($_REQUEST['captcha'])) != $_SESSION['captcha']) //Check captcha
		{
			$this->errorsrv->set_error("Invalid captcha");
			$do_register = false;	
		}
		
		return $do_register;
	}
	
	/**
	 * Register a user
	 */
	
	function user_register()
	{
		$do_register = $this->user_check_input(); //Check all input
		$cols = $this->DB->database_build_query('USER_INSERT');
		if($do_register)
		{
			$cols = $this->DB->database_build_query('USER_INSERT', 'INSERT');
		
			extract($_POST); //Extract the user-supplied data for database insertion
			
			//$birthday = "$year-$month-$day";
		
			$salt = $this->generateSalt(); //Generate salt associated with password
		
			foreach($_POST as $key => $value) //Add values to insertion array
			{
				if(array_key_exists($key, $cols))
				{
					if($key == 'password') //Password needs additional processing
					{
						$cols[$key] = md5(sha1(md5(sha1($password . $salt))));
					}
					//else if($key == 'birthday')
					//{
					//	$cols['birthday'] = $birthday;
					//}
					else
						$cols[$key] = $value;
				}
			}
			
			if(!isset($cols['email_hidden']))
			{
				$cols['email_hidden'] = 0;
			}
			
			$cols['salt'] = $salt; //Need to manually add salt to insert columns
			$cols['gid'] = 1; //User will start off as basic user
			
			$ins = false;
			$ins = $this->DB->database_insert('users', $cols);
			
			//user was sucessfully added. now try to create a mailbox for them
			
			if($ins == true)
			{
				$id = $this->DB->database_select('users', array('uid'), array('username' => $username), 1);
				$id = $id['uid'];
				session_create('USER', $username);
				session_create('uid', $id);
				if(!$this->DB->database_insert('mailbox', array('owner' => $username)))
				{
					echo "Warning: Failed to create mailbox<br />";
				}
				return true;
			}
			else 
			{
				$this->errorsrv->set_error(mysql_error(), 'DATABASE');
				return false;
			}
		}
		else 
		{
			return false;	
		}
		return true;
	}
	
	/**
	 * Check all user input for update (different than registering as user may upload a picture and profile description as well)
	 */
	
	function user_check_update_input($uid)
	{
		$update = true;
		
		$user_previous_data = $this->DB->database_select('users', '*', array('uid' => $uid), 1);
		
		$empty_keys = array('firstname', 'lastname', 'email', 'profile_about');
		
		/**
		 * If any fields were empty, do not update row with empty values. Instead, update using previous values
		*/
		
		for($i = 0; $i < count($empty_keys); $i++)
		{
			$key = $empty_keys[$i];
			if(empty($_POST[$key]))
			{
				$_POST[$key] = $user_previous_data[$key];
			}
		}
		
		$_POST['profile_about'] = substr($_POST['profile_about'], 0, 2000);
		$_POST['firstname'] = substr($_POST['firstname'], 0, 50);
		$_POST['lastname'] = substr($_POST['lastname'], 0, 50);
		
		//Special query needed...one of the rare moments when != is used in where context <--- ONLY SPECIAL QUERY USED TO DATE.
		
		$rows = $this->DB->special_query("select * from users where email='
						 ".mysql_real_escape_string($_POST['email'])."' and uid != '$uid'");
		
		$rows = mysql_fetch_array($rows);
		
		if($rows != false)
		{
			$this->errorsrv->set_error("Email address already exists");
			$update = false;
		}
		
		if(!preg_match("/^[A-Za-z0-9_\-\.]+@[A-Z0-9a-z]+\.[a-zA-Z]{2,5}\.?[a-zA-Z]*$/", trim($_POST['email'])))
		{
			$this->errorsrv->set_error("Invalid email address");
			$update = false;
		}
		
		if(!empty($_POST['password']) && ($_POST['password'] != $_POST['password_confirm']))
		{
			$this->errorsrv->set_error("Passwords do not match");
			$update = false;
		}
		
		/*
		 *Need to do a separate check on
		 *password field being empty as data first needed to be compared against confirm data
		*/
		
		if($update && empty($_POST['password']))
		{
			$_POST['password'] = $user_previous_data['password'];
		}
		
		unset($_POST['password_confirm']);
		
		return $update;
		
	}
	
	/**
	 * Method updates user data
	 * If adminAction is set, then the default uid is overridden
	*/
	
	function user_update($adminAction = false)
	{
		if(session_get('username')) //Ensure that this action cannot be done while not logged on
		{
			$uid = ($adminAction) ? $adminAction : $this->user_getid();
			$do_update = $this->user_check_update_input($uid); 
			if($do_update)
			{
				$ins = $_POST;
				array_pop($ins); //We don't need the last post value doUpdateUser...
				
				/*
				 * Get the existing user values, then determine any incoming values that are different
				 * than existing values
				*/
				
				$existingStruct = $this->DB->database_select('users', '*', array('uid' => $uid), 1);
				$diff = array_diff($ins, $existingStruct); //Only these values are to be updated
				
				if(isset($diff['password']))
				{
					$diff['salt'] = $this->generateSalt();
					$diff['password'] = md5(sha1(md5(sha1($diff['password'] . $diff['salt']))));
				}
				
				if(isset($_POST['email_hidden'])) //Did the user want to hide their email?
				{
					$diff['email_hidden'] = true;
				}
				else
				{
					$diff['email_hidden'] = false;
				}
				
				//There needs to be values that have been modified...
				
				if(count($diff) > 0 || !empty($_FILES['profile_picture']['name'])) 
				{
					if(!empty($_FILES['profile_picture']['name']))
					{
						$name = upload_image();
						$diff['profile_image'] = $name;
					}
					if($this->DB->database_update('users', $diff, array('uid' => $uid)))
					{
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Generate security salt for user password
	 */
	
	function generateSalt()
	{
		$lets = range('a', 'z');
		$ups = range('A', 'Z');
		$nums = range('0', '9');
		
		$special = array('*', '%', '#');
		
		$union = $lets + $nums + $ups + $special; //Generate an array with numbers, letters, and special characters
		
		$salt = '';
		
		for($i = 0; $i < 5; $i++) //Create a salt of length 5, supplying random values
		{
			$r = rand(0, count($union)-1);
			$salt .= $union[$r];
		}
		
		return $salt;
	}
	
	/**
	 * Update quizzes completed upon successful quiz completion
	 */
	
	function updateCompletedQuizzes($quizID, $points)
	{
		$current_quizzes = $this->DB->database_select('users', 'quizzes_completed', array('username' => session_get('username')), 1);
		
		if($current_quizzes == false || $current_quizzes['quizzes_completed'] == '') //First quiz completed, simply add field
		{
			$addition = $quizID;
		}
		else //Must add to already populated completed quizzes field
		{
			$current_quizzes = explode(',', $current_quizzes['quizzes_completed']);
			$current_quizzes[] = $quizID;
			$addition = implode(',', $current_quizzes);
		}

		$this->DB->database_update('users', array('quizzes_completed' => $addition), array('username' => session_get('username')));
		$this->DB->database_insert('points', array('uid' => session_get('uid'), 'qid' => $quizID, 'points_obtained' => $points));
	}
	
	/**
	 * Update courses completed upon successful course completion
	 */
	
	function updateCompletedCourses($courseID)
	{
		$current_courses = $this->DB->database_select('users', 'courses_completed', array('username' => session_get('username')), 1);
		
		if($current_courses == false || $current_courses['courses_completed'] == '') //First course completed, simply add field
		{
			$addition = $courseID;
		}
		else //Must add to already completed courses field
		{
			$current_courses = explode(',', $current_courses['courses_completed']);
			$current_courses[] = $courseID;
			$addition = implode(',', $current_courses);
		}

		$this->DB->database_update('users', array('courses_completed' => $addition),
					   array('username' => session_get('username')));
	}
	
	/**
	 * Add $points to user account
	*/
	
	function userAddPoints($points)
	{
		$pns = $this->DB->database_select('users', 'points', array('username' => session_get('username')), 1);
		$add_points = $pns['points'] + $points;
		$gid = $this->user_getgroup();
		$next_gid = $gid+1;
		$points_required = $this->DB->database_select('groups', '*', array('gid' => $next_gid), 1); //Has user has acquired enough points to make a new group rank?
		$points_required = $points_required['points'];
		$newPnts = $this->DB->database_update('users',
						      array('points' => $add_points), array('username' => session_get('username')));
		if($add_points >= $points_required && $gid < 4) //User has made enough points to progress rank!
		{
			$this->doPromote = true;
			$this->DB->database_update('users', array('gid' => $next_gid), array('username' => session_get('username')));
		}
	}
	
	/**
	 * Get ID of a given username
	*/
	
	function user_getid($username = null)
	{
		$user = ($username == null) ? session_get('username') : $username;
		$get = $this->DB->database_select('users', 'uid', array('username' => $user), 1);
		return ($get === false) ? false : $get['uid'];
	}
	
	/**
	 * Get username of a given ID
	*/
	
	function userGetName($uid)
	{
		$username = $this->DB->database_select('users', 'username', array('uid' => $uid), 1);
		return $username['username'];
	}
	
	/**
	 * Get group of *this* user
	*/
	
	function user_getgroup()
	{
		$id = $this->user_getid();
		$perms = $this->DB->database_select('users', 'gid', array('uid' => $id), 1);
		return ($perms === false) ? false : $perms['gid'];
	}
	
	/**
	 * Get group name of a given GID
	*/
	
	function getGroupName($gid = null)
	{
		$id = ($gid == null) ? $this->user_getgroup() : $gid;
		$perms = $this->DB->database_select('groups', 'name', array('gid' => $id), 1);
		return ($perms === false) ? false : $perms['name'];
	}
	
	/**
	 * Get profile data of a given user
	*/

	function user_get_profile_data($id = null)
	{
		$uid = ($id == null) ? $this->user_getid() : $id; //If ID is null, then fetch *this* user profile
		$columns = $this->DB->database_build_query('USER_PROFILE');
		$profile = $this->DB->database_select('users', $columns,
						      array('uid' => $uid), 1);
		if($profile == FALSE)
		{
			return false;
		}
		return $profile;
	}
	
	/**
	 * Get comment data of a given user
	*/
	
	function user_get_comment_data($id = null)
	{
		$uid = ($id == null) ? $_SESSION['uid'] : $id;
		$comments = $this->DB->database_select('user_comments', '*', array('to_user' => $uid));
		return $comments;
	}
	
	/**
	 * Get points of a given user
	*/
	
	function getUserPoints($uid = null)
	{
		$uid = ($uid == null) ? $this->user_getid() : $uid;
		$points = $this->DB->database_select('users', 'points', array('uid' => $uid), 1);
		return $points['points'];
	}
	
	/**
	 * Get total # of online users
	*/
	
	function getOnlineUserCount()
	{
		return $this->DB->database_count('users', array('online' => '1'));
	}
	
	/**
	 * Get online usernames
	*/
	
	function getonlineUserList()
	{
		$list = $this->DB->database_select('users', array('uid', 'username'), array('online' => '1'));
		return $list;
	}
	
	/**
	 * Get total # of registered users
	*/
	
	function getTotalUsers()
	{
		return $this->DB->database_count('users');
	}
	
	/**
	 * Get total # of courses
	*/
	
	function getTotalCourses()
	{
		return $this->DB->database_count('course');
	}
	
	/**
	 * Get newest user
	*/
	
	function getNewestUser()
	{
		$res = $this->DB->database_select('users', array('username'), array(), 1, array('value' => 'uid', 'option' => 'desc'));
		return $res['username'];
	}
	
	/**
	 * Get permissive action of user
	*/
	
	function checkAction($id)
	{
		$check = $this->DB->database_select('users', 'uid', array('username' => session_get('username'), 'uid' => $id), 1);
		return ($check != 0);
	}
	
	/**
	* Return list of users sorted by their points
	* Note: this is the only place that a special query call is made
	*/
    
	function getUsersByRank()
	{
	    $resource = $this->DB->database_select('users', '*', array(), 0, array('value' => 'points', 'option' => 'desc'));
	    return $resource;
	}
    
    /**
     * Bulky method for performing overall user score analysis
     * All of the courses are retrieved. It is determined what quiz sections
     * the user has completed within said courses, and whether or not the user has completed
     * the entire course. The overall points a user obtained from a given course will be calculated as well
    */
    
    function getRankingPoints($id)
    {
        //Define return structure
        
        $struct = array(
                        'title' => array(), //Title of course which quizzes belong to
                        'quiz' => array('section' => array(), 'completed' => array()), //There might be many quizzes in a course
                        'completed' => array()
                        );
        
        //Retrieve list of present courses
        
        $courses = $this->DB->database_select('course', '*');
        
        //Fetch courses a user has taken
        
        $coursesTaken = $this->DB->database_select('users', 'enrolled', array('uid' => $id), 1);
        $coursesTaken = explode(',', $coursesTaken['enrolled']);
        
        //Fetch courses a user has taken
        
        $coursesCompleted = $this->DB->database_select('users', 'courses_completed', array('uid' => $id), 1);
        $coursesCompleted = $coursesCompleted['courses_completed'];
        $coursesCompleted = ($coursesCompleted == '') ? array() : explode(',', $coursesCompleted);
        
        //Now fetch quizzes a user has completed
        
        $quizzesCompleted = $this->DB->database_select('users', 'quizzes_completed', array('uid' => $id), 1);
        $quizzesCompleted = explode(',', $quizzesCompleted['quizzes_completed']); 
        
        
        for($i = 0; $i < count($courses); $i++)
        {
            $struct['title'][] = $courses[$i]['course_title'];
            
            //Grab quizzes associated with course
            $quizzes = $this->DB->database_select('quiz', array('qid', 'quiz_section'), array('quiz_course' => $courses[$i]['cid']));
            
            if($quizzes != false)
            {
                for($q = 0; $q < count($quizzes); $q++)
                {
                    $struct['quiz']['section'][$i][] = $quizzes[$q]['quiz_section'];
                    if(in_array($quizzes[$q]['qid'], $quizzesCompleted))
                    {
                        $struct['quiz']['completed'][$i][] = 'complete';
                    }
                    else
                    {
                         $struct['quiz']['completed'][$i][] = 'incomplete';
                    }
                }
            }
            else
            {
                 $struct['quiz']['section'][] = array();
                 $struct['quiz']['completed'][] = array();
            }
            
            if(!empty($coursesCompleted)) //User may not have completed an entire course yet
            {
                if(in_array($courses[$i]['cid'], $coursesCompleted)) //User has indeed completed course, add statistic
                {
                        $struct['completed'][] = 'complete';
                }
                else
                {
                    $struct['completed'][] = 'incomplete';
                }
            }
            else
            {
                $struct['completed'][] = 'incomplete';
            }
        }
        
        return $struct;
        
    }
    
    /**
     * Computes grades for every completed quiz
     * Every quiz question weight is combined to yield the weight of the quiz
     * The weight of all the quizzes are then summed in order to get the weight of the course
     * and therefore dtermine the user's overall performance in the course
     * DATABASE DESIGN ISSUE: Perhaps weights can be stored in the table and re-calculated whenever
     * a relevant admin function modifies courses/quizzes?
    */
    
    function getGradepoints()
    {
	$struct = array('title' => array(),
			'quiz' => array('section' => array(), 'weight' => array(), 'scored' => array(), 'completed' => array()),
			'weight' => array(),
			'points' => array(),
			'completed' => array()
			);
	
	$courses = $this->DB->database_select('course', '*');
	$enrolled = $this->DB->database_select('users', array('enrolled'), array('uid' => session_get('uid')), 1);
	$enrolled = explode(',', $enrolled['enrolled']);
	
	$quizzes_completed = $this->DB->database_select('users', array('quizzes_completed'),
							array('uid' => session_get('uid')), 1);
	$quizzes_completed = explode(',', $quizzes_completed['quizzes_completed']);
	
	$index = 0;
	for($c = 0; $c < count($courses); $c++)
	{
		
		//If the user is not even enrolled in the course, then they definitely have not performed in it. Skip course
		
		if(!in_array($courses[$c]['cid'], $enrolled))
		{
			continue;
		}
		
		
		$courseWeight = 0;
		$struct['title'][] = $courses[$c]['course_title'];
		$course_points_earned = 0;
		
		
		//Get all the quizzes associated with course
		
		$quizzes = $this->DB->database_select('quiz', 'qid', array('quiz_course' => $courses[$c]['cid']));
		
		for($i = 0; $i < count($quizzes); $i++)
		{
			//User has not completed quiz
			
			if(!in_array($quizzes[$i]['qid'], $quizzes_completed))
			{
				$struct['quiz']['completed'][$c][] = 'incomplete';
				$struct['completed'][$c] = false;
				continue;
			}
			
			$quizWeight = 0;
			$quiz = $this->DB->database_select('quiz_content', 'qcid', array('qid' => $quizzes[$i]['qid']));
			
			//Add points that user scored in quiz
			$points_earned = $this->pointsScored($quizzes[$i]['qid']); 
			
			//Add to total course points scored
			$course_points_earned += $points_earned;
			
			//Determine weight of quiz questions
			for($j = 0; $j < count($quiz); $j++)
			{
				$quizWeight += $this->quizWeight($quiz[$j]['qcid']);
			}
			
			//Add total question weight to quiz weight
			
			$struct['quiz']['weight'][$index][] = $quizWeight;
			$struct['quiz']['scored'][$index][] = $points_earned;
			
			//User has completed quiz or not?
			$struct['quiz']['completed'][$index][] = 'completed';
			
			//Add total quiz weight to quiz score
			
			$courseWeight += $quizWeight;
			
		}
		
		$struct['weight'][$index] = $courseWeight;
		$struct['points'][$index] = $course_points_earned;
		
		
		if(!isset($struct['completed'][$index]))
		{
			$struct['completed'][$index] = 'complete';
		}
		else
		{
			$struct['completed'][$index] = 'incomplete';
		}
		$index++;
	}
	
	return $struct;
    }
    
    
	/**
	* Determine weight of a given quiz
	*/
	function quizWeight($quiz)
	{
	    $fetch = $this->DB->database_select('quiz_content', array('question_weight'), array('qcid' => $quiz));
	    $weight = 0;
	    for($i = 0; $i < count($fetch); $i++)
	    {
		$weight += $fetch[$i]['question_weight'];
	    }
	    return $weight;
	}
    
	/**
	* Determine points scored from quiz
	*/
    
	function pointsScored($quiz)
	{
	    $fetch = $this->DB->database_select('points', array('points_obtained'), array('uid' => session_get('uid'), 'qid' => $quiz), 1);
	    return ($fetch != false) ? $fetch['points_obtained'] : 0;
	}
    
	/**
	* Check if user has completed a challenge
	*/
	
	function challengeCompleted($challenge_id)
	{
	    $current = $this->DB->database_select('users', array('challenges_completed'), array('uid' => session_get('uid')), 1);
	    $current = $current['challenges_completed'];
	    if(!empty($current)) //Has any challenges been completed?
	    {
		    $challenges = explode(',', $current);
		    for($i = 0; $i < count($challenges); $i++)
		    {
			    if($challenges[$i] == $challenge_id)
			    {
				    return true; //Challenge was already completed
			    }
		    }
	    }
	    return false;
	}
	
	/**
	* Get completed challenges from user
	*/
	
	function getChallengesCompleted()
	{
	    $current = $this->DB->database_select('users', array('challenges_completed'), array('uid' => session_get('uid')), 1);
	    $current = $current['challenges_completed'];
	    if(!empty($current))
	    {
		    return $current;
	    }
	    return false; //No challenges completed
	}
	
	/**
	* Update completed challenges
	*/
	
	function updateChallengesCompleted($challenge_id)
	{
	    $add = '';
	    $current = $this->DB->database_select('users', array('challenges_completed'), array('uid' => session_get('uid')), 1);
	    $current = $current['challenges_completed'];
	    
	    if(!empty($current))
	    {
		    $add .= $current . ',' . $challenge_id;
	    }
	    else
	    {
		    $add = $challenge_id;
	    }
	    
	    $this->DB->database_update('users', array('challenges_completed' => $add), array('uid' => session_get('uid')));
	}
	
	/**
	* Get rank of given user
	*/
	
	function getUserRank($id = null)
	{
	    $uid = ($id == null) ? $_SESSION['uid'] : $id;
	    $users = $this->getUsersByRank(); //Get list of users associated by their points
	    for($i = 0; $i < count($users); $i++)
	    {
		    if($users[$i]['uid'] == $uid)
		    {
			    $i++;
			    return $i;
		    }
	    }
	    return 0;
	}
}
?>
