<?php
/**
 * Challenge #4
 * Concept by: Spencer Brydges 
*/
if(!defined('IN_EDU'))
{
    die('');
}

if(isset($_POST['doSubmit']))
{
    if($UD->challengeCompleted(4))
    {
        echo "<center><p>Sorry, you have already completed this challenge and will be awarded no points</p></center>";
    }
    else
    {
        if($_POST['challenge_password'] == 'injectthis')
        {
            $UD->updateChallengesCompleted(4);
            $UD->userAddPoints(40);
            echo "<center><p>Congratulations! You have earned 40 points</p></center>";
        }
        else
        {
            echo "<center><p>Wrong answer, try again.</p></center>";
        }
    }
}

if(isset($_GET['id']))
{
    if($_GET['id'] == 1)
    {
       echo "<center>
            <p>
                Example content for page 1.
                <br /><br />
                <a href='?page=challenges&challenge=4&id=1'>Page 1</a><br />
                <a href='?page=challenges&challenge=4&id=2'>Page 2</a><br />
                <a href='?page=challenges&challenge=4&id=3'>Page 3</a><br />
            </p>
        </center>";
    }
    
    else if($_GET['id'] == 2)
    {
        echo "<center>
            <p>
                Example content for page 2 (Do something)
                <br /><br />
                <a href='?page=challenges&challenge=4&id=1'>Page 1</a><br />
                <a href='?page=challenges&challenge=4&id=2'>Page 2</a><br />
                <a href='?page=challenges&challenge=4&id=3'>Page 3</a><br />
            </p>
        </center>";
    }
    
    else if($_GET['id'] == 3)
    {
        echo "<center>
        <p>
            Example content for page 3 (Note: this is all irrelevant. Look at the URL instead).
            <br /><br />
            <a href='?page=challenges&challenge=4&id=1'>Page 1</a><br />
            <a href='?page=challenges&challenge=4&id=2'>Page 2</a><br />
            <a href='?page=challenges&challenge=4&id=3'>Page 3</a><br />
        </p>
        </center>";
    }
    
    else if(preg_match("/union\s+all\s+select/i", $_GET['id']) || preg_match("/union\s+select\s+all/i", $_GET['id']))
    {
        $id = $_GET['id'];
        if($id[0] == "'" || $id[0] == "-")
        {
            $comma_count = 0;
            for($i = 0; $i < strlen($id); $i++)
            {
                if($id[$i] == ',')
                {
                    $comma_count++;
                }
            }
            
            if($comma_count == 4)
            {
                if(preg_match("/password/i", $id))
                {
                    if(preg_match("/from site_users/i", $id))
                    {
                        if(preg_match("/where username\s*=\s*'dev'/i", $id))
                        {
                            echo "<center>dev : injectthis</center>";
                        }
                        else
                        {
                            echo "<center>Almost there...you're either missing something or you did not get the username column name correct</center>";
                        }
                    }
                    else
                    {
                        echo "<center>The supplied column does not exist in table site_content</center>";
                    }
                }
                else
                {
                    echo "<center>The supplied column does not exist in table site_content</center>";
                }
            }
            else
            {
                echo "<center>The used SELECT statements have a different number of columns</center>";
            }
        }
    }
}
else
{
    echo "<center>
    
    <p>
        The web developer has decided to ditch includes altogether. Instead, all content will be pulled from the database. The content can be viewed below:
        <br /><br />
        <a href='?page=challenges&challenge=4&id=1'>Page 1</a><br />
        <a href='?page=challenges&challenge=4&id=2'>Page 2</a><br />
        <a href='?page=challenges&challenge=4&id=3'>Page 3</a><br />
        <br /><br />
        The page content is retrieved from a table, namely site_content. In addition, there is a site_users table, which contains all the usernames/passwords/email/and other information
        associated with each registered user.<br /><br />
        Find the password for the user 'dev'<br />
        (HINT: Use incrementing numbers, not nulls, when counting columns. You may have to brute force the column names...or maybe not)
    </p>";
}

?>
    
    <form action='' method='post'>
    <p>
        <center>
        Submit the password: <input type='text' name='challenge_password'><br />
        <input type='submit' value='Submit' name='doSubmit'>
        </center>
    </p>
</form>
    
</center>
