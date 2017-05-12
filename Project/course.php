<?php

/**
* Spencer Brydges 
* Shefali Chohan 
* course.php
* Would have been nice to actually create course content...
*/

if(!defined('IN_EDU'))
{
	die('');
}

$cdriver = new CourseDriver($db); //Initialize driver class for handling course views/registrations/etc

/**
 * Check the following input: cid for SQL injection attacks,
 * sid for SQL injection attacks,
 * and qid for SQL injection attacks
*/

$check_array = array();

if(isset($_GET['cid']))
	$check_array[] = 'cid';
if(isset($_GET['sid']))
	$check_array[] = 'sid';
	
if(!empty($check_array))
	_validate_input($check_array, $_GET);
	
if(isset($_GET['act']))
{
	$process_request = true;
	$index = 0;
	$cid = '';
	if(!isset($_GET['cid']))
	{
		$process_request = false;
		$errors[$index++] = 'You did not supply a course to register in';
	}
	if(isset($_GET['cid']))
	{
		$cid = $_GET['cid'];
		if(!$cdriver->setID($cid))
		{
			$process_request = false;
			$errors[$index++] = 'Invalid course ID';
		}
		if($cdriver->isenrolled())
		{
			$process_request = false;
			$errors[$index++] = 'Invalid course ID';
		}
		if($cdriver->check_enrollment_status())
		{
			$process_request = false;
			$errors[$index++] = 'Already sent request';
		}
	}
	if(!session_get('username'))
	{
		$process_request = false;
		$errors[$index++] = 'You are not logged onto the system';
	}
	
	if(!$process_request)
	{
		echo "Failed to process application request. Reason(s): ";
		for($i = 0; $i < $index; $i++)
		{
			echo "$errors[$i]<br />";
		}
	}
	else
	{
		$cdriver->enroll_request();
		echo "Application request sent successfully!<br />";
	}
}

/**
 * Check to see that the user wishes to view only a course and not a specific course section
*/

if(isset($_GET['cid']) && !isset($_GET['sid']))
{
	$exists = $cdriver->setID($_GET['cid']); //Set course ID in preparation for course fetch
	
	/**
	 * Does the course ID exist? Check
	*/
	
	if($exists)
	{
		$course = $cdriver->fetchCourse(); //Attempt to fetch course content
		$content->_display_course($course);
	}
	else //The course could not be viewed due to a database error or a malformed course ID
	{
		if(($error = $cdriver->fetchError()) == false)
		{
			echo "Invalid course ID";
		}
		else
		{
			echo $error[0];
		}
	}
}

/**
 * The user wishes to view a specific course section
*/

else if(isset($_GET['cid']) && isset($_GET['sid']) && !isset($_GET['qid']))
{
	$cdriver->setID($_GET['cid']);
	$cdriver->setSection($_GET['sid']);
	
	/**
	 * Check to see if user is registered for this section -- isenrolled() will return false for guests, no need to check
	 * here
	*/
	
	$isRegistered = $cdriver->isenrolled();
	if($isRegistered)
	{
		$content = $cdriver->fetchContent();
		if($content != null && $content !== false)
		{
			echo "<b>$content[section_title]</b><br /><br /><p>$content[section_content]</p><br />";
			
			if(($quizID = $cdriver->quiz_exists()))
			{
				echo "<a href='?page=course&cid=$_GET[cid]&sid=$_GET[sid]&qid=$quizID'>Take the quiz</a> ";
				if($cdriver->checkComplete())
				{
					echo "(Already completed)";
				}
			}
		}
		else
		{
			if(($error = $cdriver->fetchError()) == false)
			{
				echo "Invalid section or course ID";
			}
			else
			{
				echo $error[0];
			}
		}
	}
	else
	{
		echo "You are not registered in this section";
	}
}
else //All courses must be displayed
{
	$courses = $cdriver->fetchAll();
	$display_courses = array('cid' => array(),
				 'title' => array(),
				 'prereqs' => array(),
				 'image' => array(),
				 'descr' => array(),
				 'eligible' => array(),
				 'enrolled' => array());

	for($i = 0; $i < count($courses); $i++)
	{
		$cdriver->setID($courses[$i]['cid']);
		$display_courses['enrolled'][] = ($cdriver->isenrolled()) ? true : false;
		$display_courses['title'][] = $courses[$i]['course_title'];
		$display_courses['cid'][] = $courses[$i]['cid'];
		$display_courses['descr'][] = $courses[$i]['course_description'];
		$display_courses['image'][] = $courses[$i]['course_image'];
		$check = $cdriver->iseligible();
		$display_courses['eligible'][] = ($check['set_error_bit'] == 0) ? "Yes" : "No";
		
		if(empty($courses[$i]['course_prerequisites']))
		{
			$display_courses['prereqs'][] = 'None';
		}
		else
		{
			$str = '';
			$pre_reqs = explode(',', $courses[$i]['course_prerequisites']);
			
			for($j = 0; $j < count($pre_reqs); $j++) //Get list of course prerequisites
			{
				$name = $cdriver->fetchCourseTitle($pre_reqs[$j]);
				if($j != 0)
				{
					$str .= ', ';
				}
				$str .= $name;
			}
			
			$display_courses['prereqs'][] = $str;
		}
	}
	
	$content->_display_courses($display_courses);
}

?>
