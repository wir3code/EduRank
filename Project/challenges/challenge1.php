<?php
/**
 * Challenge #1
 * Concept by: Spencer Brydges 
*/
    if(!defined('IN_EDU'))
    {
	die('');
    }
    
    if(isset($_POST['doSubmit']))
    {
        if($UD->challengeCompleted(1))
        {
            echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
        }
        else
        {
            if($_POST['challenge_password'] == 'simple')
            {
                $UD->updateChallengesCompleted(1);
                $UD->userAddPoints(5);
                echo "<center><p>Congratulations! You have earned 5 points</p></center>";
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
    Find the password<br />
    (Hint: an extremely basic understanding of HTML and the web is required...as is using a mouse.)<br />
</center>
<!--Web developer note: In case you forget the password, it is 'simple'-->
</p>

<form action='' method='post'>
    <p>
        <center>
        Submit the password: <input type='text' name='challenge_password'><br />
        <input type='submit' value='Submit' name='doSubmit'>
        </center>
    </p>
</form>
