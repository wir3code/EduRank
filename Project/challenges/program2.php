<center>
<?php
/**
 * Challenge #2
 * Concept by: Spencer Brydges 
*/
if(isset($_POST['sum']))
{
    $good = true;
    
    $ex = explode(',', $_POST['sum']);
    
    for($i = 0; $i < count($ex); $i++)
    {
        if($ex[$i] != $_SESSION['answer'][$i])
        {
            $good = false;
            break;
        }
    }
    
    if($good)
    {
        if($UD->challengeCompleted(7))
        {
            echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
        }
        else
        {
            $UD->updateChallengesCompleted(7);
            $UD->userAddPoints(200);
            echo "<p>Congratulations! You have earned 200 points</p>";
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
    if($current_time >= ($_SESSION['program1'] + 7))
    {
        echo "<p>You ran out of time</p>";
        unset($_SESSION['program1']);
        unset($_SESSION['usernames']);
        unset($_SESSION['count']);
    }
}

echo "<p>You have 7 seconds to sum the following matrices and submit in the form of v1,v2,v3,v4,v5
(where v1 matrix1[0][0] + matrix2[0][0], v2 is matrix1[0][1] + matrix2[0][1], etc...):<br /><br />";


$answer = array();

if(!isset($_SESSION['program2']) || $current_time >= ($_SESSION['program2'] + 3))
{
    $_SESSION['matrix1'] = array();
    $_SESSION['matrix2']  = array();
    $_SESSION['program2'] = microtime(true);
    $_SESSION['answer'] = array();
    for($i = 0; $i < 10; $i++)
    {
        for($j = 0; $j < 10; $j++)
        {
            $_SESSION['matrix1'][$i][$j] = rand(0, 9);
        }
    }
    echo "<br />";
    
    for($i = 0; $i < 10; $i++)
    {
        for($j = 0; $j < 10; $j++)
        {
            $_SESSION['matrix2'][$i][$j] = rand(0, 9);
            $_SESSION['answer'] = $_SESSION['matrix1'][$i][$j] + $_SESSION['matrix2'][$i][$j];
        }
    }
}

for($i = 0; $i < 10; $i++)
{
    for($j = 0; $j < 10; $j++)
    {
         echo $_SESSION['matrix1'][$i][$j]. " ";
    }
    echo "<br />";
}
echo "<br />";
    
for($i = 0; $i < 10; $i++)
{
    for($j = 0; $j < 10; $j++)
    {
        echo $_SESSION['matrix2'][$i][$j]. " ";
    }
    echo "<br />";
}
    
?>

<form action='' method='post'>
    Sum: <input type='text' name='sum'> <input type='submit' value='Submit Answer' name='doSubmit'>
</form>

</center>
