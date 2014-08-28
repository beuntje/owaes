<?
	include "inc.default.php"; // should be included in EVERY file 
	$oSecurity = new security(TRUE); 
	$oLog = new log("page visit", array("url" => $oPage->filename())); 
	
	//$oPage->addJS("http://code.jquery.com/ui/1.10.3/jquery-ui.js");
	$oPage->addJS("https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true");
	$oPage->addJS("script/owaesadd.js");
	//$oPage->addJS("script/mugifly-jquery-simple-datetimepicker-702f729/jquery.simple-dtpicker.js"); 
	//$oPage->addCSS("http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"); 
	//$oPage->addCSS("script/mugifly-jquery-simple-datetimepicker-702f729/jquery.simple-dtpicker.css"); 
	 
	
	$iID = isset($_GET["edit"])?intval($_GET["edit"]):0;
	$oOwaesItem = new owaesitem($iID);
	
	if ($oOwaesItem->author()->id() != me()) {
		if (!admin()) {
			$oSecurity->doLogout(); 
			exit(); 
		}
	}
	
	if ($oOwaesItem->id() != 0) {
		$strType = $oOwaesItem->type()->key();  
	} else {
		$strType = (isset($_GET["t"]) ? $_GET["t"] : ""); 
	} 
	 
	if (isset($_POST["owaesadd"])) { 
		
		$iGroup = intval($_POST["group"]); 
		if ($iGroup != 0){
			$oGroup = new group($iGroup); 
			if ($oGroup->userrights()->owaesadd()) $oOwaesItem->group($iGroup);
			// TODO : BOVENSTAANDE KAN FOUT OPLEVEREN ALS ADMIN GROUPSITEM AANPAST ! 
		} else {
			$oOwaesItem->group(0); 
		} 
		
		$oOwaesItem->title($_POST["title"]); 
		$oOwaesItem->body($_POST["description"]); 
	//	switch($_POST["locationOptions"]) {
	//		case "free": 
	//			$oOwaesItem->location("", 0, 0); 
	//			break; 
	//		default: // "fixed"	
				$oOwaesItem->location($_POST["locationfixed"], $_POST["locationlat"], $_POST["locationlong"]); 
	//	}
		
		//$oOwaesItem->timingtype($_POST["timing"]); 
		
		foreach ($oOwaesItem->data() as $iDate) {
			$oOwaesItem->removeMoment($iDate);
		}  
		if (isset($_POST["data"])) foreach ($_POST["data"] as $iDatum) { 
			$oOwaesItem->addMoment(ddmmyyyyTOdate($_POST["datum-$iDatum"]), hhmmTOminutes($_POST["start-$iDatum"]), hhmmTOminutes($_POST["tijd-$iDatum"])); 
		}   
		 
		foreach ($oOwaesItem->getTags() as $strTag) {
			$oOwaesItem->removeTag($strTag);
		} 
		if (isset($_POST["tag"])) foreach ($_POST["tag"] as $strTag) { 
			$oOwaesItem->addTag($strTag);  
		}   
		
		$oOwaesItem->timing($_POST["timingtime"]); 
		$oOwaesItem->physical($_POST["physical"]); 
		$oOwaesItem->mental($_POST["mental"]); 
		$oOwaesItem->emotional($_POST["emotional"]); 
		$oOwaesItem->social($_POST["social"]); 
		$oOwaesItem->credits($_POST["credits"]); 
		$oOwaesItem->type($_POST["type"]); 
		
		foreach ($oOwaesItem->subscriptions() as $iUser=>$oValue) {
			switch ($oValue->state()) {
				case SUBSCRIBE_CONFIRMED:  
					$oConversation = new conversation($iUser); 
					$oConversation->add("Er werden aanpassingen doorgevoerd in deze opdracht. Gelieve deze na te kijken. ", $oOwaesItem->id() );  
					break; 
			} 
		} 
			
		$oOwaesItem->update();   
		//switch($oOwaesItem->task())  {
		//	case TRUE: 
				header("Location: index.php?t=" . $oOwaesItem->type()->key());  
				exit(); 
		//		break; 	
		//	case FALSE:  
		//		header("Location: index.php?t=market");  
		//		exit(); 
		//		break; 	
		//} 
		
	} 
	  
	list($iLat, $iLong) = $oOwaesItem->LatLong(); 

	$oPage->tab("market.$strType");  
	 
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
					echo ("var startpos = new google.maps.LatLng(" . ($iLat) . ", " . ($iLong) . ");");  
				} else {
					echo ("var startpos = new google.maps.LatLng(" . settings("geo", "latitude") . ", " . settings("geo", "longtitude") . ");"); 
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
						echo (" oPos = new google.maps.LatLng(" . $iLat . ", " . $iLong . "); 
						setMarker(oPos); "); 
					} 
				?> 
			}
			google.maps.event.addDomListener(window, 'load', initialize);
			

		</script> 
          <style>
								
