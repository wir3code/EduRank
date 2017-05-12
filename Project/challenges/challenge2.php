<?php
/**
 * Challenge #2
 * Concept by: Spencer Brydges 
*/
    if(!defined('IN_EDU'))
    {
	die('');
    }
    
    if(isset($_GET['include_page']) && $_GET['include_page'] == '../secret/password.txt')
    {
	echo "<h2><font color='red'<b>applewatch</b></font><br /><br /></h2>";
    }
    if(isset($_POST['doSubmit']))
    {
        if($UD->challengeCompleted(2))
        {
            echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
        }
        else
        {
            if($_POST['challenge_password'] == 'applewatch')
            {
                $UD->updateChallengesCompleted(2);
                $UD->userAddPoints(20);
                echo "<center><p>Congratulations! You have earned 20 points</p></center>";
            }
            else
            {
                echo "<center><p>Wrong answer, try again.</p></center>";
            }
        }
    }
?>
<p>
<center>
    Info: The web developer who wrote this uses a $_GET variable, called 'include_page', that includes supplied files from the /inc/ directory. The current directory is /files/. The directory hiearchy is as follows:
    <br /><br />
    /files/
    <br />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/inc/
	<br />
	    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;index.php
	    <br />
	    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;form.php
	    <br />
	    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;mail.php
	    <br />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/secret/
	<br />
	    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;password.txt
    <br /><br />
    Find the password<br />
    (Hint: You cannot simply visit /secret/password.txt. The idea here it to take advantage of a local file inclusion exploit.)
</center>
</p>

<form action='' method='post'>
    <p>
        <center>
        Submit the password: <input type='text' name='challenge_password'><br />
        <input type='submit' value='Submit' name='doSubmit'>
        </center>
    </p>
</form>
