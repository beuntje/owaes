<?
	include "inc.default.php"; // should be included in EVERY file 
	$oSecurity = new security(FALSE); 
   
	$iID = intval($_GET["owaes"]); 
	$oOwaesItem = new owaesitem($iID);  
	
	if ($oOwaesItem->author() != $oSecurity->me()->id()) {
		$oSecurity->doLogout(); 
		exit(); 
	}
	
	
	/* -- IF CREATOR CHOOSES EXECUTORS -- */ 
	/*	if (isset($_POST["selectusers"])) {
			foreach($_POST["employ"] as $iUser) { 
				$oOwaesItem->addSubscription($iUser, SUBSCRIBE_CONFIRMED); 
				$oConversation = new conversation($iUser); 
				$oConversation->add("Uw inschrijving werd bevestigd", $oOwaesItem->id() ); 
			} 
			foreach ($oOwaesItem->subscriptions() as $iUser=>$iValue) {
				if ($iValue != 2) {
					$oOwaesItem->addSubscription($iUser, SUBSCRIBE_CANCEL); 
					$oConversation = new conversation($iUser); 
					$oConversation->add("u werd niet gekozen voor deze opdracht", $oOwaesItem->id() ); 	
				}	
			}  
			$oOwaesItem->state(STATE_SELECTED); 
			$oOwaesItem->update();  
		}
	/* -- stop -- */
	
	
	/* -- IF CREATOR REWARDS AN EXECUTOR -- */
	/*	if (isset($_POST["beloon"])) { 
			//$oTransaction = new transaction($iID, $_POST["other"]);  // niet nodig want wordt zowieso geladen op deze pagina
			//echo ("hier"); 
			// class checks post and submits
		} 
	/* -- stop -- */
	
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?
        	echo $oPage->getHeader(); 
		?> 
        <script>
			$(document).ready(function() { 
			}); 
		</script> 
    </head>
    <body id="owaes"> 
    	<div class="body">
        	<? echo $oPage->startTabs(); ?>
            	<div class="sideleft">
					<? echo $oSecurity->me()->html("templates/leftuserprofile.html"); ?>
                </div>
                <div class="sidecenterright"> 
					<? echo $oOwaesItem->HTML("templates/owaesdetail.html");  ?> 
					<?
						echo ("<form method=\"post\">");  
								echo ("<div class=\"box\" >
										<h2>Beloon</h2> 
									"); 
									foreach ($oOwaesItem->subscriptions() as $iUser=>$iValue) {
										switch ($iValue) {
											case SUBSCRIBE_CONFIRMED: 
												$oUser = new user($iUser);
												echo ("<div id='user" . $oUser->id() . "'>");  
												echo ($oUser->html("templates/userid.html")); 
												echo ("</div>"); 
												break;  
										}
									}  
							echo ("</div>"); 
							
							
							 
							?>   
							<?
						echo ("</form>");  
                            
                    ?> 
            	</div> 
            <? echo $oPage->endTabs(); ?>
        </div>
        <div class="footer">
        	<? echo $oPage->footer(); ?>
        </div>
    </body>
</html>