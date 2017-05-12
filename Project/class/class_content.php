<?php

/*
 * Spencer Brydges 
 * Shefali Chohan
 * class_content.php
 * This file is not heavily commented -- this class is merely used to process data and display it on the frontend,
 * and thus doesn't need to be explained for the most part.
*/

class ContentManager
{
    var $profileImageDefault;
    
    function __construct()
    {
        $this->profileImageDefault = "default_profile.jpg";
        $this->emailIcon = "./images/Site/icon_email.png";
    }
    
    
    function _image_profile_default()
    {
        return $this->profileImageDefault;
    }
    
    function _display_login()
    {
        ?>
        <center>
            <form action='' method='post' style='display: inline'>
                <input type='text' id='username' style='text-align: center' value='Username' onclick='doclear()' name='username'>
                <input type='password' id='password' style='text-align: center' value='Password' name='password'><br />
                <input type='submit' value='Login' name='doLogin'>
            </form>
        </center>
        <?php
    }
    
    function _display_login_error()
    {
        ?>
        <div class='section'>
            <div class='login_error'>
                <p>Incorrect username/password</p>
            </div>
        </div>
        <?php
    }
    
    function _display_stats()
    {
        global $UD;
        $registered_users = $UD->getTotalUsers();
        $course_count = $UD->getTotalCourses();
        $newest = $UD->getNewestUser();
        $onlineCount = $UD->getonlineUserCount();
        $online = $UD->getOnlineUserList();
        
        echo "<div class='menuHeader'>Statistics</div>";
        echo "<p>";
        echo "Registered users: <b>$registered_users</b></p><br />";
        echo "<p>Newest registered user: <i><b>$newest</b></i></p><br />";
        echo "<p>Online users (<b>$onlineCount</b>): ";
        for($i = 0; $i < count($online); $i++)
        {
            if($i != 0)
            {
                echo ", ";
            }
            echo "<a href='?page=profile&id=".$online[$i]['uid']."'><i><font color='#4bfc85'>".$online[$i]['username']."</font></i></a>";
        }
        echo "<br /><p>Courses: <b>$course_count</b>";
        echo "</p>";
    }
    
    function _display_user_stats()
    {
        global $UD;
        echo "<div class='menuHeader'>Welcome</div>";
        $points = $UD->getUserPoints();
        echo "<p>Rank: <b><br /></b></p>";
        echo "<p>Points: <b>$points<br /></b></p>";
        echo "<center><a href='?page=logout'>[Logout]</a></center>";
    }
    
    function _display_news_all($initData)
    {
        for($i = 0; $i < count($initData); $i++)
        {
            $discussion = ($initData[$i]['comment_count'] == 0) ? "Be the first to comment" : "Join the discussion";
            echo "
            <div class='newsTitle'>
            <a href='?page=news&news_id=".$initData[$i]['id']."'>".$initData[$i]['news_title']."</a>
            </div>";
            echo "
            <div class='newsContent'>";
            echo $initData[$i]['news_content'];
            echo "
            <br /><br />
            <table width='100%'>";
            echo "
            <tr>
            <td colspan='2' style='text-align: right; color: red;'>Posted by ".$initData[$i]['news_author'] . "</td>
            </tr>
            <tr>
            <td>Comments: <b>".$initData[$i]['comment_count']."</b></td>
            <td style='text-align: right; color: red;'>@ ".$initData[$i]['news_date'] . "</td>";
            echo "
            </tr>
            <tr>
            <td colspan='2' style='text-align: center;'>
            <a href='?page=home&news_id=".$initData[$i]['id']."#commentanchor'>$discussion</a>
            </td>
            </tr>
            </table>
            </div>
            <br />";
        }
    }
    
