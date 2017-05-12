<center>
<?php
/**
 * Challenge #7
 * Concept by: Spencer Brydges 
*/

    if(isset($_POST['input']))
    {
        if(preg_match("/curl_open/", $_POST['input']))
        {
            echo "<h2><font color='red'<b>directorymaster</b></font><br /><br /></h2>";
        }
    }
    if(isset($_POST['doSubmit']))
    {
        if($UD->challengeCompleted(10))
        {
            echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
        }
        else
        {
            if($_POST['challenge_password'] == 'directorymaster')
            {
                $UD->updateChallengesCompleted(10);
                $UD->userAddPoints(120);
                echo "<center><p>Congratulations! You have earned 120 points</p></center>";
            }
            else
            {
                echo "<center><p>Wrong answer, try again.</p></center>";
            }
        }
    }
    else
    {
        echo "
        <p>
            Our web developer has come up with the idea of allowing users to execute arbitrary PHP code. Because open_basedir and safe_mode are enabled, and because fopen, file_get_contents,
            fgets, etc, are disallowed, the web developer feels secure.<br />
            <br />
            Find the password, which is located in password.txt<br />
            (Hint: This was a very popular exploit back in 2007-2008 and was used to compromise many shelled sites)<br /><br />
            <form action='' method='post'>
                Enter PHP code: <br /><textarea rows='10' cols='70' name='input'></textarea><br /> <input type='submit' value='Execute'>
            </form>
            
            <br /><br />
            
            <form action='' method='post'>
                Submit the password: <input type='text' value='' name='challenge_password'> <input type='submit' value='Submit' name='doSubmit'>
            </form>
        </p>";
    }
?>

</center>
