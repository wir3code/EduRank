<?php
/**
* Spencer Brydges 
* Shefali Chohan 
* index.php
* The starting of a long and complicated website
* Note: All files in the root directory are relatively small and therefore not commented on heavily, as they simply display or load data. The real
* machines driving this site are all commented and explained heavily, all of which reside in /class/
* The /captcha/ directory contains the only 3rd party code that we have used.
*/


define('IN_EDU', true); //This constant must be set for a file to be viewed -- user is locked out from seeing all other files through their browser as a result
session_start();
ob_start();
include './class/class_user.php';
$ER = new ErrorDriver();
$db = new DatabaseDriver($ER);
$user = new UserDriver($db);
_validate_user();

$errors = array();

if(!isset($_SESSION['understood']))
{
    header('Location: disclaimer.php');
}

if(isset($_POST['doRegister']))
{
    if($user->user_register())
    {
	header('Location: home.php');	
    }
    else 
    {
	$errors = $user->error();
    }
}

if(isset($_POST['doLogin']))
{
    $login = $user->user_login($_POST['username'], $_POST['password']);
    if($login == FALSE)
    {
	$errors[] = "Invalid username/password, try again";
    }
    else
    {
	header('Location: home.php');
    }
}

?>

<html>
<head>
    <title>Edu Net</title>
    <link rel='stylesheet' type='text/css' href='./theme/style.css'>
</head>

<body>
    
    
    <div id='landing'>
    <?php
    if(!isset($_GET['act']))
    {
        echo "
        <form action='' method='post'>
            <p>Login using your Edu Net ID<br /></p>
            <center>
                <p>Username: <input type='text' value='' name='username' class='input1'/></p>
                <p>Password: <input type='password' value='' name='password' class='input1'/></p>
                <p><input type='submit' value='Login' name='doLogin' class='input2'></p>";
                if(count($errors) > 0)
                {
                    for($i = 0; $i < count($errors); $i++)
                        echo "<p><font color='red'>$errors[$i]</font></p>";
                }
                echo "
                <p><a href='?act=register'>Don't have a Edu Net ID?</a></p>
            </center>
        </form>
        ";
    }
    else
    {
        echo "
        <form action='' method='post'>";
            if(count($errors) > 0)
            {
                echo "<p>Failed to register. Reason(s): </p><ul>";
                for($i = 0; $i < count($errors); $i++)
                    echo "<font color='red'><li>$errors[$i]</li></font>";
                echo "</ul>";
            }
            echo "
            <p>Create an Edu Net ID<br /></p>
            <center>
                <p>Username: <input type='text' value='' name='username' class='input1'/></p>
                <p>Password: <input type='password' value='' name='password' class='input1'/></p>
                <p>(Confirm): <input type='password' value='' name='password_confirm' class='input1'/></p>
                <p>Email: <input type='text' value='' name='email' class='input1'/></p>
                <p><img src='./captcha/captcha.php' border='1' /></p>
                <p>Captcha: <input type='text' value='' name='captcha' class='input1'></p>
                <p><input type='submit' value='Register' name='doRegister' class='input2'></p>
                <p><a href='?'>Have a Edu Net ID?</a></p>
            </center>
        </form>";
    }
    ?>
    </div>
    
</body>

</html>

<?php   ob_end_flush()      ?>
