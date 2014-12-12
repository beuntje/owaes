<?
	include "inc.default.php"; // should be included in EVERY file 
	$oSecurity = new security(TRUE); 
	$oPage->addJS("https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true");

	$oProfile = user(me()); 
	
	$arErrors = array(); 
	
	if (isset($_POST["profile"])) {
		$oProfile->firstname($_POST["firstname"]); 
		$oProfile->lastname($_POST["lastname"]); 
		if (!$oProfile->email($_POST["email"])) $arErrors["email"] = "e-mailadres bestaat reeds in het systeem"; 
		$oProfile->alias($_POST["alias"], TRUE); 
		if (!$oProfile->login($_POST["username"])) $arErrors["username"] = "De opgegeven gebruikersnaam bestond al. Er werd een nieuwe gegenereerd"; 
		$oProfile->location($_POST["location"], $_POST["locationlat"], $_POST["locationlong"] ); 
		$oProfile->description($_POST["description"]); 
		$oProfile->gender($_POST["gender"]); 
		$oProfile->visible(isset($_POST["visible"])); 
		$oProfile->visible("firstname", $_POST["showfirstname"]);
		$oProfile->visible("lastname", $_POST["showlastname"]); 
		$oProfile->visible("description", $_POST["showdescription"]); 
		$oProfile->visible("gender", $_POST["showgender"]); 
		$oProfile->visible("location", $_POST["showlocation"]); 
		$oProfile->visible("img", $_POST["showimg"]); 
		$oProfile->visible("email", $_POST["showemail"]); 
		
		$oProfile->telephone($_POST["telephone"]); 
		$oProfile->visible("telephone", $_POST["showtelephone"]); 
		$oProfile->birthdate(ddmmyyyyTOdate($_POST["birthdate"]));  
		$oProfile->visible("birthdate", $_POST["showbirthdate"]); 
		
		if (($_POST["password1"] == $_POST["password2"]) && (trim($_POST["password1"]) != "")) $oProfile->password($_POST["password1"]); 
		$bImageUploaded = FALSE; 
		if ($_FILES["img"]["error"] == 0){  
			$strTmp = "upload/tmp/" . $_FILES["img"]["name"]; 
			move_uploaded_file($_FILES["img"]["tmp_name"], $strTmp);
			createProfilePicture($strTmp, $oProfile->id()); 
		}

		$oProfile->update(); 
		
		if ($bImageUploaded) $oProfile->addbadge("photo"); 
	}

	list($iLat, $iLong) = $oProfile->LatLong(); 
	
    $oPage->tab("account");