    function _display_news($initData, $commentData)
    {
        global $UD;
        echo "
        <div class='newsTitle'>
            <a href='?page=news&news_id=".$initData['id']."'>".$initData['news_title']."</a>
        </div>";
         echo "
        <div class='newsContent'>";
        echo $initData['news_content'];
            echo "
            <br /><br />
            <table width='100%'>";
            echo "
            <tr>
            <td colspan='2' style='text-align: right; color: red;'>Posted by ".$initData['news_author'] . "</td>
            </tr>
            <tr>
            <td style='text-align: right; color: red;'>@ ".$initData['news_date'] . "</td>";
            echo "
            </tr>
            </table>";
        echo "</div><br />";
         echo "
        <div class='newsTitle'>
           <font style='font-size: 16px' id='commentanchor'>Comments</font>
        </div>";
        
        if($commentData != false)
        {
            echo "<div id='comments'>";
            for($i = 0; $i < count($commentData['username']); $i++)
            {
                //Automatically add a newline if a comment becomes consistently long and doesn't space..
                $comment = format_comment($commentData['comment'][$i]);
                $uid = $UD->user_getid($commentData['username'][$i]);
                if($i % 2 == 0)
                    echo "<div class='userComment'>";
                else
                    echo "<div class='userCommentTwo' style='backgound-color: #10e604'>";
                    
                    echo "<table cols='2' style='width: 100%'>";
                    echo "<tr>";
                    echo "<td valign='top' style='width: 10%; padding-right: 20px;'>
                    <img src='".$commentData['image'][$i]."' height='90px' width='90px'/><br />
                    <a href='?page=profile&id=$uid'><center>".$commentData['username'][$i]."</center></a>
                    </td>";
                    echo "<td valign='top' style='width: 80%'>".$comment."</td>";
                    echo "</tr>
                    <tr>
                        <td colspan='2' style='text-align: right'>
                        ".$commentData['date'][$i]."
                        </td>
                    </tr>
                    <tr>";
                    echo "</tr>";
                    echo "</table>";
                    
                echo "</div>";
            }
            echo "</div>";
        }
        
        if(session_get('uid'))
        {
            echo "<br />
            <div class='commentBox'>
            <form action='' method='post'>
            <b>Voice your opinion</b><br />
            <input type='hidden' name='news_id' value='$_GET[news_id]'>
            <textarea class='commentBox' name='comment'></textarea>
            <br />
            <input type='submit' value='Submit' name='doComment'>
            </form>
            </div>";
        }
        else
        {
            echo "<b>Login to join the discussion</b>";
        }
        
    }
    
