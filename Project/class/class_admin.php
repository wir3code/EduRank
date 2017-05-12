<?php

/*
 * Spencer Brydges 
 * Shefali Chohan 
 * class_admin.php
 * This class is used for encapsulating the many details pertaining to administrative tasks -- modifying users and courses,
 * denying and approving course registration requests, etc.
*/

include_once 'class_mail.php'; //Emails will be sent out for application decisions
include_once 'class_user.php'; //User modification is to be done

class AdminDriver
{
    var $DB;
    var $MB;
    var $UD;
    
    /**
     *Constructor. If no database reference is supplied, a new database object must be created
    */
    
    function __construct($ref = null)
    {
        $this->DB = ($ref == null) ? new DatabaseDriver() : $ref;
        $this->MB = new MailBox($this->DB);
        $this->UD = new UserDriver($this->DB);
    }
    
    /**
     * Method to obtain the total number of enrollment requests that must be decided on
    */
    
    function getEnrollmentCount()
    {
        $count = $this->DB->database_count('enrollment_requests');
        return $count;
    }
    
    /**
     * Obtain the enrollment requests
    */
    
    function getEnrollmentRequests()
    {
        $fetch = $this->DB->database_select('enrollment_requests');
        return $fetch;
    }
    
    /**
     * Translate a course ID into a course name (should ideally move this function to class_courses...)
    */
    
    function getCourseName($cid)
    {
        $courseName = $this->DB->database_select('course', 'course_title', array('cid' => $cid), 1);
        return $courseName['course_title'];
    }
    
    /**
     * Method to deny a user's enrollment request for a given course
    */
    
    function denyEnrollment($user, $cid)
    {
        $courseName = $this->getCourseName($cid); //Need course name for email
        $this->MB->set_subject("Enrollment application for $courseName: Denied");
        $this->MB->set_body("We are sorry to inform you that your application to enroll in $courseName
                            has been rejected. We encourage you to apply at a later time when
                            you are a more qualified applicant. Thank you.");
        $this->MB->set_sendto($user);
        $this->MB->set_sendfrom('admin');
        $this->MB->send_mail(); //Sed user an email informing them of their rejection
        $this->DB->database_delete('enrollment_requests', array('user' => $user, 'cid' => $cid)); //Delete request from database
    }
    
    /**
     * Method to approve a user's enrollment request for a given course
    */
    
    function approveEnrollment($user, $cid)
    {
        $courseName = $this->getCourseName($cid); //Get course name for email
        $this->MB->set_subject("Enrollment application for $courseName: Approved");
        $this->MB->set_body("We are pleased to inform you that your application to enroll in $courseName
                            has been processed successfully. You may immediately begin reading the course content
                            and completing the quizzes.");
        $this->MB->set_sendto($user);
        $this->MB->set_sendfrom('admin');
        $this->MB->send_mail(); //Send user email
        $this->DB->database_delete('enrollment_requests', array('user' => $user, 'cid' => $cid)); //Delete enrollment request
        
        $currently_enrolled = $this->DB->database_select('users', 'enrolled', array('username' => $user), 1); //Add course ID to user registered table
        
        $currently_enrolled = explode(',', $currently_enrolled['enrolled']);
        
        $index = count($currently_enrolled)+1;
        $currently_enrolled[$index] = $cid;
        
        $currently_enrolled = implode(',', $currently_enrolled);
        
        $this->DB->database_update('users', array('enrolled' => $currently_enrolled), array('username' => $user)); 
    }
    
    /**
     * Fetch all the registered users for display
    */
    
    function fetchUsers()
    {
        $users = $this->DB->database_select('users', array('username', 'uid'));
        return $users;
    }
    
    /**
     * Determine if a given user exists (should just make userExists in UD static...)
    */
    
    function userExists($uid)
    {
        return $this->UD->userExists($uid);
    }
    
    /**
     * Update given user (Note: the fact that parameter is set in user_update implies an admin action...)
    */
    
    function updateUser()
    {
        return $this->UD->user_update($_GET['uid']);
    }
    
    /**
     * Fetch data associated with given user ID
    */
    
    function getUserInfo($uid)
    {
        $info = $this->DB->database_select('users', '*', array('uid' => $uid), 1);
        return $info;
    }
    
    /**
     * Fetch all news
     */
    
    function getNews()
    {
        $ret = $this->DB->database_select('news', '*');
        return $ret;
    }
    
    function submitNews()
    {
        $date = get_timestamp();
        $username = $_SESSION['uid'];
        $content = htmlentities($_POST['content']);
        $title = htmlentities($_POST['title']);
        
        if($this->DB->database_insert('news', array('news_title' => $title, 'news_content' => $content, 'news_date' => $date, 'news_author' => $username)))
        {
            return true;
        }
        return false;
    }
}


?>