?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?
        	echo $oPage->getHeader(); 
		?>
        <script>
		
			// GOOGLE MAP
			var map;
			<?
				if ($iLat + $iLong != 0) {
					echo ("var startpos = new google.maps.LatLng(" . ($iLat + 0.011) . ", " . ($iLong + 0.018) . ");");  
				} else {
					echo ("var startpos = new google.maps.LatLng(50.8305300, 3.2644600);"); 
				}
			?> 
			var marker; 
			function initialize() {
				var mapOptions = {
					zoom: 12,
					center: startpos,
					disableDefaultUI: true,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
				<?
					if ($iLat + $iLong != 0) {
						echo ("setMarker(new google.maps.LatLng(" . $iLat . ", " . $iLong . ")); "); 
					} 
				?>
			}
			google.maps.event.addDomListener(window, 'load', initialize);
			

			// MAPS CHANGE
			var iTimerZoek = 0; 
			$(document).ready(function() {
				$("input#location").keyup(function() { 
					clearTimeout(iTimerZoek);
					iTimerZoek = setTimeout("geozoek();", 1000); 
				})
				$("input#location").change(function() { 
					geozoek(); 
				})
				$("#frmprofile").submit(function() {
					$("div.foutmelding").remove(); 
					bValidated = true; 
					arMeldingen = {}; 
					
					strPW1 = $("#password1").val(); 
					strPW2 = $("#password2").val(); 
					if ((strPW1 != "")||(strPW2 != "")) if (strPW1 != strPW2) arMeldingen["#password2"] = "wachtwoord komt niet overeen"; 
					
					if ($("#email").val() == "") arMeldingen["#email"] = "E-mailadres is verplicht"; 
					if (!validateEmail($("#email").val())) arMeldingen["#email"] = "Het opgegeven e-mailadres is incorrect"; 

					
					for (var strKey in arMeldingen){ 
						$(strKey).after($("<div>").addClass("foutmelding alert alert-dismissable alert-danger").html(arMeldingen[strKey]));  
					}

					if (Object.keys(arMeldingen).length == 0) {
						return true; 
					} else {
						$('html,body').animate({scrollTop: $(Object.keys(arMeldingen)[0]).offset().top-50});
						return false; 
					}
				})
			})
			
			function geozoek() {
				clearTimeout(iTimerZoek); 
				strVal = $("input#location").val(); 
				if (strVal == "") {
					$("input#locationlong").val(0);
					$("input#locationlat").val(0);
					deleteMarker(); 
				} else {
					$.ajax({
						type: "POST",
						url: "details.location.php", 
						data: "search=" + escape(strVal), 
						success: function(strResult){ 
							arLoc = strResult.split("|"); 
							if (arLoc.length == 2) {
								if (($("input#locationlong").val() != arLoc[0])||($("input#locationlat").val() != arLoc[1])){
									$("input#locationlong").val(arLoc[0]);
									$("input#locationlat").val(arLoc[1]);
									setMarker(new google.maps.LatLng(arLoc[1], arLoc[0]));   
								}
							}  else deleteMarker(); 
						}
					}); 
				}
			}
			
			function setMarker(oPos) {
				if (marker) marker.setMap(null);
				marker = new google.maps.Marker({
					map:map,
					draggable: false, 
					animation: google.maps.Animation.DROP, 
					position: oPos
				  });	
				  map.panTo(marker.getPosition()); 
				  map.setZoom(14); 
			}
			
			function deleteMarker() {
					if (marker) marker.setMap(null);
					map.setZoom(12); 
			}
		</script>
    </head>
    <body id="settings">
        <? echo $oPage->startTabs(); ?> 
    <div class="body content content-account-settings container">
        	
            	<div class="row">
					<? /*echo $oSecurity->me()->html("leftuserprofile.html"); */
                    echo $oSecurity->me()->html("user.html");
                    ?>
                </div>
                <div class="container sideleftcenter">
                	<form method="post" name="frmprofile" id="frmprofile" class="form-horizontal" enctype="multipart/form-data"> 
                    <fieldset>
                    <legend>Basisgegevens</legend>
                    <div class="form-group">
                            <label for="alias" class="control-label col-lg-2">Alias of schuilnaam:</label>
                            <div class="col-lg-6">
                                <input type="text" name="alias" class="alias form-control" id="alias" placeholder="Alias" value="<? echo inputfield($oProfile->alias()); ?>" />
                            </div>
                            <div class="col-lg-4">
                                <select class="form-control" disabled>
                                    <option>Zichtbaar voor iedereen</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="lastname" class="control-label col-lg-2">Familienaam:</label>
                            <div class="col-lg-6">
                                <input type="text" name="lastname" class="lastname form-control" id="lastname" placeholder="Familienaam" value="<? echo inputfield($oProfile->lastname()); ?>" />
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showlastname", $oProfile->visible("lastname")); ?> 
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="firstname" class="control-label col-lg-2">Voornaam:</label>
                            <div class="col-lg-6">
                                <input type="text" name="firstname" class="firstname form-control" id="firstname" placeholder="Voornaam" value="<? echo inputfield($oProfile->firstname()); ?>" />
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showfirstname", $oProfile->visible("firstname")); ?> 
                            </div>
                        </div>
                        <legend>Accountgegevens</legend>
                        <div class="form-group">
                            <label for="username" class="control-label col-lg-2">Login:</label>
                            <div class="col-lg-6">
                                <input type="text" name="username" class="username form-control" id="username" placeholder="Login" value="<? echo inputfield($oProfile->login()); ?>" />
                                    <?
                            	        if (isset($arErrors["username"])) echo "<dd class=\"fout\">" . $arErrors["username"] . "</dd>"; 
							        ?>
                            </div>
                            <div class="col-lg-4">
                                <select class="form-control" disabled>
                                    <option>Verborgen</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password1" class="control-label col-lg-2">Wachtwoord:</label>
                            <div class="col-lg-6">
                                <input type="password" name="password1" class="password1 form-control" id="password1" placeholder="Wachtwoord" />
                            </div>
                            <div class="col-lg-4">
                                <select class="form-control" disabled>
                                    <option>Verborgen</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password2" class="control-label col-lg-2">Herhalen:</label>
                            <div class="col-lg-6">
                                <input type="password" name="password2" class="password2 form-control" id="password2" placeholder="Wachtwoord herhalen" />
                            </div>
                            <div class="col-lg-4" disabled>
                                
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="control-label col-lg-2">E-mailadres:</label>
                            <div class="col-lg-6">
                                <input type="text" name="email" class="email form-control" id="email" placeholder="E-mailadres" value="<? echo inputfield($oProfile->email()); ?>" />
                                    <?
                            	        if (isset($arErrors["email"])) echo "<dd class=\"fout\">" . $arErrors["email"] . "</dd>"; 
							        ?>
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showemail", $oProfile->visible("email")); ?>  
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="telephone" class="control-label col-lg-2">Telefoon:</label>
                            <div class="col-lg-6">
                                <input type="text" name="telephone" class="telephone form-control" id="telephone" placeholder="Telefoon" value="<? echo inputfield($oProfile->telephone()); ?>" />
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showtelephone", $oProfile->visible("telephone")); ?> 
                            </div>
                        </div>
                        <legend>Persoonlijke gegevens</legend>
                        <div class="form-group">
                            <label for="visible" class="control-label col-lg-2">Zichtbaar:</label>
                            <div class="col-lg-10 checkbox">
                                <input type="checkbox" name="visible" id="visible" value="1" <? if ($oProfile->visible()) echo 'checked="checked"' ?> /> <label for="visible">Dit profiel mag getoond worden in het overzicht van gebruikers</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="control-label col-lg-2">Over jezelf:</label>
                            <div class="col-lg-6">
                                <textarea name="description" id="description" class="form-control" placeholder="Vertel ons iets over jezelf..."><? echo textarea($oProfile->description()); ?></textarea>
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showdescription", $oProfile->visible("description")); ?> 
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="control-label col-lg-2">Geslacht:</label>
                            <div class="col-lg-6">
								 <select name="gender" class="gender form-control" id="gender" placeholder="Geslacht">
                                 	<option value="" <? if ($oProfile->gender() == "") echo "selected"; ?>>onbepaald</option>
                                 	<option value="male" <? if ($oProfile->gender() == "male") echo "selected"; ?>>man</option>
                                 	<option value="female" <? if ($oProfile->gender() == "female") echo "selected"; ?>>vrouw</option> 
                                 </select>
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showgender", $oProfile->visible("gender")); ?> 
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="control-label col-lg-2">Geboortedatum:</label>
                            <div class="col-lg-6">
								 <input name="birthdate" class="birthdate form-control" id="geboortedatum" placeholder="birthdate" value="<?
                                 	if ($oProfile->birthdate() != 0) echo str_date($oProfile->birthdate(), $strFormat = "d-m-Y"); 
								 ?>" /> 
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showbirthdate", $oProfile->visible("birthdate")); ?> 
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="img" class="control-label col-lg-2">Foto:</label>
                            <div class="col-lg-6">
                                <input type="file" name="img" ext="jpg,jpeg,gif,bmp,png" class="img image form-control" id="img" placeholder="" value="" />
                                <? echo $oProfile->getImage(); ?>
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showimg", $oProfile->visible("img")); ?>  
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="location" class="control-label col-lg-2">Woonplaats:</label>
                            <div class="col-lg-6">
                                <input type="text" name="location" id="location" class="location form-control" placeholder="Woonplaats" value="<? echo inputfield($oProfile->location()); ?>" />
                                <input type="hidden" name="locationlat" id="locationlat" value="<? echo $iLat; ?>" />
                                <input type="hidden" name="locationlong" id="locationlong" value="<? echo $iLong; ?>" />
                                <div id="map-canvas" style="height: 200px; "></div>
                            </div>
                            <div class="col-lg-4">
                                <? echo showDropdown("showlocation", $oProfile->visible("location")); ?> 
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input type="submit" value="Gegevens opslaan" id="profile" class="btn btn-default pull-right" name="profile" />
                            </div>
                        </div> 
                        </fieldset>
					</form>
                </div> 
        	<? echo $oPage->endTabs(); ?>
        </div>
        <div class="footer">
        	<? echo $oPage->footer(); ?>
        </div>
    </body>
</html>
