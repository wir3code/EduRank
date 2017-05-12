<center>
<?php
/**
 * Challenge #5
 * Concept by: Spencer Brydges 
*/
    if(!isset($_COOKIE['challenge5_username']))
    {
        setcookie('challenge5_username', 'guest');
    }
    
    if($_COOKIE['challenge5_username'] == 'magic')
    {
        if($UD->challengeCompleted(5))
        {
            echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
        }
        else
        {
            $UD->updateChallengesCompleted(5);
            $UD->userAddPoints(30);
            echo "<p>Congratulations! You have earned 30 points</p>";
        }
    }
    else
    {
        echo "
        <p>
            In order to complete this challenge, you must view this page under the username 'magic'.
        </p>";
    }
?>

</center>
