<?php

/**
* Spencer Brydges 
* Shefali Chohan 
* quiz.php
* Somewhat tiny file that loads quiz data and displays it
*/

if(!defined('IN_EDU'))
{
	die('');
}

$cdriver = new CourseDriver($db);


if(isset($_GET['cid']))
{
	_validate_input(array('cid'), $_GET);
        $cdriver->setID($_GET['cid']);
}
if(isset($_GET['sid']))
{
	_validate_input(array('sid'), $_GET);
        $cdriver->setSection($_GET['sid']);
}

if(isset($_GET['qid']))
{
	_validate_input(array('qid'), $_GET);
        $cdriver->setQuiz($_GET['qid']);
}

    
    
if(!$cdriver->checkComplete())
{
    if(isset($_POST['dosubmit']))
    {
        $check = $cdriver->iseligible();
        
        //First ensure that the user didn't previously complete the quiz and that they are registered in the course
        if($cdriver->isenrolled() && !$cdriver->checkComplete())
        {
            $struct = $cdriver->computeAnswers();
            if(!$struct)
            {
                echo "Error: You did not supply all answers";
            }
            else
            {
                $points = $struct['add_points'];
                $potential = $struct['total_weight'];
                $right = $struct['questions_correct'];
                $total = $struct['total_questions'];
                $cdriver->setComplete($points);
                echo "Congratulations! Quiz completed successfully. You scored $right/$total and have been awarded $points/$potential points";
                if($UD->getPromote())
                {
                    echo "<br />Congratulations, you have earned enough points to increase your rank! You have been promoted to: " . $UD->getGroupName();
                }
                if($cdriver->checkCourseCompleted())
                {
                    echo "<br />Course completed! Congratulations<br />";
                }
            }
        }
        else
        {
            echo "You are not eligible to complete this quiz";
        }
    }
    if(isset($_GET['cid']) && isset($_GET['sid']) && isset($_GET['qid']) && !isset($_POST['dosubmit']))
    {
            $check = $cdriver->isenrolled();
            if($check == true)
            {
                $quiz_content = $cdriver->fetchQuiz();
                if($quiz_content != null && $quiz_content !== false)
                {
                        $info = $quiz_content['info'];
                        $content = $quiz_content['content'];
                        
                        echo "<p><b>$info[quiz_title]</b><br /><br />This weight of this quiz is out of <b>$info[quiz_weight]</b></p>";
                        
                        echo "<form action='' method='post'>";
                        
                        for($i = 0; $i < count($content); $i++)
                        {
                                echo "<p>".nl2br($content[$i]['quiz_question']).
                                "</p>
                                ";
                                $options = explode(',', $content[$i]['quiz_choices']);
                                if($content[$i]['quiz_choices'] != '')
                                {
                                        for($j = 0; $j < count($options); $j++)
                                        {
                                                echo "<input type=\"radio\" name='$i' value='".$options[$j]."'> $options[$j]";
                                        }
                                }
                                else
                                {
                                        echo "
                                        <textarea name='$i'></textarea>
                                        ";
                                }
                                echo "
                                </input>
                                <br />";
                        }
                        echo "
                        <br /><br />
                        <input type='submit' value='Submit' name='dosubmit'>
                        </form>";
                }
                else
                {
                        if(($error = $cdriver->fetchError()) == false)
                        {
                                echo "Invalid quiz ID";
                        }
                        else
                        {
                                echo $error[0];
                        }
                }
            }
            else
            {
                echo "Cannot view section";
            }
    }
    else
    {
            echo "<p>Invalid quiz ID</p>";
    }
}
else
{
    echo "You have already completed this quiz";
}

?>