    function _user_register()
    {
        echo "<form action='' method='post'>";

        echo "Username: <input type='text' value='' name='username'><br />
        Password: <input type='password' value='' name='password'><br />
        Password Confirm: <input type='password' value='' name='password_confirm'><br />
        Email: <input type='text' value='' name='email'><br />
        Hide email: <input type='checkbox' name='email_hidden' value='1'><br />
        First Name: <input type='text' value='' name='firstname'><br />
        Last Name: <input type='text' value='' name='lastname'><br />
        City: <input type='text' value='' name='city'><br />
        Birthday:
        Day: <select name='day'><option></option>";
        for($i = 1; $i <= 30; $i++)
        {
            $disp = ($i < 10) ? "0$i" : $i;
            echo "<option name='day' value='$disp'>$disp</option>";
        }
        echo "</select>
        Month: <select name='month'>
        <option></option>
        <option name='month' value='01'>January</option>
        <option name='month' value='02'>February</option>
        <option name='month' value='03'>March</option>
        <option name='month' value='04'>April</option>
        <option name='month' value='05'>May</option>
        <option name='month' value='06'>June</option>
        <option name='month' value='07'>July</option>
        <option name='month' value='08'>August</option>
        <option name='month' value='09'>September</option>
        <option name='month' value='10'>October</option>
        <option name='month' value='11'>November</option>
        <option name='month' value='12'>December</option>
        </select>
        Year: <select name='year'><option></option>";
        for($i = 2015; $i >= 1900; $i--)
        {
            echo "<option name='year' value='$i'>$i</option>";
        }
        echo "</select>
        <input type='hidden' name='birthday' value='g'>
        <br />
        <br />
        <img src='./captcha/captcha.php' border='1' />
        <br />
        <br />
        Captcha: <input type='text' value='' name='captcha'>
        <br />
        <br />
        <input type='submit' value='Register' name='register'>";
        echo "</form>";
    }
    
    
    function _profile_viewer($initData, $commentData)
    {
        global $file_upload_path;
        global $UD;
        
        $hidden = ($initData['email_hidden'] == 1) ? true : false;
        $status = ($initData['online'] == 1) ? "icon_online.png" : "icon_offline.png";
        
        echo "
        <div id='profileView'>
            <table>
                <tr>
                    <td width='30%' cellpadding=0 valign='top'>
                        <center>
                        <img src='$file_upload_path".$initData['profile_image']."' height=120 width=140 /><br />
                        <img src='./images/Site/$status' /> ".$initData['username']."
                        </center>
                    </td>
                    <td width='70%' valign='top'>
                        <span class='profileLinks'><a href='#' onclick=\"script:profile_loader('about');\">About</a>
                        <a href='#' onclick=\"script:profile_loader('contact');\">Contact</a>
                        <a href='#' onclick=\"script:profile_loader('comments');\">Comments</a>
                        <a href='#' onclick=\"script:profile_loader('rank');\">Ranking</a></span>
                        <br /><br />
                        <div id='profile_about'>";
                        echo "Name: ".$initData['firstname'] . " " .$initData['lastname']."<br />";
                        echo "About: ".$initData['profile_about']."<br />";
                        echo "</div>";
                        echo "<div id='profile_contact' style='display: none'>";
                        if(!$hidden)
                        {
                            echo "Email: ".$initData['email']."<br />";
                        }
                        else
                        {
                            echo "Email: Hidden<br />";
                        }
                        echo "<a href='?page=mail&compose=".$initData['uid']."'>Send a message</a>";
                        echo "</div>";
                        echo "<div id='profile_rank' style='display: none'>";
                        echo "Points: " . $initData['points'];
                        echo "</div>";
                        echo "<div id='profile_comments' style='display: none'>";
                        for($i = 0; $i < count($commentData); $i++)
                        {
                            $user = $UD->userGetName($commentData[$i]['from_user']);
                            $id = $UD->user_getid($user);
                            echo "<div class='userCommentTwo'>";
                            echo "<a href='?page=profile&id=$id'>".$user."</a> said at " . $commentData[$i]['comment_date'] . "...<br />";
                            echo $commentData[$i]['comment'] . "<br />";
                            echo "</div>";
                        }
                        echo "<center>
                            <form action='' method='post'>
                                <textarea class='textInput1' rows=20 cols=80 name='comment'></textarea><br />
                                <input type='submit' value='Leave Comment for ".$initData['username']."' name='doComment'>
                            </form>
                        ";
                        echo "</div>";
                    echo "
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                    </td>
                </tr>
            </table>";
            
        if($_SESSION['uid'] === $initData['uid'])
        {
            echo "<center><a href='?page=profile&act=edit'>Edit profile</a></center>";
        }
        echo "</div>";
    }
    
    
    function _profile_error()
    {
        echo "You are not authorized to edit this profile<br />";
    }
    
    function _profile_editor($initData)
    {
        $checked = ($initData['email_hidden'] == 1) ? "checked = '1'" : null;
        echo "
        <div id='profileEditor'>
        <form action='' method='post' enctype='multipart/form-data'>
            First Name: <input type='text' value='$initData[firstname]' name='firstname' class='input1'><br />
            Last Name: <input type='text' value='$initData[lastname]' name='lastname' class='input1'><br />
            Email: <input type='text' value='$initData[email]' name='email' class='input1'> Hide email: <input type='checkbox' name='email_hidden' $checked><br />
            Password: <input type='password' value='' name='password' class='input1'><br />
            Password Confirm: <input type='password' value='' name='password_confirm' class='input1'><br />
            Profile Picture: <input type='file' id='profile_picture' name='profile_picture'><br />
            About Me:<br /> <textarea rows='20' cols='35' name='profile_about' class='input2'>".$initData['profile_about']."</textarea><br />
            <input type='submit' value='Update' name='doUpdate'>
        </form>
        </div>";
    }
    
