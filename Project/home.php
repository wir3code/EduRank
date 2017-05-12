<?php
/**
* Spencer Brydges
* Shefali Chohan 
* home.php
* The "actual" front page, which can only be viewed once a user agrees to site conditions and registers or logs in.
* As stated, these files are not heavily commented on, mainly due to time constraints and the need to thoroughly explain the class files
*/
define('IN_EDU', true);
ob_start();
session_start();
if(!isset($_SESSION['username']))
{
	header('Location: index.php');
}
include './class/class_courses.php';
include './class/class_mail.php';
include './class/class_user.php';
include './class/class_content.php';
$ER = new ErrorDriver();
$db = new DatabaseDriver($ER);
$UD = new UserDriver($db, $ER);
$MB = new Mailbox($db);
$content = new ContentManager();
include 'initializer.php';
_validate_user();
?>

<html>
<head>
	<title>Home</title>
	<link rel='stylesheet' type='text/css' href='theme/style.css'>
		
	<script type='text/javascript'>
	function doclear()
	{
		document.getElementById('username').value = '';
		document.getElementById('password').value = '';
	}
	
	function profile_loader(what)
	{
		if (what == 'about')
		{
			document.getElementById("profile_about").style.display = 'inline';
			document.getElementById('profile_comments').style.display = 'none';
			document.getElementById("profile_rank").style.display = 'none';
			document.getElementById("profile_contact").style.display = 'none';
		}
		
		if (what == 'contact')
		{
			document.getElementById("profile_contact").style.display = 'inline';
			document.getElementById("profile_about").style.display = 'none';
			document.getElementById("profile_rank").style.display = 'none';
			document.getElementById('profile_comments').style.display = 'none';
		}
		
		if (what == 'rank')
		{
			document.getElementById("profile_rank").style.display = 'inline';
			document.getElementById("profile_about").style.display = 'none';
			document.getElementById("profile_contact").style.display = 'none';
			document.getElementById('profile_comments').style.display = 'none';
		}
		
		if (what == 'comments')
		{
			document.getElementById('profile_comments').style.display = 'inline';
			document.getElementById("profile_rank").style.display = 'none';
			document.getElementById("profile_about").style.display = 'none';
			document.getElementById("profile_contact").style.display = 'none';
		}
	}
	</script>
</head>
	
<body>
<div id='container'>
<div id='navigationTop'>
<ul>
	<li><a href='?page=home'>Home</a></li>
	<li><a href='?page=course'>Courses</a></li>
	<li><a href='?page=challenges'>Challenges</a></li>
<?php
if(session_get('loggedin'))
{
	$count = get_mail_count(0);
	$display = ($count == 0) ? '' : "(<b>$count</b>)";
	$id = session_get('uid');
	echo "
	<li><a href='?page=grades'>Grades</a></li>
	<li><a href='?page=ranks'>Ranking Ladder</a></li>
	<li><a href='?page=mail'>Mail $display</a></li>";
	if(session_get('ADMIN'))
		echo "<li><a href='?page=admin'>Admin Panel</a></li>";
}
?>
</ul>
<span class='navRight'>
<?php
	if(session_get("username"))
	{
		echo "<li>
			<a href=''>". strtoupper($_SESSION['username']) . "&#9660;</a>
			<ul class='subMenu'>
				<li><a href='?page=profile&id=".$_SESSION['uid']."'>View Profile</a></li>
				<li><a href='?page=logout'>Logout</a></li>
			</ul>
		</li>";
	}
	else
	{
		echo "Login/Register"; 
	}
?>
</span>

</div>
<br />
<br />

	
	<div id='menuLeft'>
		<br />
		<div class='section'>
			<?php
				if(!session_get('username'))
				{
					if(!isset($_POST['doLogin']))
					{
						$content->_display_login();
					}
					else
					{
						if(!$UD->user_login($_POST['username'], $_POST['password']))
						{
							$content->_display_login_error();
							$content->_display_login();
						}
						else
						{
							header('Location: home.php');
						}
					}
				}
				else
				{
					$content->_display_user_stats();
				}
			?>
		</div>
		<div class='section'>
			<?php
				$content->_display_stats();
			?>
		</div>
		<div class='section'>
			<div class='menuHeader'>Notifications</div>
			<p>Add more content here?</p>
		</div>
	</div>
<div id='trail'><br /><a href='?'><img src='images/Site/home.png' /></a><?php compute_trail() ?></div>
<div id='main'>

<?php

if(isset($_GET['message']))
{
	switch($_GET['message'])
	{
		case 'logout':
			echo "Logged out successfully";
	}
}

if(isset($_GET['page']))
{
	_validate_input(array('page'), $_GET);
	switch($_GET['page'])
	{
		case 'register':
			include 'register.php';
			break;
		case 'course':
			if(!isset($_GET['qid']))
				include 'course.php';
			else
				include 'quiz.php';
			break;
		case 'mail':
			include 'mail.php';
			break;
		case 'profile':
			include 'profile.php';
			break;
		case 'admin':
			include 'admin.php';
			break;
		case 'grades':
			include 'grades.php';
			break;
		case 'ranks':
			include 'ranks.php';
			break;
		case 'challenges':
			include 'challenge.php';
			break;
		case 'logout':
			$UD->user_logout();
			session_destroy();
			header('Location: home.php?message=logout');
			break;
		default:
			include 'news.php';
			break;
			
	}
}
else
{
	include 'news.php';
}

?>
</div>

</div>
</body>

</html>
<?php ob_end_flush() ?>
