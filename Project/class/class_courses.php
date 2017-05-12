<?php

/*
 *Spencer Brydges 
 *Shefali Chohan 
 *class_courses.php
 *Class driver for managing and viewing course content.
 *Courses are naturally divided into sections. The class ensures that a given user meets the requirements to view or enroll
 *in a course, and delivers the requested content if the user satisfies the eligibility requirements.
 *Requires: User class
 *Needs: Database object
*/

if(!defined('IN_EDU'))
{
	die('');
}

class CourseDriver
{
	var $courseID; //Store course that user is viewing
	var $sectionID; //Store course section that user is viewing
	var $quizID;
	var $DB; //Object for communicating with database
	var $user;
	
	/*
	 *Initialize database object
	*/
	
	function __construct($ref = null)
	{
		$this->DB = ($ref == null) ? new DatabaseDriver() : $ref;
		$this->user = new UserDriver($this->DB);
	}
	
	/**
	 * Checks to see if a supplied course ID exists
	*/
	
	function exists($id)
	{
		$check = $this->DB->database_select('course', '*', array('cid' => $id));
		return ($check == 0) ? false : true;
	}
	
	/*
	 * Store course ID being viewed. Used by caller
	*/

	function setID($id)
	{
		if($this->exists($id))
		{
			$this->courseID = $id;
			return true;
		}
		return false;
	}
	
	/*
	 * Store section ID being viewed. Used by caller
	*/
	
	function setSection($id)
	{
		$this->sectionID = $id;
	}
	
	function setQuiz($id)
	{
		$this->quizID = $id;
	}
	
	/*
	 * Method receives any errors set by database. If no database errors have occurred,
	 * the method passes false back to the caller. The caller can then differentiate why
	 * a false query was returned i.e., the supplied course simply does not exist due to URL manipulation
	*/
	
	function fetchError()
	{
		return ($this->DB->is_error()) ? $this->DB->get_error() : false;
	}
	
	/*
	* If no course ID or section ID was supplied, a course listing is supplied.
	* This is also viewable by guests
	*/
	
	function fetchAll()
	{
		$selection = $this->DB->database_build_query('COURSE_VIEW');
		$fetch = $this->DB->database_select("course", $selection);
		return $fetch;
	}
	
	/*
	* If a course ID is supplied, grab the course title and course description
	* and return to caller
	*/
	
	function fetchCourse()
	{
		$selection = $this->DB->database_build_query('COURSE_VIEW');
		$fetch = $this->DB->database_select("course", $selection, array('CID' => $this->courseID), 1);
		return $fetch;
	}
	
	/*
	* If a course ID AND section ID is supplied, grab the section content from the database
	* and return to caller
	*/
	
	function fetchContent()
	{
		$selection = $this->DB->database_build_query('COURSECONTENT_VIEW');
		$fetch = $this->DB->database_select("section", $selection, array('cid' => $this->courseID, 'section_id' => $this->sectionID), 1);
		return $fetch;
	}
	
	/*
	* Retrieves secetion listings associated with supplied course
	*/
	
	function fetchSections()
	{
		$selection = $this->DB->database_build_query('SECTIONS_VIEW');
		$fetch = $this->DB->database_select("section", $selection, array('cid' => $this->courseID));
		return $fetch;
	}
	
	function fetchQuiz()
	{
		$selection = $this->DB->database_build_query('QUIZ');
		$fetch = $this->DB->database_select("quiz", $selection, array('qid' => $this->quizID), 1);
		
		//Either an empty set was returned (user is not enrolled) or a database error occurred
		//It is up to the caller to determine what has happened
		
		if($fetch === false)
		{
			return $fetch;
		}
		
		$selection = $this->DB->database_build_query('QUIZCONTENT');
		
		$content_fetch = $this->DB->database_select('quiz_content', $selection, array('qid' => $this->quizID));
		
		$quiz_contents = array("info" => $fetch, "content" => $content_fetch);
		
		return $quiz_contents;
	}
	
	/*
	* Determine if a select user is enrolled in the course or not. If user is not enrolled,
	* caller will appropriately inform the user that they must register for the course
	*/
	