.invoer {
  display: block;
  width: 100%; 
  padding: 10px 18px;
  font-size: 15px;
  line-height: 1.42857143;
  color: #333333;
  background-color: #ffffff;
  background-image: none;
  border: 1px solid #cccccc;
  border-radius: 0;
  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
  -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
  transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
}
.invoer.actief {
  outline: none;
  border: 1px solid #f9f9f9;
  box-shadow: 0 0 5px #ffcc00;
  background: white; 
 } 
 .invoer input { outline: none;
  border:none !important;
  box-shadow:none !important; }
  
  div#tags span.tag {border: 1px solid #CCC; display: inline-block; margin-left: 10px; padding: 2px 5px; margin-bottom: 2px; -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; }
div#tags span.tag a {font-size: 10px; color: white; padding: 0 5px 2px 5px; vertical-align: top; font-weight: bold; background: #efefef; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; text-decoration: none; margin-left: 4px;  }
div#tags span.tag a:hover {background: #333; }
div#tags input {border: 0; display: inline; width: 400px; padding-left: 10px; }
div#tags ul.tags {position: absolute; z-index: 999; background: white; border: 1px solid #efefef; width: 455px; max-height: 100px; overflow: auto; }
div#tags ul.tags li {padding: 3px 10px; cursor: pointer; }
div#tags ul.tags li:hover {background: #efefef; }
input.time {width: 100%; display: block; }
  
								</style>
    </head>
    <body id="owaesadd">               
        <? echo $oPage->startTabs(); ?> 
    	<div class="body content content-market content-market-add container">
        	
            	<div class="row">
					<? /*echo $oSecurity->me()->html("templates/leftuserprofile.html"); */
                    echo $oSecurity->me()->html("templates/user.html");
                    ?>
                </div>
                <div class="container sidecenterright"> 
                 
                <div class="errors"></div>
                
                <form method="post" class="form-horizontal" id="frmowaesadd" name="frmowaesadd">
                	<?  
						$arOwaesTypes = owaesType()->getAllTypes();
						
						if (isset($arOwaesTypes[$strType])) {
							echo ("<legend class=\"aanbod\">Aanbod toevoegen: <strong>" . $arOwaesTypes[$strType] . "</strong> <small>(credits " . ((owaesType($strType)->direction()==DIRECTION_EARN) ? "verdienen" : "uitgeven") . ")</small></legend>"); 
						} else {
							echo ("<legend class=\"aanbod\">Aanbod toevoegen</legend>"); 
						}  
					?>
                          
                    <ul class="nav nav-tabs" id="tabsAdd">
                      <li class="active"><a href="#algemeen" data-toggle="tab" class="algemeen">Algemeen</a></li>
                      <li><a href="#tijdlocatie" data-toggle="tab" class="tijdlocatie" id="tijdlocatietab" >Tijd en Locatie</a></li>
                      <li><a href="#compensatie" data-toggle="tab" class="compensatie">Compensatie</a></li>
                    </ul>
                    
                    <div class="tab-content">
                      <div class="tab-pane fade in active" id="algemeen">
                      <dl id="algemeen">
                            	<? 
									$arGroups = $oOwaesItem->author()->groups();  
									$arAddGroups = array(); 
									
									// groups van de bestaande user
									foreach ($arGroups as $oGroup) { 
										if ($oGroup->userrights()->owaesadd()) $arAddGroups[] = $oGroup; 
									}
									
									// groups van mij
									foreach (user(me())->groups() as $oGroup) { 
										if (!in_array($oGroup, $arAddGroups)) if ($oGroup->userrights()->owaesadd()) $arAddGroups[] = $oGroup; 
									} 
									
									if (count($arAddGroups) > 0) {
										echo ('<div class="form-group">
												<label for="group" class="col-lg-12">Aanbieder</label>
												<div class="col-lg-12">
													<select name="group" id="group" class="required form-control">'); 
													echo ("<option value=\"0\" style=\"border-bottom: 1px dotted #000; \">" . $oOwaesItem->author()->getName() . "</option>");  
													foreach ($arAddGroups as $oGroup) {
														if ($oOwaesItem->group() && $oOwaesItem->group()->id()==$oGroup->id()) { 
															echo ("<option selected=\"selected\" value=\"" . $oGroup->id() . "\">" . $oGroup->naam() . "</option>"); 
														} else {
															echo ("<option value=\"" . $oGroup->id() . "\">" . $oGroup->naam() . "</option>"); 
														}
													}
													echo ('</select>
												</div>
											</div>');  
									} else {
										echo ("<input type=\"hidden\" name=\"group\" value=\"0\" />"); 	
									}
									 
									
								?>  
                                <div class="form-group">
									<label for="title" class="col-lg-12">Titel</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="title" id="title" class="required form-control" placeholder="Titel voor uw aanbod" value="<? echo inputfield($oOwaesItem->title()); ?>" />
                                    </div>
                                    <div class="col-lg-2"> 
                                        <dd>
                                        <select class="form-control aanbod" name="type"> 
                                            <?
                                                foreach($arOwaesTypes as $strKey=>$strTitle) {
                                                    $strSelected = ($strType==$strKey) ? "selected=\"selected\"" : ""; 
                                                    echo "<option value='$strKey' $strSelected>"; 
                                                    echo $strTitle;
                                                    //echo (owaestype($strKey)->direction() == DIRECTION_EARN) ? ": dit zal me credits opleveren" : ": dit zal me credits kosten"; 
                                                    echo "</option>"; 	
                                                }
                                            ?> 
                                        </select>
                                        </dd>  
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="description" class="col-lg-12">Omschrijving</label>
                                    <div class="col-lg-12">
                                        <textarea name="description" id="description" class="required form-control" placeholder="Omschrijving"><? echo textarea($oOwaesItem->body()); ?></textarea>
                                    </div>
                                </div>
                                 
                                <div class="form-group">
                                <label for="types" class="col-lg-12">Kernwoorden</label>
                                 
                              
                                <div class="col-lg-12"><div class="invoer" id="tags">
                                <?
									$iTagCount = 0;  
                                	foreach ($oOwaesItem->getTags() as $strTag) {
										$strKey = "tag" . ++$iTagCount; 
										echo ("<span class=\"tag\" id=\"$strKey\"><span>$strTag</span><a title=\"verwijderen\" href=\"#\" rel=\"$strKey\">x</a><input type=\"hidden\" name=\"tag[]\" value=\"$strTag\"></span>"); 	
									}
								?><input type="text" name="tag[]" id="tag" class="tag" /> 
                                </div></div> 
                                </div> 
                                
                                <div class="form-group"> 
                                    <div class="col-lg-12"> 
                                        <a href="#tijdlocatie" class="tabchange">volgende</a>  
                                    </div>
                                </div>
                            </dl>
                      </div>
                      <div class="tab-pane fade" id="tijdlocatie">  
                      <!-- <legend>Tijd en locatie</legend> -->
                       <div class="row">
                      		 <div class="col-lg-12">
                         	    <h4 class="tijd">Tijd</h4>
                             </div>
                               <div class="col-lg-3"> 
                                    <div id="calendar"></div>
                                </div> 
                                <div class="col-lg-9">
                                    <div class="errorsTime"></div>
                                     
                                   	<? 
										  
										$arDatums = array(); 
										foreach ($oOwaesItem->data() as $iDate) {
											$oMoment = $oOwaesItem->getMoment($iDate);  
											$arDatums[($iDate == 0) ? "" : str_date($iDate, "d/m/Y")] = array(
												"start" => minutesTOhhmm($oMoment["start"]),
												"tijd" => ($oMoment["tijd"] == 0) ? "" : $oMoment["tijd"]/60,
											); 
										}
										echo ('<script>  
											var arDatums = ' . (count($arDatums) > 0 ? json_encode($arDatums) : "{}") . '; 
										</script>'); 
									?><div id="timers">
                                    
                                     
                                   
                                   </div>
                                </div>
                            </div> <!--/row--> 
                            <div class="row">
                                <div class="col-lg-2"> 
                                    <h4 class="locatie">Locatie</h4>
                                </div>
                                <div class="col-lg-7">
                                    <div class="errorsTime"></div>
                                    <div id="dates"></div>
                                    <div id="datesperiods"></div>
                                </div>
                                <div class="col-lg-3">
                                    <div id="calendar"></div>
                                </div>
                            </div> <!--/row-->
                            
                            <div class="row">
                                <div class="col-lg-12">
                                <dd class="locationfixed">
                                    <input type="text" name="locationfixed" class="locationfixed_required form-control" id="location" placeholder="Locatie..." value="<? echo inputfield($oOwaesItem->location()); ?>" />
                                	<input type="hidden" name="locationlat" id="locationlat" value="<? echo $iLat; ?>" />
                                	<input type="hidden" name="locationlong" id="locationlong" value="<? echo $iLong; ?>" />
                                </dd>
                                <dd class="locationfixed"><div id="map-canvas" style="height: 300px; "></div></dd> 
                                </div>
                            </div> <!--/row-->
                            

                            <div class="form-group"> 
                                <div class="col-lg-12"> 
                                    <a href="#algemeen" class="tabchange">vorige</a>
                                    <a href="#compensatie" class="tabchange">volgende</a>  
                                </div>
                            </div>
                             
                      </div>
                      <div class="tab-pane fade" id="compensatie">
                      <!-- <legend>Compensatie</legend> -->
                      
                           
                           <div class="row row-credits">
                           <label for="credits" class="col-lg-2">Credits</label>
                           <div class="col-lg-10">
                            <input type="text" min="0" max="1000" name="credits" id="creditsfield" class="auto border" value="<? echo $oOwaesItem->credits(); ?>" />
                           </div>
                           </div>
                           
                           <div class="row row-sociaal">
                           <label for="social" class="col-lg-2">Sociaal</label>
                           <div class="col-lg-10">
                            <input type="range" min="0" max="100" name="social" id="socialslide" value="<? echo $oOwaesItem->social(); ?>" class="development"  />
                           </div>
                           </div>
                           
                           <div class="row row-fysiek">
                           <label for="physical" class="col-lg-2">Fysiek</label>
                           <div class="col-lg-10">
                            <input type="range" min="0" max="100" name="physical" id="physicalslide" value="<? echo $oOwaesItem->physical(); ?>" class="development" />
                           </div>
                           </div>
                           
                           <div class="row row-kennis">
                           <label for="mental" class="col-lg-2">Kennis</label>
                           <div class="col-lg-10">
                            <input type="range" min="0" max="100" name="mental" id="mentalslide" value="<? echo $oOwaesItem->mental(); ?>" class="development"  />
                           </div>
                           </div>
                           
                           <div class="row row-welzijn">
                           <label for="emotional" class="col-lg-2">Welzijn</label>
                           <div class="col-lg-10">
                            <input type="range" min="0" max="100" name="emotional" id="emotionalslide" value="<? echo $oOwaesItem->emotional(); ?>" class="development"  />
                           </div>
                           </div>
                           
                            <div class="form-group"> 
                                <div class="col-lg-12"> 
                                    <a href="#tijdlocatie" class="tabchange">vorige</a> 
                                </div>
                            </div>
                           
                            <div class="row row-buttons">
                                <div class="form-group col-lg-12">
                                    <input type="submit" name="owaesadd" id="owaesadd" class="auto border btn btn-default pull-right" value="opslaan" />
                                </div>
                            </div>
                      </div>
                    </div>

                        <ul id="addfouten">
                        
                        </ul>
                    </form>
            	</div> 
			<? echo $oPage->endTabs(); ?>
        </div>
        <div class="footer">
        	<? echo $oPage->footer(); ?>
        </div>
    </body>
</html>