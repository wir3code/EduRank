<?php

/**
* Spencer Brydges 
* Shefali Chohan
* ranks.php
* Tiny file that loads initial display data such as user statistics
*/

if(!defined('IN_EDU'))
{
	die('');
}

include_once 'class/class_mail.php';

function init_welcome()
{
    global $UD;
    if(!session_get('username'))
    {
        ?>
        <div class='menuHeader'>Login</div>
	    <form action='' method='post'>
		    Username:<br />
		    <input type='text' value='' name='username'><br />
		    Password:<br />
		    <input type='password' value='' name='password'><br />
		    <input type='submit' value='Login' name='login'>
	    </form>
    <?php
    }
    else
    {
        echo "<div class='menuHeader'>Welcome</div>";
        $points = $UD->getUserPoints();
        echo "Welcome back, " . session_get('username');
        echo "<br /><a href='?logout=true'><b>[Logout]</b></a><br />";
        echo "Points: <b>$points</b>";
    }
}

function init_stats()
{
    global $UD;
    $registered_users = $UD->getTotalUsers();
    $course_count = $UD->getTotalCourses();
    $newest = $UD->getNewestUser();
    $onlineCount = $UD->getonlineUserCount();
    $online = $UD->getOnlineUserList();
    
    echo "<p>";
    echo "Registered users: <b>$registered_users</b><br />";
    echo "Newest registered user: <i><b>$newest</b></i>";
    echo "<br />Online users: <b>$onlineCount</b><br />";
    for($i = 0; $i < count($online); $i++)
    {
        if($i != 0)
        {
            echo ", ";
        }
        echo "<a href='?page=profile&id=".$online[$i]['uid']."'>".$online[$i]['username']."</a>";
    }
    echo "<br />Courses: <b>$course_count</b>";
    echo "</p>";
}

function get_mail_count()
{
    global $MB;
    return $MB->get_mail_count();
}

?>
