<?php

/**
* Spencer Brydges 
* Shefali Chohan 
* news.php
* Somewhat tiny file that loads news data and passes it off the front-end handler (class_content.php)
*/

if(isset($_GET['news_id']))
    _validate_input(array('news_id'), $_GET);
    
if(isset($_POST['news_id']))
    _validate_input(array('news_id'), $_POST);
    
if(isset($_POST['comment']))
    _validate_input_xss($_POST['comment']);
    
    $onlineCount = $UD->getonlineUserCount();
    $points = $UD->getUserPoints();
    $user_rank = $UD->getUserRank();
    $user_total = $UD->getTotalUsers();
    echo "
    <div id='infostats'>
        <div class='status_online'>$onlineCount Users Online</div> <div class='status_courses'>$points Points</div> <div class='status_points'>Rank $user_rank/$user_total</div>
    </div>";

if(isset($_POST['doComment']))
{
    $comment = htmlentities($_POST['comment']);
    $user = session_get('uid');
    $news_id = $_POST['news_id'];
    $datetime = date("Y-m-d h:i:s ");
    $doComment = true;
    $reasons = array();
    
    $lastComment = $db->database_select('comments',
                                        'comment_date',
                                        array('nid' => $news_id, 'uid' => $user),
                                        1,
                                        array('value' => 'nid', 'option' => 'asc'));
    if($lastComment != false)
    {
        $lastComment = $lastComment['comment_date'];
        $diff = computeTimeDifference($lastComment, $datetime);
        $minutesSince = $diff['minutes'];
        if($minutesSince < 2)
        {
            $doComment = false;
            $reasons[] = "You must wait at least two minutes before posting another comment";
        }
    }
    
    if(strlen($comment) > 2000)
    {
        $doComment = false;
        $reasons[] = "Comment must not be over 2000 characters";
    }
    
    if($doComment)
    {

        $db->database_insert('comments', array('nid' => $news_id,
                                           'uid' => $user,
                                           'comment' => $comment,
                                           'comment_date' => $datetime)); 
    }
    else
    {
        echo "Error: Failed to insert the comment for the following reason(s): <br />";
        for($i = 0; $i < count($reasons); $i++)
        {
            echo $reasons[$i];
            echo "<br />";
        }
    }
}
if(!isset($_GET['news_id']))
{
    $data = $db->database_select('news', '*', array(), 0, array('value' => 'id', 'option' => 'desc'));
    for($i = 0; $i < count($data); $i++)
    {
        if(strlen($data[$i]['news_content']) > 200)
        {
            $data[$i]['news_content'] = substr($data[$i]['news_content'], 0, 200);
            $data[$i]['news_content'] .= "...<a href='?page=news&news_id=".$data[$i]['id']."'>[ Read More ]</a>";
        }

        $data[$i]['news_author'] = $UD->userGetName($data[$i]['news_author']);
        $data[$i]['comment_count'] = $db->database_count('comments', array('nid' => $data[$i]['id']));
    }
    
    $content->_display_news_all($data);
}
elseif(isset($_GET['news_id']))
{
    $data = $db->database_select('news', '*', array('id' => $_GET['news_id']), 1);
    if($data != false)
    {
        $data['news_author'] = $UD->userGetName($data['news_author']);
        $comments = $db->database_count('comments', array('nid' => $_GET['news_id']));
    
        if($comments == false)
        {
            $content->_display_news($data, false);
        }
        else
        {
            $comments = array('username' => array(), 'image' => array(), 'comment' => array(), 'date' => array());
            
            $comment_fetch = $db->database_select('comments', '*', array('nid' => $_GET['news_id']));
            
            for($i = 0; $i < count($comment_fetch); $i++)
            {
                $user_info = $db->database_select('users',
                                                  array('username', 'profile_image'),
                                                  array('uid' => $comment_fetch[$i]['uid']),
                                                  1);
                $comments['username'][] = $user_info['username'];
                $comments['image'][] = ($user_info['profile_image'] == '')
                                                ? $content->_image_profile_default()
                                                : $user_info['profile_image'];
                                                
                //Automatically add a newline if a comment becomes consistently long and doesn't space..
                $comments['comment'][] = format_comment($comment_fetch[$i]['comment']);
                
                $comments['date'][] = format_timestamp($comment_fetch[$i]['comment_date'], 'comment');
                
                $comments['reply'][] = $comment_fetch[$i]['reply'];
            }
            
            $content->_display_news($data, $comments);
        }
    }
    else
    {
        echo "Invalid news ID";
    }
}


?>
