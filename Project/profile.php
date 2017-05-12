<?php

/**
* Spencer Brydges 
* Shefali Chohan 
* profile.php
* Somewhat file that loads profile data and passes it off the front-end handler (class_content.php)
*/

if(!defined('IN_EDU'))
{
	die('');
}

if(isset($_GET['act']))
    _validate_input(array('act'), $_GET);
if(isset($_GET['id']))
    _validate_input(array('id'), $_GET);
if(isset($_POST['doUpdate']))
{
    $keys = array_keys($_POST);
    _validate_input($keys, $_POST);
}

$user = new UserDriver($db);

if(!isset($_GET['id']))
{
    $_GET['id'] = $_SESSION['uid'];
}

$profile_data = $user->user_get_profile_data($_GET['id']);
$comment_data = $user->user_get_comment_data($_GET['id']);

if(isset($_POST['doComment']))
{
    $comment = htmlentities($_POST['comment']);
    $user = session_get('uid');
    $datetime = date("Y-m-d h:i:s ");
    $doComment = true;
    $reasons = array();
    
    
    $lastComment = $db->database_select('user_comments',
                                        'comment_date',
                                        array('to_user' => $_GET['id'], 'from_user' => $user),
                                        1,
                                        array('value' => 'cid', 'option' => 'asc'));
    if($lastComment != false)
    {
        $lastComment = $lastComment['comment_date'];
        $diff = computeTimeDifference($lastComment, $datetime);
        $minutesSince = $diff['minutes'];
        if($minutesSince < 2)
        {
            $doComment = false;
            $reasons[] = "You must wait at least two minutes before posting another comment";
        }
    }
    
    if(strlen($comment) > 2000)
    {
        $doComment = false;
        $reasons[] = "Comment must not be over 2000 characters";
    }
    
    if($doComment)
    {

        $db->database_insert('user_comments', array('to_user' => $_GET['id'],
                                           'from_user' => $user,
                                           'comment' => $comment,
                                           'comment_date' => $datetime));
        echo "Commented successfully!<br />";
    }
    else
    {
        echo "Error: Failed to insert the comment for the following reason(s): <br />";
        for($i = 0; $i < count($reasons); $i++)
        {
            echo $reasons[$i];
            echo "<br />";
        }
    }
}

if(isset($_POST['doUpdate']))
{
    $check = $user->checkAction($_GET['id']);
    if($check)
    {
        if($user->user_update())
        {
            echo "Profile updated successfully!";
        }
        else
        {
            echo "Failed to update user profile!";
        }
    }
    else
    {
        $content->_profile_error();
    }
}

if(!$profile_data)
{
    echo "Invalid user ID";
}
else
{
    if(isset($_GET['act']))
    {
        if($_GET['act'] == 'edit')
        {
            $check = $user->checkAction($_GET['id']);
            if($check)
            {
                $content->_profile_editor($profile_data);
            }
            else
            {
                $content->_profile_error();
            }
        }
    }
    else
    {
        if($profile_data['profile_image'] == '')
        {
            $profile_data['profile_image'] = $content->_image_profile_default();
        }
        if($profile_data['profile_about'] == '')
        {
            $profile_data['profile_about'] = "User has not filled their about me!";
        }
        
        $data = $UD->getRankingPoints($_GET['id']);
        
        $content->_profile_viewer($profile_data, $comment_data);
    }
}

$image = $profile_data['profile_image'];
$name = $profile_data['firstname'] . ' ' . $profile_data['lastname'];
$about_user = $profile_data['profile_about'];


?>
