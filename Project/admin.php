<?php

/**
* Spencer Brydges 
* Shefali Chohan
* admin.php
* File is VERY incomplete, on account of the fact that nobody else in the class will even see the very basic functionality it offers.
* May add a whole CMS eventually if time permits.
*/

if(!defined('IN_EDU'))
{
	die('');
}

include 'class/class_admin.php';

if(!session_get('ADMIN'))
{
    die("you are not an admin, goodbye");
}

$adminDriver = new AdminDriver($db);

$enrollCount = $adminDriver->getEnrollmentCount();
echo "
<a href='?page=admin&opt=reqs'>View Enrollment Requests ($enrollCount)</a>
<a href='?page=admin&opt=courses'>Manage Courses</a>
<a href='?page=admin&opt=users'>Manage Users</a>
<a href='?page=admin&opt=news'>Manage News</a><br /><br />
";

if(isset($_GET['opt']))
{
    if($_GET['opt'] == 'reqs')
    {
        if($enrollCount == 0)
        {
            echo "There are currently no enrollment requests<br />";
        }
        else
        {
            if(isset($_POST['doSubmit']))
            {
                $decisions = $_POST;
                array_pop($decisions); //The last value will be 'doSubmit' and not a decision. Remove it.
                print_r($decisions);
                foreach($decisions as $struct => $decision) //Process enrollment decisions made
                {
                    list($user, $cid) = explode(':', $struct);
                    if($decision == 'deny') 
                    {
                        $adminDriver->denyEnrollment($user, $cid);
                    }
                    else
                    {
                        $adminDriver->approveEnrollment($user, $cid);
                    }
                }
            }
            echo "<form action='?page=admin&opt=reqs' method='post'>";
            $enrollRequests = $adminDriver->getEnrollmentRequests();
            for($i = 0; $i < count($enrollRequests); $i++)
            {
                $user = $enrollRequests[$i]['user'];
                $course = $adminDriver->getCourseName($enrollRequests[$i]['cid']);
                
                echo "User $user wishes to enroll in course $course
                <input type='radio' name='$user:".$enrollRequests[$i]['cid']."' value='approve'>Approve
                <input type='radio' name='$user:".$enrollRequests[$i]['cid']."' value='deny'>Deny<br />
                ";
            }
            echo "<br /><input type='submit' value='Submit Decisions' name='doSubmit'>";
        }
    }
    
    else if($_GET['opt'] == 'news')
    {
        echo "<br />";
        if(!isset($_GET['act']))
        {
            $news = $adminDriver->getNews();
            for($i = 0; $i < count($news); $i++)
            {
                $nid = $news[$i]['id'];
                $title = $news[$i]['news_title'];
                echo "<a href='?page=admin&opt=users&act=edit&nid=$nid'>$title</a><br />";
            }
            echo "<br /><br />";
            echo "<center>
                    <form action='?page=admin&opt=news&act=post' method='post'>
                        Title: <input type='text' class='textInput1' name='title' style='width: 800px'><br />
                        Content: <br /><textarea class='textInput1' rows='30' cols='100' name='content'></textarea><br />
                        <input type='submit' value='Submit Article' name='doSubmit'>
                    </form>
                </center>";
        }
        else
        {
            if(isset($_POST['doSubmit']))
            {
                if($adminDriver->submitNews())
                {
                    echo "Submitted news successfully!<br />";
                }
                else
                {
                    echo "Failed to submit news!<br />";
                }
            }
        }
    }
    
    else if($_GET['opt'] == 'users')
    {
        if(!isset($_GET['act']))
        {
            $users = $adminDriver->fetchUsers();
            for($i = 0; $i < count($users); $i++)
            {
                $uid = $users[$i]['uid'];
                $username = $users[$i]['username'];
                echo "<a href='?page=admin&opt=users&act=edit&uid=$uid'>$username</a><br />";
            }
        }
        else
        {
            if($_GET['act'] == 'edit')
            {
                if(isset($_POST['doUpdateUser']))
                {
                    if($adminDriver->updateUser())
                    {
                        echo "User updated successfully!";
                    }
                    else
                    {
                        echo "Failed to update user";
                    }
                }
                else
                {
                    if(isset($_GET['uid']) && $adminDriver->userExists($_GET['uid']))
                    {
                        $struct = $adminDriver->getUserInfo($_GET['uid']);
                        extract($struct);
                        echo "Editing user <b><i>$username</i></b><br />";
                        echo "<a href='?page=admin&opt=users&act=delete&uid=$_GET[uid]'>Delete User</a><br>
                        <a href='?page=admin&opt=users&act=ban&uid=$_GET[uid]'>Ban User</a><br>";
                        
                        echo "<form action='' method='post'>";
                        echo "Username: <input type='text' value='$username' name='username'><br />
                        User group: <br />
                        User email: <input type='text' value='$email' name='email'><br />
                        User password: <input type='password' value='' name='password'><br />
                        Confirm password: <input type='password' value='' name='password_confirm'><br /><br />
                        <input type='submit' value='Update User' name='doUpdateUser'>";
                        echo "</form>";
                    }
                    else
                    {
                        echo "Invalid user ID<br />";
                    }
                }
            }
        }
    }
}

?>
