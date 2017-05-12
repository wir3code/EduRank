<center>
<?php
/**
 * Challenge #3
 * Concept by: Spencer Brydges
*/
if(isset($_POST['doSubmit']) && $_POST['challenge_password'] == 'overflow')
{
    if($UD->challengeCompleted(8))
    {
        echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
    }
    else
    {
        $UD->updateChallengesCompleted(8);
        $UD->userAddPoints(300);
        echo "<p>Congratulations! You have earned 300 points</p>";
    }
}

    echo "<p>In order to complete this challenge, you must download the following C program and find the password:<br />
    <a href=''>Windows 7 version</a><br />
    <a href=''>Unix version (Compiled on fc17)</a><br />
    <form action='' method='post'>
    Enter the password: <input type='text' name='challenge_password'> <input type='submit' value='Submit' name='doSubmit'>
    </form>";
    
?>
</center>
