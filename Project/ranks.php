<?php

/**
* Spencer Brydges
* Shefali Chohan
* ranks.php
* Tiny file that loads ranking data and displays it
*/

if(!defined('IN_EDU'))
{
	die('');
}

$users_by_rank = $UD->getUsersByRank();

echo "<div id='ranking_table'>";

$rank_of = count($users_by_rank);

for($i = 0; $i < $rank_of; $i++)
{
	$image = (empty($users_by_rank[$i]['profile_image'])) ? 'default_profile.jpg' : $users_by_rank[$i]['profile_image'];
	$rank = $i+1;
	echo "<div class='rank'>";
	echo "<table>";
	echo "
	<tr>
		<td>
			<center>
				Rank: $rank of $rank_of
			</center>
			<img src='$file_upload_path".$image."' width='180' height='120' />
		</td>
		<td valign='top'>
			<br />
			Points: ".$users_by_rank[$i]['points']."
			<br />
			Usergroup: ";
			echo $UD->getGroupName($users_by_rank[$i]['gid']);
			echo "
		</td>
	</tr>
	<tr>
		<td>
			<center>
			<a href='?page=profile&id=".$users_by_rank[$i]['uid']."'>".$users_by_rank[$i]['username']."</a>
			</center>
		</td>
	</tr>
	";
	echo "</table>";
	echo "</div>";
}
echo "</div>";

?>
