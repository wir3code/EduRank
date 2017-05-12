<?php

session_start();
ob_start();
if(isset($_POST['doSubmit']))
{
    $_SESSION['understood'] = true;
    header('Location: index.php');
}

?>
<html>
    <head>
        <title>Disclaimer</title>
    </head>
<body>
<p>
    <h3>
    <center><b>Disclaimer</b></center><br />
In order to use this website, you are required to abide by the following terms:<br /><br />

1) Our site does not encourage nor condone any illicit activities that occur from outside of it, including (but not limited to) hacking into private or public systems, auditing private or public systems, or making any unauthorized attempt to illegally view or alter any data associated with the aforementioned systems.
<br />
<br />
2) The content on this site is meant for educational purposes only. The challenges are strictly intended to give you, the user, an understanding of vulnerabilities and exploits as they pertain to system security, and to use that knowledge in order to fortify, NOT destruct, systems.  
<br />
<br />
3) Knowledge is power. Use it at your own discretion. If you disagree, here is the <a href='http://www.google.ca/'>door</a>.<br />
</h3>
<center>
    <form action='' method='post'>
        <input type='submit' value='I hereby agree to the following terms' name='doSubmit'>
    </form>
</center>
</p>

</body>
</html>
<?php ob_end_flush(); ?>