    function _grade_view($data)
    {
        if(count($data['title']) > 0)
        {
            echo "<div id='grades'>";
            echo "<table style='width: 100%' cellspacing=0>";
            echo "<th>Course Name</th><th>Quizzes completed</th><th>Course Score</th>";
    
            for($i = 0; $i < count($data['title']); $i++)
            {
               // if(isset($data['quiz']['completed'][$i]))
                //{
                    $title = $data['title'][$i];
                    
                    
                    $nq = count($data['quiz']['completed']);
                    
                    $cp = 0;
                    for($j = 0; $j < $nq; $j++)
                    {
                        if(isset($data['quiz']['completed'][$i][$j])){
                        if($data['quiz']['completed'][$i][$j] != 'incomplete')
                        {
                            $cp++;
                        }
                        }
                    }
                    
                    $score = $data['points'][$i];
                    $totalWeight = $data['weight'][$i];
                    
                    $percentage = ($totalWeight == 0) ? 0 : (($score/$totalWeight)*100);
                    
                    if($data['completed'][$i] == 'incomplete')
                    {
                        $grade = "<font color='black'><b>I</b></font>";
                    }
                    else if($percentage >= 0 && $percentage <= 49)
                    {
                        $grade = "<font color='red'><b>F</b></font>";
                    }
                    else if($percentage >= 50 && $percentage < 60)
                    {
                        $grade = "<font color='red'><b>D</b></font>";
                    }
                    else if($percentage >= 60 && $percentage < 70)
                    {
                        $grade = "<font color='yellow'><b>C</b></font>";
                    }
                    else if($percentage >= 70 && $percentage < 80)
                    {
                        $grade = "<font color='green'><b>B</b></font>";
                    }
                    else
                    {
                        $grade = "<font color='green'><b>A</b></font>";
                    }
                    
                    $style = ($i % 2 == 0) ? 'td1' : 'td2';
                    echo "<tr class='$style'>";
                    echo "<td>$title</td><td>$cp/$nq</td><td>$score/$totalWeight ($grade)";
                    echo "</tr>";
                //}
            }
            echo "</table>";
            echo "<br /><div id='legend'>Legend:<b><br /><font color='black'>I - Incomplete</font><br /><font color='red'>F - 0 - 49%</font><br /><font color='red'>D - 50% - 59%</font><br /><font color='yellow'>C - 60% - 69%</font><br /><font color='green'>B - 70 - 79%</font><br /><font color='green'>A - 80% - 100%</font><br /></b>";
            echo "</div></div>";
        }
        else
        {
            echo "<div id='legend'><center><b>No grades to display -- start taking courses and quizzes today!</b></center></div>";
        }
    }
    
    function _mail_compose($initData)
    {
         echo "<div id='mailList'>
        <div id='mailMenu'>
        <a href='?page=mail' class='mailMenuItem'>Inbox</a> <a href='?page=mail&view=sent' class='mailMenuItem'>Outbox</a>
        <a class='mailMenuItem' href='?page=mail&compose=true'>Compose</a>
        </div><br />";
        echo "<div id='mailCompose'>
        <form action='' method='post'>";
		echo "To: <input type='text' size='50px' value='$initData' name='sendto'><br /><br />";
		echo "Subject: <input type='text' value='' size='180px' name='subject'><br /><br />";
		echo "<textarea rows='35' cols='150' name='body'></textarea><br />
		<input type='submit' value='Send' name='doSend'>";
		echo "</form>
        </div>";
    }
    
