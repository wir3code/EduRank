<?php

/**
* Spencer Brydges
* Shefali Chohan
* grade.php
* Tiny file that loads grade data and passes it off the front-end handler (class_content.php)
*/

if(!defined('IN_EDU'))
{
	die('');
}


$data = $UD->getGradePoints();

$content->_grade_view($data);

?>
