<?
	include "inc.default.php"; // should be included in EVERY file 

	$oSecurity = new security(TRUE); 

	$oLog = new log("page visit", array("url" => $oPage->filename())); 
	
	$strKey = isset($_GET["alias"]) 
		? $_GET["alias"]
		: ( isset($_GET["id"]) ? intval($_GET["id"]) : me() ); 
	


	$oProfile = new user($strKey); // NEW user, want user-functie werkt niet met key
	if ($oProfile->id() == me()) $oProfile->savePostData(); 
	
    $oPage->tab("account");
	 
	$oNotification = new notification(); 
	$oNotification->read("friendship." . $oProfile->id() . "." . me());  


	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?
        	echo $oPage->getHeader(); 
		?>
    </head>
    <body id="profile">
        <? echo $oPage->startTabs(); ?> 
    	<div class="body content content-account-profile container">
        	
            	<div class="row">
					<?  
                    echo $oSecurity->me()->html("templates/user.html");
                    ?>
                </div>
 

					<?  
					
 
						echo $oProfile->html("templates/userprofile.html");  
                    ?>
   

        	<? echo $oPage->endTabs(); ?>
        </div>
        <div class="footer">
        	<? echo $oPage->footer(); ?>
        </div>
    </body>
</html>