    function _mail_viewmail($messages, $mail, $received = true)
    {
        global $UD;
        global $file_upload_path;
        echo "<div id='mailList'>
        <div id='mailMenu'>
        <a href='?page=mail' class='mailMenuItem'>Inbox</a> <a href='?page=mail&view=sent' class='mailMenuItem'>Outbox</a>
        <a class='mailMenuItem' href='?page=mail&compose=true'>Compose</a>
        </div>
        <form action='' method='post'>";
        
	if($messages === false)
	{
	    echo "Failed to retrieve messages from mailbox<br />";
	}
	else if($messages == 0)
	{
	    echo "You currently have no messages<br />";
	}
	else
	{
	    for($i = 0; $i < count($messages); $i++)
	    {
                if(!(strlen($messages[$i]['body']) <= 25))
                {
                    $messages[$i]['body'] = substr($messages[$i]['body'], 0, 25);
                    $messages[$i]['body'] .= "...";
                }
                echo "<div class='mailListItem'>";
		$id = $messages[$i]['messageID'];
                $wasRead = $messages[$i]['wasread'];
                if(!$wasRead)
                {
                    $subject = "<a href='?page=mail&id=".$messages[$i]['messageID']."'><font color='green'><b>".$messages[$i]['subject']."</b></font></a>";
                }
                else
                {
                    $subject = "<a href='?page=mail&id=".$messages[$i]['messageID']."'>".$messages[$i]['subject']."</a>";
                }
                $from = ($received) ? $mail->get_mailbox_username($messages[$i]['mail_from']) : $mail->get_mailbox_username($messages[$i]['mid']);
		$date = $messages[$i]['mail_date'];
		$wasRead = $messages[$i]['wasread'];
                $picture = $messages[$i]['profile_image'];
                
                $uid = $UD->user_getid($from);
                
                echo "
                <table style='width: 100%'>";
                echo "<tr><td style='width: 20%'></td><td colspan='2'><input type='checkbox' name='".$messages[$i]['messageID']."' value='action'> Subject: $subject</td><td style='text-align: right'>Sent: $date</td></tr>";
                echo "<tr><td><img src='".$file_upload_path.$picture."' height='90px' width='90px' /></td>
                <td colspan='3' rowspan='2'>".$messages[$i]['body']."</td>
                </tr>";
                echo "<tr><td><b><a href='?page=profile&id=$uid'>$from</a></b></td></tr>";
                echo "</table>";
                
                echo "</div>";
                                
	    }
        }
	echo "
        <input type='submit' value='Delete' name='multiDelete'>
        <input type='submit' value='Mark Read' name='multiRead'>
        <input type='submit' value='Mark Unread' name='multiUnread'>
	</form>
        </div>";
    }
    
    function _mail_viewmessage($initData)
    {
        global $file_upload_path;
        echo "<center>Subject: ".$initData['subject']."<br />Date: ".$initData['mail_date']."<br /></center>";
        echo "<div id='mailView'>";
        echo "<table>";
        echo "<tr>";
        echo "<td><img src='$file_upload_path".$initData['profile_image']."' width='120px' height='120px' /><br />
        ".$initData['user']."</td>";
        echo "<td valign='top'>".nl2br($initData['body'])."</td>";
        echo "</tr>";
        echo "</table>";
        echo "</div>";
        echo "<div class='commentBox'><form action='' method='post'>";
        echo "Subject: <input type='text' style='width: 500px;' name='subject' value='".$initData['subject']."'>
        <br /><textarea name='body'></textarea><br />
        <input type='hidden' name='sendto' value='".$initData['user']."'>
        <center><input type='submit' value='Reply' name='doSend'>";
        echo "</form></div>";
    }
    
    function _display_courses($courses)
    {
        $rows = floor(count($courses['cid']) / 3);
        $p = 0;
        
        echo "<div id='courseView'>
        <div class='header'>Courses enrolled in</div><br />
        <table>";
        
        for($i = 0; $i < count($courses['cid']); $i++)
        {
            if(!$courses['enrolled'][$i])
            {
                continue;
            }
            $p++;
            if($p == 1)
            {
                echo "<tr>";
            }
            echo "<td class='td1'><a href='?page=course&cid=".$courses['cid'][$i]."'><b>".$courses['title'][$i]."</b><br /><img src='./images/Course/".$courses['image'][$i]."' width=150 height=120 />
            <br />Eligible to register: ".$courses['eligible'][$i]."</a></td>";
            
            if($p >= $rows && $p % $rows == 1)
            {
                echo "</tr>";
                $p = 0;
            }
        }
        echo "</table>
        <div class='header'>Available Courses</div><br />
        <table>";
        
        
        for($i = 0; $i < count($courses['cid']); $i++)
        {
            if($courses['enrolled'][$i])
            {
                continue;
            }
            $p++;
            if($p == 1)
            {
                echo "<tr>";
            }
            echo "<td  class='td1'><a href='?page=course&cid=".$courses['cid'][$i]."'><b>".$courses['title'][$i]."</b><br /><img src='./images/Course/".$courses['image'][$i]."' width=150 height=120 />
            <br />Eligible to register: ".$courses['eligible'][$i]."</a></td>";
            
            if($p >= $rows && $p % $rows == 1)
            {
                echo "</tr>";
                $p = 0;
            }
        }
        
        echo "</table></div>";
    }
    
