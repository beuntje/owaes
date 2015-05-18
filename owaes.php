<?php
	include "inc.default.php"; // should be included in EVERY file 
	$oSecurity = new security(TRUE); 
	$oLog = new log("page visit", array("url" => $oPage->filename())); 
   
	$iID = intval($_GET["owaes"]); 
	$oOwaesItem = owaesitem($iID);  
	
	//if ($oOwaesItem->author()->id() == $oSecurity->me()->id()) {
	if ($oOwaesItem->userrights("select", me())) {
		redirect("owaes-selecteer.php?owaes=" . $iID); 
		exit(); 
	}
	
	$oExperience = new experience(me());  
	$oExperience->detail("reason", "pageload");     
	$oExperience->add(1);  
	
	$strType = $oOwaesItem->type()->key(); 
	$oPage->tab("market.$strType");  
	
	if (isset($_POST["addmessage"])) {
		$oConversation = new conversation($oOwaesItem->author()->id()); 
		$oConversation->add($_POST["message"], $oOwaesItem->id() ); 	
	}
	
	/* -- SET $iStatus TO TYPE OF USER (CREATOR TASK, EXECUTOR, SUBSCRIBED OR JUST GUEST -- */
		define ("JOB_SUBSCRIBED", 4); 
		define ("JOB_EXECUTOR", 2); 
		define ("JOB_VIEWER", 3);
		define ("JOB_CREATOR", 1);
		$iStatus = JOB_VIEWER; 
		if ($oSecurity->me()->id() == $oOwaesItem->author()->id())  {
			$iStatus = JOB_CREATOR; 
		} else { 
			foreach ($oOwaesItem->subscriptions() as $iUser=>$oSubscription) {
				if ($iUser == me()) $iStatus = ($oSubscription->state() == SUBSCRIBE_CONFIRMED)?JOB_EXECUTOR:JOB_SUBSCRIBED; 
			}
		}
	/* -- stop -- */
	   
	$oRightcolumnUser = NULL; // als er maar 1 uitvoerder of ingeschrevene is zal deze getoond worden aan de rechterkant 
 
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php
        	echo $oPage->getHeader(); 
		?>
    </head>
    <body id="owaes">               
            <?php echo $oPage->startTabs(); ?> 
    	<div class="container body content content-owaes">
        	
        <div class="row">
					<?php /*echo $oSecurity->me()->html("leftuserprofile.html"); */
                    echo $oSecurity->me()->html("user.html");
                    ?>
                </div>
        
        
      
            
                <div class="sidecenter"> 
                
                
                
                
					<?php echo $oOwaesItem->HTML("owaesdetail.html");  ?> 
				 	
                    <div class="messages">
                             <?php 
                   		        $oConversation = new conversation($oOwaesItem->author()->id());  
						        $oConversation->filter("owaes", $iID); 
						        $oPrevUser = NULL; 
                                echo('<div class="bericht">');
						        foreach ($oConversation->messages() as $oMessage) {
							        if ($oMessage->sender()->id() != me()) $oMessage->doRead(); 
                                   
                                    
                                    if ($oPrevUser!=null & $oMessage->sender() != $oPrevUser) {
                                            echo('</div>');
                                            echo('<div class="bericht">');
                                    }
                                    
							        echo ('<div class="message">'); 
								        if ($oMessage->sender() != $oPrevUser) {
									        echo ('<div class="user">
											        <div class="img">' . $oMessage->sender()->getImage("90x90", TRUE) . '</div>
											        <div class="name"><a href="' . $oMessage->sender()->getURL() . '">' . $oMessage->sender()->getName() . '</a></div>
										        </div>');
								        }
								        echo ('<div class="date">' . str_date($oMessage->sent()) . '</div>
									        <div class="msg">' . html($oMessage->body(), array("p", "a", "strong", "em", "br")) . '</div>'); 
								        echo ('<div class="spacer"></div>'); 
							        echo ('</div>'); 
							        $oPrevUser = $oMessage->sender();
						        }
						
						        $oMe = user(me());  
                            ?>
                            <hr/>
                            <div class="message"> 
                                <form method="post">
                                    <textarea name="message" placeholder="Tik hier uw bericht..." id="postmsg" class="form-control wysiwyg"></textarea>
                                    <input class="btn btn-default pull-right" type="submit" name="addmessage" value="Verzenden" />
                                </form>
                            </div> 
                    </div>
                   
            	</div> 
            <?php echo $oPage->endTabs(); ?>
        </div>
        <div class="footer">
        	<?php echo $oPage->footer(); ?>
        </div>
    </body>
</html>