	function isenrolled()
	{
		$selection = $this->DB->database_build_query('USER_REGISTERED');
		$courses = $this->DB->database_select('users', $selection, array('username' => session_get('username')), 1);
		
		//Either an empty set was returned (user is not enrolled) or a database error occurred
		//It is up to the caller to determine what has happened
		
		if($courses == 0) 
		{
			return $courses;
		}
		
		$courses = explode(',', $courses['enrolled']); //Separate enrolled courses
		
		for($i = 0; $i < count($courses); $i++) //Determine if user is enrolled in THIS course out of list
		{
			if($courses[$i] == $this->courseID)
			{
				return true;
			}
		}
		
		return false; //User is not enrolled in course
	}
	
	//Problematic function. Make hooking function for checking errors IN function, do not rely on caller in the future
	//Lots of awkward statements, improve improve improve improve improve improve
	
	function iseligible()
	{
		$errors = 0;
		$return_array = array('set_error_bit' => 0, 'set_error_messages' => array());
		$course_prerequisites = $this->DB->database_select('course', 'course_prerequisites', array('cid' => $this->courseID), 1);
		
		if($course_prerequisites == false)
		{
			if(($error = $this->fetchError()) !== false)
			{
				$return_array['set_error_bit'] = 1;
				$return_array['set_error_messages'][$errors++] = $error;
			}
		}
		else
		{
			if($course_prerequisites['course_prerequisites'] != '')
			{
				$course_prerequisites = explode(',', $course_prerequisites['course_prerequisites']); //Really awkward, make this "better" in the future
				$courses_completed = $this->DB->database_select('users', 'courses_completed', array('username' => session_get('username')), 1);
				if($courses_completed === false)
				{
					if(($error = $this->fetchError()) !== false)
					{
						$return_array['set_error_bit'] = 1;
						$return_array['set_error_messages'][$errors++] = $error;
					}	
				}
				else
				{
					$set_error = true;
					$courses_completed = explode(',', $courses_completed['courses_completed']);
					for($i = 0; $i < count($courses_completed); $i++)
					{
						if(in_array($courses_completed[$i], $course_prerequisites))
						{
							$set_error = false;
						}
					}
					if($set_error)
					{
						$return_array['set_error_bit'] = 1;
						$return_array['set_error_messages'][$errors++] = "Course prerequisites have not been met";
					}
				}
			}
			
		}
		
		$group_prerequisites = $this->DB->database_select('course', 'group_prerequisites', array('cid' => $this->courseID), 1);
		
		if($group_prerequisites == false)
		{
			if(($error = $this->fetchError()) !== false)
			{
				$return_array['set_error_bit'] = 1;
				$return_array['set_error_messages'][$errors++] = $error;
			}
		}
		else
		{
			$group_prerequisites = $group_prerequisites['group_prerequisites'];
			$user_group = $this->DB->database_select('users', 'gid', array('username' => session_get('username')), 1);
			$user_group = $user_group['gid'];
			if($user_group < $group_prerequisites)
			{
				$return_array['set_error_bit'] = 1;
				$return_array['set_error_messages'][$errors++] = "You do not have enough points";
			}
		}

		return $return_array;
	}
	
	/**
	 * Helper method for converting a course ID to its course NAME
	*/
	
	function fetchCourseTitle($cid)
	{
		$course_prerequisites = $this->DB->database_select('course', 'course_title', array('cid' => $cid), 1);
		return $course_prerequisites['course_title'];
	}
	
	function enroll_request()
	{
		$this->DB->database_insert('enrollment_requests', array('cid' => $this->courseID, 'user' => session_get('username')));
	}
	
	/**
	 * Method for determining whether or not a user has applied to enroll in a specific coruse
	*/
	
	function check_enrollment_status()
	{
		$check = $this->DB->database_select('enrollment_requests', '*',
						    array('cid' => $this->courseID,
						    'user' => session_get('username')), 1);
		return ($check == 0) ? false : true;
	}
	
	function setComplete($points)
	{
		$this->user->updateCompletedQuizzes($this->quizID, $points);
	}
	