    function _display_course($course)
    {
        global $cdriver;
        global $UD;

        echo "<div id='courseView'>";
	echo "<div class='header'>$course[course_title]</div><br />";
	echo "
        <table>
            <tr>
                <td style='none'>
                    <img src='./images/Course/".$course['course_image']."' width=300 height=200 />
                </td>
                <td valign='top'>
                ".$course['course_description']."
                </td>
            </tr>
            <tr>
                <td valign='top'>";
                //There may be course prerequisites that the user must first complete
                if(($course['course_prerequisites'] == '') && ($course['group_prerequisites'] == ''))
                {
                    echo "None";
                }
                else
                {
                    if(!empty($course['course_prerequisites']))
                    {
                        echo "Course Prerequisites: ";
                        $pre_reqs = explode(',', $course['course_prerequisites']);
                        for($i = 0; $i < count($pre_reqs); $i++) //Print out list of course prerequisites
                        {
                            $name = $cdriver->fetchCourseTitle($pre_reqs[$i]);
                            if($i != 0)
                            {
                                echo ", ";
                            }
                                    echo "<i>$name</i>";
                        }
                    }
                    if(!empty($course['group_prerequisites']))
                    {
                        $groupname = $course['group_prerequisites'];
                        $groupname = $UD->getGroupName($groupname);
                        echo "Group Prerequisites: Belong to group ".$groupname." or higher";
                    }
                }
                echo "
                </td>
            </tr>
        </table><br /><center>";
        
        /**
	* If the user is logged on, check to see that they are registered in the course.
	* If they are logged on and not registered, determine if they are eligible to apply for enrollment.
	*/
		
	if(session_get('loggedin'))
	{
	    $isRegistered = $cdriver->isenrolled(); //Check if user is enrolled in course
	    if($isRegistered) //Since user is indeed registered in the course, fetch sections to display for user
	    {
		$sections = $cdriver->fetchSections();
				
		if($sections != FALSE)
		{
                    echo "<table class='sections'>";
		    for($i = 0; $i < count($sections); $i++)
		    {
			$disp = ($i+1);
			echo "<tr><td><a href=".$_SERVER['PHP_SELF'].'?page=course&cid='
			.$_GET['cid'].'&sid='.$sections[$i]['section_id']
			.">$disp) ".$sections[$i]['section_title']
			."</a></td></tr>";
		    }
                    echo "</table>";
		}
		else
		{
		    if(($error = $cdriver->fetchError()) == false)
		    {
			echo "No course sections available";
		    }
		    else
		    {
			echo $error[0];
		    }
		}
	    }
	    else //The user is logged on but not registered
	    {
		$meets_requirements = $cdriver->iseligible(); //Determine if the user can register 
		if($meets_requirements['set_error_bit'] != 1)
		{
		//First determine if user has already sent an enrollment request
		if($cdriver->check_enrollment_status())
		{
		    echo "Your application for enrollment was submitted successfully. Wait 2-5 days for a response.";
		}
		else
		{
		    echo "<a href='?page=course&cid=$_GET[cid]&act=enroll'>Enroll in Course</a>";
		}
		}
		else
		{
		    echo "You do not have the permission to enroll in this course. Reason(s): <br />";
		    for($i = 0; $i < count($meets_requirements['set_error_messages']); $i++)
		    {
			echo $meets_requirements['set_error_messages'][$i] . "<br />";
		    }
		}
	    }
	}
	else //User is not logged on, prompt for them to register
	{
	    echo "<b>Register today in order to enroll</b>";
	}
        
        
        echo "</center></div>";
    }
    
}

?>
