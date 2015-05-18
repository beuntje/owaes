<?php
	include "inc.default.php"; // should be included in EVERY file
	$oSecurity = new security(TRUE);
	if (!$oSecurity->admin()) stop("admin");
	$oPage->addJS("script/admin.js");
	$oPage->addCSS("style/admin.css");
	function addressToCoordinates($address) {
		$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=" . $address;
		$response = file_get_contents($url);
		$json = json_decode($response, TRUE);
		$coord = array(
			"latitude" => $json["results"][0]["geometry"]["location"]["lat"],
			"longitude" => $json["results"][0]["geometry"]["location"]["lng"]
		);
		return $coord;
	}
	function coordinatesToAddress($lat, $lon) {
		$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&latlng=" . $lat . "," . $lon;
		$response = file_get_contents($url);
		$json = json_decode($response, TRUE);
		$address = $json["results"][0]["address_components"][2]["long_name"];
		return $address;
	}
	
	function encryptor($text) {
		$key = pack("H*", "cf372282683d4802ee035e793218e2e4a8a8eb4f6a1d5675b6a6a289c860abde");
		$key_size = strlen($key);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$block = mcrypt_get_block_size("rijndael_256", "cbc");
		$pad = $block - (strlen($text) % $block);
		$text .= str_repeat(chr($pad), $pad);
		$cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_CBC, $iv);
		$cipherText = $iv . $cipherText;
		$encodeCipher = base64_encode($cipherText);
		return $encodeCipher;
	}
	function prepareAndExecuteStmt($key, $val, $dbPDO) {
		$query = "UPDATE `tblConfig` SET `value` = ? WHERE `key` LIKE ?";
		$stmt = $dbPDO->prepare($query);
		$stmt->bindParam(1, $val);
		$stmt->bindParam(2, $key);
		$stmt->execute();
	}
	function issetAndNotEmpty($field) {
		$test = false;
		if (isset($field) && !empty($field)) {
			$test = true;
		}
		return $test;
	}
	if (isset($_POST["btnOpslaan"])) {
		/* Start waarden */
		if (issetAndNotEmpty($_POST["txtTemplateFolder"])) prepareAndExecuteStmt("domain.templatefolder", $_POST["txtTemplateFolder"], $dbPDO);
		$test = "FALSE";
		if (isset($_POST["chkAlgemenevoorwaarden"])) $test = "TRUE";
		prepareAndExecuteStmt("startvalues.algemenevoorwaarden", $test, $dbPDO);
		$test = "FALSE";
		if (isset($_POST["chkVisibility"])) $test = "TRUE";
		prepareAndExecuteStmt("startvalues.visibility", $test, $dbPDO);
		if (isset($_POST["txtAnalytics"])) prepareAndExecuteStmt("analytics", $_POST["txtAnalytics"], $dbPDO);
		/* ------------- */
		/* Debugging */
		$test = "FALSE";
		if (isset($_POST["chkShowwarnings"])) $test = "TRUE";
		prepareAndExecuteStmt("debugging.showwarnings", $test, $dbPDO);
		$test = "FALSE";
		if (isset($_POST["chkDemo"])) $test = "TRUE";
		prepareAndExecuteStmt("debugging.demo", $test, $dbPDO);
		/* ------------- */
		/* Verzekeringen */
		if (issetAndNotEmpty($_POST["txtV1"])) prepareAndExecuteStmt("verzekeringen.1", $_POST["txtV1"], $dbPDO);
		if (issetAndNotEmpty($_POST["txtV2"])) prepareAndExecuteStmt("verzekeringen.2", $_POST["txtV2"], $dbPDO);
		/* ------------- */
		/* Tijdzone en lokatie */
		prepareAndExecuteStmt("date.timezone", $_POST["lstTimezone"], $dbPDO);
		if (isset($_POST["txtLokatie"])) {
			$coord = addressToCoordinates($_POST["txtLokatie"]);
			prepareAndExecuteStmt("geo.latitude", $coord["latitude"], $dbPDO);
			prepareAndExecuteStmt("geo.longitude", $coord["longitude"], $dbPDO);
		}
		/* ------------- */
		/* Credits */
		if (isset($_POST["txtStart"])) prepareAndExecuteStmt("startvalues.credits", $_POST["txtStart"], $dbPDO);
		if (isset($_POST["txtMin"])) prepareAndExecuteStmt("credits.min", $_POST["txtMin"], $dbPDO);
		if (isset($_POST["txtMax"])) prepareAndExecuteStmt("credits.max", $_POST["txtMax"], $dbPDO);
		if (issetAndNotEmpty($_POST["txtEenheid"])) prepareAndExecuteStmt("credits.name.1", $_POST["txtEenheid"], $dbPDO);
		if (issetAndNotEmpty($_POST["txtMeervoud"])) prepareAndExecuteStmt("credits.name.x", $_POST["txtMeervoud"], $dbPDO);
		if (issetAndNotEmpty($_POST["txtOverdracht"])) prepareAndExecuteStmt("credits.name.overdracht", $_POST["txtOverdracht"], $dbPDO);
		/* ------------- */
		/* Mail */
		$test = "FALSE";
		if (isset($_POST["chkSMTP"])) $test = "TRUE";
		prepareAndExecuteStmt("mail.smtp", $test, $dbPDO);
		if (isset($_POST["txtHost"])) prepareAndExecuteStmt("mail.Host", $_POST["txtHost"], $dbPDO);
		
		$test = "FALSE";
		if (isset($_POST["chkAuth"])) $test = "TRUE";
		prepareAndExecuteStmt("mail.SMTPAuth", $test, $dbPDO);
		if (isset($_POST["txtSecure"])) prepareAndExecuteStmt("mail.SMTPSecure", $_POST["txtSecure"], $dbPDO);
		if (isset($_POST["txtPort"])) prepareAndExecuteStmt("mail.Port", $_POST["txtPort"], $dbPDO);
		if (isset($_POST["txtUsername"])) prepareAndExecuteStmt("mail.Username", $_POST["txtUsername"], $dbPDO);
		$pwd = null;
		if (!empty($_POST["txtPasswd"])) {
			$query = "SELECT `value` FROM `tblConfig` WHERE `key` LIKE 'mail.Password'";
			$result = $dbPDO->query($query);
			$pwd = $_POST["txtPasswd"];
			foreach ($result as $p) {
				if ($pwd != $p["value"]) {
					$pwd = encryptor($pwd);
				}
			}
		}
		prepareAndExecuteStmt("mail.Password", $pwd, $dbPDO);
		/* ------------- */
		/* Facebook loginapp */
		if (isset($_POST["txtFbId"])) prepareAndExecuteStmt("facebook.loginapp.id", $_POST["txtFbId"], $dbPDO);
		if (isset($_POST["txtFbSecret"])) prepareAndExecuteStmt("facebook.loginapp.secret", $_POST["txtFbSecret"], $dbPDO);
		/* ------------- */
		redirect(filename());
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<? echo $oPage->getHeader(); ?>
	</head>
	<body id="index">
		<? echo $oPage->startTabs(); ?>
		<div class="body">
			<div class="container">
				<div class="row">
					<? echo $oSecurity->me()->html("user.html"); ?>
				</div>
				<div class="main market admin">
					<? include "admin.menu.xml"; ?>
					<h1>Configuratie paneel</h1>
					<form id="frmConfig" method="POST">
						<fieldset>
							<legend>Start waarden</legend>
							<p>
								<label for="txtTemplateFolder">Template folder:</label><br/>
								<input type="text" name="txtTemplateFolder" id="txtTemplateFolder" value="<? echo settings("domain", "templatefolder"); ?>"/>
							</p>
							<p>
								<label for="chkAlgemenevoorwaarden">Algemene voorwaarden</label>
								<input type="checkbox" name="chkAlgemenevoorwaarden" id="chkAlgemenevoorwaarden" value="algemenevoorwaarden" <? print((settings("startvalues", "algemenevoorwaarden") == "TRUE") ? "checked='checked'" : ""); ?>/>
							</p>
							<p>
								<label for="chkVisibility">Profielen zichtbaar</label>
								<input type="checkbox" name="chkVisibility" id="chkVisibility" value="visibility" <?  print((settings("startvalues", "visibility") == "TRUE") ? "checked='checked'" : ""); ?>/>
							</p>
							<p>
								<label for="txtAnalytics">Google analytics:</label><br/>
								<input type="text" name="txtAnalytics" id="txtAnalytics" value="<? echo settings("analytics"); ?>"/>
							</p>
						</fieldset>
						<fieldset>
							<legend>Debugging</legend>
							<p>
								<label for="chkShowwarnings">Show warnings</label>
								<input type="checkbox" name="chkShowwarnings" id="chkShowwarnings" value="showwarnings" <? print((settings("debugging", "showwarnings") == "TRUE") ? "checked='checked'" : ""); ?>/>
							</p>
							<p>
								<label for="chkDemo">Demo</label>
								<input type="checkbox" name="chkDemo" id="chkDemo" value="demo" <? print((settings("debugging", "demo") == "TRUE") ? "checked='checked'" : ""); ?>/>
							</p>
						</fieldset>
						<!-- Later -->
						<fieldset>
							<legend>Verzekeringen</legend>
							<p>
								<input style="width: 350px;" type="text" name="txtV1" id="txtV1" value="<? echo settings("verzekeringen", "1"); ?>"/>
							</p>
							<p>
								<input style="width: 350px;" type="text" name="txtV2" id="txtV2" value="<? echo settings("verzekeringen", "2"); ?>"/>
							</p>
						</fieldset>
						<fieldset>
							<legend>Tijdzone en lokatie</legend>
							<p>
								<label for="lstTimezone">Tijdzone:</label><br/>
								<select id="lstTimezone" name="lstTimezone">
								<?
									$zones = timezone_identifiers_list();
									foreach ($zones as $zone) {
										$place = explode("/", $zone);
										if (settings("date", "timezone") == $zone) {
											print("<option value='" . $zone . "' selected='selected'>" . $place[1] . "</option>");
										}
										else {
											if ($zone != "UTC") {
												print("<option value='" . $zone . "'>" . $place[1] . "</option>");
											}
										}
									}
								?>
								</select>
							</p>
							<p>
								<label for="txtLokatie">Standaard lokatie:</label><br/>
								<input type="text" name="txtLokatie" id="txtLokatie" value="<? echo coordinatesToAddress(settings("geo", "latitude"), settings("geo", "longitude")); ?>"/>
							</p>
						</fieldset>
						<fieldset>
							<legend>Credits</legend>
							<p>
								<label for="txtStart">Start:</label></br>
								<input type="number" name="txtStart" id="txtStart" min="0" value="<? echo settings("startvalues", "credits"); ?>"/>
							</p>
							<p>
								<label for="txtMin">Min:</label><br/>
								<input type="number" name="txtMin" id="txtMin" min="0" value="<? echo settings("credits", "min"); ?>"/>
							</p>
							<p>
								<label for="txtMax">Max:</label><br/>
								<input type="number" name="txtMax" id="txtMax" min="0" value="<? echo settings("credits", "max"); ?>"/>
							</p>
							<p>
								<label for="txtEenheid">Eenheid:</label><br/>
								<input type="text" name="txtEenheid" id="txtEenheid" value="<? echo settings("credits", "name", "1"); ?>"/>
							</p>
							<p>
								<label for="txtMeervoud">Meervoud:</label><br/>
								<input type="text" name="txtMeervoud" id="txtMeervoud" value="<? echo settings("credits", "name", "x"); ?>"/>
							</p>
							<p>
								<label for="txtOverdracht">Overdracht:</label><br/>
								<input type="text" name="txtOverdracht" id="txtOverdracht" value="<? echo settings("credits", "name", "overdracht"); ?>"/>
							</p>
						</fieldset>
						<fieldset>
							<legend>Mail</legend>
							<p>
								<label for="chkSMTP">SMTP</label>
								<input type="checkbox" name="chkSMTP" id="chkSMTP" value="smtp" <? print((settings("mail", "smtp") == "TRUE") ? "checked='checked'" : ""); ?>/>
							</p>
							<p>
								<label for="txtHost">Host:</label><br/>
								<input type="text" name="txtHost" id="txtHost" value="<? echo settings("mail", "Host"); ?>"/>
							</p>
							<p>
								<label for="chkAuth">Authentication</label>
								<input type="checkbox" name="chkAuth" id="chkAuth" value="SMTPAuth" <? print((settings("mail", "SMTPAuth") == "TRUE") ? "checked='checked'" : ""); ?>/>
							</p>
							<p>
								<label for="txtSecure">Secure:</label><br/>
								<input type="text" name="txtSecure" id="txtSecure" value="<? echo settings("mail", "SMTPSecure"); ?>"/>
							</p>
							<p>
								<label for="txtPort">Port:</label><br/>
								<input type="number" name="txtPort" id="txtPort" min="0" max="65535" value="<? echo settings("mail", "Port"); ?>"/>
							</p>
							<p>
								<label for="txtUsername">Username:</label><br/>
								<input type="text" name="txtUsername" id="txtUsername" value="<? echo settings("mail", "Username"); ?>"/>
							</p>
							<p>
								<label for="txtPasswd">Password:</label><br/>
								<input type="password" name="txtPasswd" id="txtPasswd" value="<? echo settings("mail", "Password"); ?>"/>
							</p>
						</fieldset>
						<fieldset>
							<legend>Facebook loginapp</legend>
							<p>
								<label for="txtFbId">Id:</label><br/>
								<input type="text" name="txtFbId" id="txtFbId" value="<? echo settings("facebook", "loginapp", "id"); ?>"/>
							</p>
							<p>
								<label for="txtFbSecret">Secret:</label><br/>
								<input type="text" name="txtFbSecret" id="txtFbSecret" value="<? echo settings("facebook", "loginapp", "secret"); ?>"/>
							</p>
						</fieldset>
						<input type="submit" name="btnOpslaan" value="Opslaan" class="btn btn-default btn-save"/>
					</form>
				</div>
			</div>
			<? echo $oPage->endTabs(); ?>
		</div>
	<div class="footer">
        	<? echo $oPage->footer(); ?>
        </div>
	<script>
		function enableDisableFields(state, fields) {
			var lenFields = fields.length;
			for (var i = 0; i < lenFields; i++) {
				if (!state) {
					fields[i].style.background = "#dadada";
				}
				else {
					fields[i].style.background = "#ffffff";
				}
				if (fields[i].name == "chkAuth") {
					fields[i].disabled = !state;
				}
				else {
					fields[i].readOnly = !state;
				}
			}
		}
		var chkSMTP = document.getElementById("chkSMTP");
		var txtHost = document.getElementById("txtHost");
		var chkAuth = document.getElementById("chkAuth");
		var txtSecure = document.getElementById("txtSecure");
		var txtPort = document.getElementById("txtPort");
		var txtUsername = document.getElementById("txtUsername");
		var txtPasswd = document.getElementById("txtPasswd");
		var fields = [txtHost, chkAuth, txtSecure,
			txtPort, txtUsername, txtPasswd];
		enableDisableFields(chkSMTP.checked, fields);
		chkSMTP.addEventListener("click", function() {
			enableDisableFields(chkSMTP.checked, fields);
		});
	</script>
	</body>
</html>