	function quiz_exists()
	{
		$quiz = $this->DB->database_select('quiz', 'qid', array('quiz_section' => $this->sectionID,
									'quiz_course' => $this->courseID), 1);
		return ($quiz == false) ? false : $quiz['qid'];
	}
	
	/**
	 *Method for determining if a quiz was already completed or not.
	 *Used for when a user submits quiz answers -- need to ensure the quiz was not previously completed by the user..
	*/
	
	function checkComplete()
	{
		//First check what quizzes user has completed
		$check = $this->DB->database_select('users', 'quizzes_completed',
						    array('username' => session_get('username')), 1);
		$check = explode(',', trim($check['quizzes_completed']));
		
		//Walk through completed quizzes and determine if any of the completed quizzes
		//are the quiz they are trying to view and/or submit data to
	
		for($i = 0; $i < count($check); $i++)
		{
			if((
			$this->DB->database_select
			('quiz', 'qid', array('quiz_course' => $this->courseID, 'quiz_section' => $this->sectionID, 'qid' => $check[$i])))
			!= false) //The user already completed the quiz...
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 *Method for computing the quiz answers given by the user
	 *Updates user points based on questions they answered correctly
	 *Returns false if there are answers that are missing -- error is handled by caller
	*/
	
	function computeAnswers()
	{
		$qid = $this->DB->database_select('quiz', 'qid', array('quiz_course' => $this->courseID,
								       'quiz_section' => $this->sectionID), 1);
		$qid = $qid['qid'];
		
		$quiz_content = $this->DB->database_select('quiz_content', '*', array('qid' => $qid));
		
		$answers = $_POST;
		array_pop($answers); //We do not need the last doSubmit post value
		
		if(count($answers) < count($quiz_content)) //Supplied answers do not match number of questions, alert user
		{
			return false;
		}
		
		$struct = array('add_points' => '',
				'questions_correct' => '',
				'total_questions' => '',
				'total_weight' =>'');
		
		$add_points = 0;
		$questions_correct = 0;
		$total_questions = count($quiz_content);
		$total_weight = 0;
		
		for($i = 0; $i < count($quiz_content); $i++)
		{
			$total_weight += $quiz_content[$i]['question_weight'];
			$real_answer = trim($quiz_content[$i]['quiz_answer']);
			$user_answer = trim($answers[$i]);
			
			if($user_answer == $real_answer)
			{
				$add_points += $quiz_content[$i]['question_weight'];
				$questions_correct++;
			}
		}
		$this->user->userAddPoints($add_points);
		$struct['add_points'] = $add_points;
		$struct['questions_correct'] = $questions_correct;
		$struct['total_questions'] = $total_questions;
		$struct['total_weight'] = $total_weight;
		
		return $struct;
	}
	
	/**
	 *Method automatically called whenever a quiz is completed
	 *Determines if the course is completed via the following steps:
	 *A) Fetch all quizzes associated with course
	 *B) Count completed quizzes against # of quizzes associated with course
	*/
	
	function checkCourseCompleted()
	{
		$quizzes = $this->DB->database_select('quiz', 'qid', array('quiz_course' => $this->courseID));
		$total_quizzes = count($quizzes);
		$user_completed = $this->DB->database_select('users', 'quizzes_completed', array('username' => session_get('username')), 1);
		$user_completed = explode(',', $user_completed['quizzes_completed']);
		$user_completed_count = count($user_completed);
		$completed = 0;
		
		for($i = 0; $i < count($quizzes); $i++)
		{
			if(in_array($quizzes[$i]['qid'], $user_completed))
			{
				$completed++;
			}
		}
		
		if($completed == $total_quizzes)
		{
			$add_weight = $this->DB->database_select('course', 'course_weight', array('cid' => $this->courseID), 1);
			$add_weight = $add_weight['course_weight'];
			//$this->user->userAddPoints($add_weight);
			$this->user->updateCompletedCourses($this->courseID);
			return true;
		}
		
		return false;
	}
	
	/**
	* Method is called whenever an admin adds a quiz/section to a course, thus resetting all user completed values
	*/
	
	function resetComplete()
	{
		
	}
}

?>
