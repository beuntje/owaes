<?
	include "inc.default.php"; // should be included in EVERY file 
	$oSecurity = security(TRUE); 
//	if (!$oSecurity->admin()) $oSecurity->doLogout(); 
	 
	$iGroep = intval($_GET["g"]); 
	$iUser = intval($_GET["u"]); 
	$oGroep = group($iGroep); 
	$oMijnRechten = $oGroep->userrights(me());
	
	switch($_GET["action"]) {
		case "add": 
			if ($oMijnRechten->useradd()) {
				$oGroep->addUser($iUser); 
				echo user($iUser)->HTML("templates/userfromlist.html");
				// echo ("<span class=\"icon icon-addtogroup\"></span><span class=\"title\">Toegevoegd aan " . $oGroep->naam() . "</span>");  
			} else $oSecurity->doLogout(); 
			break; 
		case "del": 
			if ($oMijnRechten->userdel()) {
				$oGroep->removeUser($iUser);
				echo user($iUser)->HTML("templates/userfromlist.html"); 
				//echo ("<span class=\"icon icon-addtogroup\"></span><span class=\"title\">Verwijderd uit " . $oGroep->naam() . "</span>");  
			} else $oSecurity->doLogout();  
			break; 
	} 
?>