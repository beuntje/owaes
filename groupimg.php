<?php 
	$iID = intval($_GET["id"]); 
	$iW = intval(isset($_GET["w"])?$_GET["w"]:0);  
	$iH = intval(isset($_GET["h"])?$_GET["h"]:0); 
	
	$strFilename = "upload/groups/id/" . $iID . ".png"; 
	if (!file_exists($strFilename)) $strFilename = "upload/groups/noprofileimg.png"; 
	
	$oSource = imagecreatefrompng($strFilename);
	$iProp = imagesx($oSource)/imagesy($oSource);
	
	if ($iW==0) $iW = $iH * $iProp; 
	if ($iH==0) $iH = $iW / $iProp; 
	
	$oThumb = imagecreatetruecolor($iW, $iH);
	imagealphablending($oThumb, false);
	imagesavealpha($oThumb, true);
	
	if ($iW/$iH > $iProp) {
		$iX = 0;
		$iY = ($iH - ($iW / $iProp))/2;
		$iH = $iW / $iProp; 
	} elseif ($iW/$iH < $iProp) { 
		$iX = ($iW - ($iH * $iProp))/2;
		$iY = 0; 
		$iW = $iH * $iProp;  
	} else {
		$iX = 0;
		$iY = 0; 
	}
	 
	
	
	imagecopyresampled($oThumb, $oSource, $iX, $iY, 0, 0, $iW, $iH, imagesx($oSource), imagesy($oSource));
	
	header('Content-Type: image/png');
	imagepng($oThumb); 
	
?>