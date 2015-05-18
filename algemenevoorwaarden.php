<?php
	include "inc.default.php"; // should be included in EVERY file 

	$oSecurity = new security(TRUE); 

	$oLog = new log("page visit", array("url" => $oPage->filename())); 
	 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php
        	echo $oPage->getHeader(); 
		?>
    </head>
    <body id="profile">
        <?php echo $oPage->startTabs(); ?> 
    	<div class="body content container">
        	
            	<div class="row">
					<?php  
                    echo $oSecurity->me()->html("user.html");
                    ?>
                </div>
                <div>
                	
                    <?php
                    	echo template("algemenevoorwaarden.html");  
					?>
                </div>
 
        	<?php echo $oPage->endTabs(); ?>
        </div>
        <div class="footer">
        	<?php echo $oPage->footer(); ?>
        </div>
    </body>
</html>
