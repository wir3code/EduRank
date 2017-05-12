<?php

/**
* Spencer Brydges
* Shefali Chohan 
* challenge.php
* This file fills the void left from the lack of course material.
* Initializes front end, no need for comments.
*/

if(!defined('IN_EDU'))
{
    die('');
}
    
if(!isset($_GET['challenge']) && !isset($_GET['program']))
{
    echo "<div id='challengeView'>
            <div class='header'>
            Web Hacking Challenges
            </div>
            <table style='width: 100%;'>
                <tr>
                    <td>
                        <a href='?page=challenges&challenge=1'>Challenge #1: Preliminary</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Very easy
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='?page=challenges&challenge=2'>Challenge #2: Local File Inclusion</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Easy
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='?page=challenges&challenge=3'>Challenge #3: Remote File Inclusion</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Easy
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='?page=challenges&challenge=4'>Challenge #4: SQL Injection</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Medium
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='?page=challenges&challenge=5'>Challenge #5: User spoofing</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Easy
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='?page=challenges&challenge=6'>Challenge #6: Cross-site Scripting</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Medium
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='?page=challenges&challenge=7'>Challenge #7: Security Circumventing</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Hard
                    </td>
                </tr>
            </table>
            <br /><br />
            <div class='header'>
            Programming challenges (Understanding of PHP, C, and x86 Assembly Required)
            </div>
            <table style='width: 100%;'>
                <tr>
                    <td>
                        <a href='?page=challenges&program=1'>Challenge #1: Brute Force</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Medium
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='?page=challenges&program=2'>Challenge #2: Matrix Computation</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Medium
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='?page=challenges&program=3'>Challenge #3: Application Cracking</a>
                    </td>
                    <td style='text-align: right'>
                        Difficulty: Hard
                    </td>
                </tr>
            </table>
        </div>";
}
else
{
    if(isset($_GET['challenge']))
    {
        switch($_GET['challenge'])
        {
            case 1:
                include './challenges/challenge1.php';
                break;
            case 2:
                include './challenges/challenge2.php';
                break;
            case 3:
                include './challenges/challenge3.php';
                break;
            case 4:
                include './challenges/challenge4.php';
                break;
            case 5:
                include './challenges/challenge5.php';
                break;
            case 6:
                include './challenges/challenge6.php';
                break;
            case 7:
                include './challenges/challenge7.php';
                break;
        }
    }
    
    else if(isset($_GET['program']))
    {
        switch($_GET['program'])
        {
            case 1:
                include './challenges/program1.php';
                break;
            case 2:
                include './challenges/program2.php';
                break;
            case 3:
                include './challenges/program3.php';
                break;
        }
    }
}

?>
