<?php
	include "inc.default.php"; // should be included in EVERY file 
	$oSecurity = new security(FALSE); // not needed to be logged in  
	$oProfile = user($_GET["id"]); 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head> 
    </head>
    <body id="profile">  
		<?php echo $oProfile->html("userpopup.html"); ?> 
    </body>
</html>
