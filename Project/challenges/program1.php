<center>
<?php
/**
 * Challenge #1
 * Concept by: Spencer Brydges 
*/
if(isset($_POST['username']) && in_array(trim($_POST['username']), $_SESSION['usernames']))
{
    $_SESSION['count']++;
    echo "<p>One username down...</p>";
    if($_SESSION['count'] == 10)
    {
        if($UD->challengeCompleted(6))
        {
            echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
        }
        else
        {
            $UD->updateChallengesCompleted(6);
            $UD->userAddPoints(150);
            echo "<p>Congratulations! You have earned 150 points</p>";
        }
    }
}
$current_time = microtime(true);
if(!isset($_SESSION['program1']))
{
    $_SESSION['program1'] = microtime(true);
    $_SESSION['usernames'] = array('Alpha','Omega','Beta','Delta','Gamma','Epsilon','Theta','Sigma','Iota','Mu');
    $_SESSION['count'] = 1;
}
else
{
    if($current_time >= ($_SESSION['program1'] + 4))
    {
        echo "<p>You ran out of time</p>";
        unset($_SESSION['program1']);
        unset($_SESSION['usernames']);
        unset($_SESSION['count']);
    }
}

echo "<p>You have 5 seconds to <b><i>individually</i></b> submit the following 10 usernames:
<br />Alpha<br />Omega<br />Beta<br />Delta<br />Gamma<br />Epsilon<br />
Theta<br />Sigma<br />Iota<br />Mu<br />";

?>

<form action='' method='post'>
    Username: <input type='text' name='username'> <input type='submit' value='Submit Username' name='doSubmit'>
</form>

</center>
