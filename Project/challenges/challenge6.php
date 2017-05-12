<?php
/**
 * Challenge #6
 * Concept by: Spencer Brydges 
*/
    if(!defined('IN_EDU'))
    {
	die('');
    }
    
    if(isset($_POST['message']))
    {
        $msg = $_POST['message'];
        if(preg_match("/escape\(document\.cookie\)/", $msg) && preg_match("/\.php\?/", $msg))
        {
            echo "<h2><font color='red'<b>escapeartist</b></font><br /><br /></h2>";
        }
    }
    if(isset($_POST['doSubmit']))
    {
        if($UD->challengeCompleted(9))
        {
            echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
        }
        else
        {
            if($_POST['challenge_password'] == 'escapeartist')
            {
                $UD->updateChallengesCompleted(9);
                $UD->userAddPoints(80);
                echo "<center><p>Congratulations! You have earned 80 points</p></center>";
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
    Info: The web developer just completed a messaging system. Why not send a message to the developer?
    <br /><br />
    <form action='' method='post'>
        Message: <br /><textarea rows='10' cols='50' name='message'></textarea><br />
        <input type='submit' value='Send message'>
    </form>
    <br /><br />
    Find the password<br />
    (Hint: The site stores passwords in cookies)
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
