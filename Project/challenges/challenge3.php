<?php
/**
 * Challenge #3
 * Concept by: Spencer Brydges 
*/
    if(!defined('IN_EDU'))
    {
	die('');
    }
    
    if(isset($_GET['include_page']) && preg_match("/^http:\/\/.*\.txt\?$/", $_GET['include_page']))
    {
        echo "<p>Congratulations! At this point in time, your shell/malicious file would be ready for execution, opening up the possibility of rooting the server.<br />
        In the meantime, don't forget to submit the password: mulcishell<br /><br />";
    }
    
    if(isset($_POST['doSubmit']))
    {
        if($UD->challengeCompleted(3))
        {
            echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
        }
        else
        {
            if($_POST['challenge_password'] == 'mulcishell')
            {
                $UD->updateChallengesCompleted(3);
                $UD->userAddPoints(35);
                echo "<center><p>Congratulations! You have earned 35 points</p></center>";
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
    Info: The web developer is now running wild with include().<br />
    However, through the use of apache mods, suspicious strings such as '/etc/passwd' are now rejected, hence the developer feels secure.<br />
    On the contrary, however, allow_url_include is still enabled ;)<br />
    include_page is still being used as the $_GET variable.
    
    <br /><br />
    
    <br /><br />
    Find the password<br />
    (Hint: If your "shell" includes .php as an extension, you are doing it wrong. Be sure to include a '?' in your shell as well.)
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
