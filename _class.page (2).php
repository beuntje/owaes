<?php 
	class page {   /*
	 $oPage wordt over de hele site geladen als GLOBAL (zit in inc.default.php)
	 TODO: public $iUser moet private worden 
	 */
		private $arMeta = array(); 
		private $strTitle = "OWAES";  // Page title (this value is the default) 
		private $arCSS = array(); 
		private $arJS = array(); 
		private $bLoggedInUser = FALSE; 
		public $iUser = 0; 
		private $strTab = NULL; 
		 
		public function page() {  
			$this->setMeta("author", "HOWEST"); 
			$this->setMeta("description", "OWAES");  
			$this->setMeta("content-language", "NL"); 
			$this->setMeta("content-language", "NL"); 
			$this->addCSS("style/reset-min.css"); 
            $this->addCSS("style/bootstrap.css");
            $this->addCSS("style/bootswatch.css");
			$this->addCSS("style/owaes.css"); 
			$this->addCSS("style/min1100.css", "only screen and (max-width: 1100px)"); 
            $this->addCSS("style/style.css");
            $this->addCSS("style/style2.css");
			$this->addCSS("http://fonts.googleapis.com/css?family=Titillium+Web:400,700"); 
			$this->addJS("http://code.jquery.com/jquery-1.9.1.js");  
            $this->addJS("bootstrap.js");
            $this->addJS("http://masonry.desandro.com/masonry.pkgd.min.js");
            $this->addJS("http://imagesloaded.desandro.com/imagesloaded.pkgd.min.js");
			$this->addJS("owaes.js");  
            $this->addJS("main.js");
			
			switch ($this->filename(FALSE)) {
				case "index.php": 
					if( isset($_GET["t"])) $this->tab("market." . $_GET["t"]);  
					break; 
				case "users.php": 
					$this->tab("users");  
					break; 
				case "settings.php":  
					if ($this->bLoggedInUser) $this->tab("settings");  
					break; 
				case "conversation.php":  
					$this->tab("messages");  
					break; 
			} 
		} 
		
		public function getHeader() { // returns all setted inside-<head> information
			global $arConfig;   
			$strHTML = "\n\t\t<title>" . $this->strTitle . "</title>";
			$strHTML .= "\n\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />"; 
			$strHTML .= "\n\t\t<link rel=\"shortcut icon\" href=\"" . fixPath("favicon.png") . "\" type=\"image/png\" />";  
			foreach($this->arMeta as $strKey=>$strVal) {
				$strHTML .= "\n\t\t<meta name=\"" . $strKey . "\" content=\"" . $strVal . "\" />"; 
			} 
			foreach($this->arCSS as $strFile=>$arData) {
				$strHTML .= "\n\t\t<link rel=\"stylesheet\" href=\"" . $strFile . "\" type=\"text/css\" media=\"" . $arData["media"] . "\" />"; 
			} 
			foreach($this->arJS as $strFile) {
				$strHTML .= "\n\t\t<script src=\"" . $strFile . "\"></script>"; 
			} 
			$strHTML .= "\n\t\t<script>
				var strRoot = \"" . $arConfig["domain"]["root"] . "\"; 
			</script>"; 
			
			
			return $strHTML; 
		}
		
		public function setTitle($strTitle) { // overrides default title
			$this->strTitle = $strTitle; 
		}
		
		public function setMeta($strName, $strContent) { // adds new meta-tag or changes existing meta-tag
			$this->arMeta[$strName] = $strContent; 
		}
		public function addCSS($strFile, $strMedia = "all") { // nieuwe CSS-file toevoegen (relatief pad)
			$strFile = fixPath($strFile);  
			if (!isset($this->arCSS[$strFile]))  $this->arCSS[$strFile] = array(
				"media" => $strMedia, 
			); 
		}
		public function addJS($strFile) { // nieuwe javascript-file toevoegen (relatief pad)
			$strFile = fixPath($strFile); 
			if (!in_array($strFile, $this->arJS)) array_push($this->arJS, $strFile); 
		}
		
		public function removeMeta($strName){ // removes setted meta-tag
			unset ($this->arMeta[$strName]); 
		}
		
		public function tab($strTab = NULL) { // get / sets current tab ("opdrachten"/"marktplaats"/"gebruikers"/..)
			if (!is_null($strTab)) $this->strTab = $strTab;  
			return $this->strTab; 
		}
		
		public function startTabs() { // returns HTML-code voor tabbladen "opdrachten", "marktplaats", "profiel" 
			global $oSecurity; 
			$arTabs = array( 
				"home" => array(
					"title" => "home", 
					"url" => "main.php", 
					"classes" => array("menu-item", "home"), 
				) 
			); 
			$oOwaesTypes = new owaestype(); 
			foreach ($oOwaesTypes->getAllTypes() as $strKey=>$strTitle) {
				$arTabs["market." . $strKey] = array(
					"title" => $strTitle, 
					"url" => "index.php?t=" . $strKey, 
					"classes" => array("menu-item", $strKey), 
				);
			}
			/*$arTabs["users"] = array(
					"title" => "gebruikers", 
					"url" => "users.php", 
					"classes" => array("users", "extratab"), 
				);*/
			if ($this->bLoggedInUser) {
				/*$oInbox = new inbox();
				if (count($oInbox->discussions()) > 0) {
					$arTabs["messages"] = array(
						"title" => "berichten", 
						"url" => "#", 
						"classes" => array("extratab", "mailbox"), 
						"sub" => array(),
					); 
					foreach ($oInbox->discussions() as $iKey=>$arUser) {
						if ($arUser["unread"] > 0) {
							$arTabs["messages"]["sub"][$arUser["names"] . " (" . $arUser["unread"] . ")"] = array("conversation.php?users=" . $arUser["ids"], "conversation unread"); 
						} else {
							$arTabs["messages"]["sub"][$arUser["names"]] = array("conversation.php?users=" . $arUser["ids"], "conversation"); 
						}
					}
				} 
                */
                $arTabs["lijsten"] = array (
                    "title" => "lijsten", 
					"url" => "#", 
					"classes" => array("dropdown-toggle", "lijsten"), 
					"sub" => array(),
                );
                
                $arTabs["lijsten"]["sub"]["gebruikers"] = array("users.php", "gebruikers");
				$arTabs["lijsten"]["sub"]["badges"] = array("#", "badges");
                
                $arTabs["account"] = array (
                    "title" => "account", 
					"url" => "#", 
					"classes" => array("dropdown-toggle", "account"), 
					"sub" => array(),
                );
                $arTabs["account"]["sub"]["profiel"] = array("profile.php", "profiel");	
				$arTabs["account"]["sub"]["berichten"] = array("conversations.php", "berichten");	
				$arTabs["account"]["sub"]["instellingen"] = array("settings.php", "instellingen");
				$arTabs["account"]["sub"]["afmelden"] = array("logout.php", "afmelden");	
                
				/*$arTabs["settings"] = array(
					"title" => "instellingen", 
					"url" => "settings.php", 
					"classes" => array("extratab", "settings"), 
					"sub" => array(),
				);
                
				foreach (user(me())->groups() as $oGroep) {
					$arTabs["settings"]["sub"][$oGroep->naam()] = array("group.php?id=" . $oGroep->id(), "groep"); 
				}
				
				if ($oSecurity->admin()) $arTabs["settings"]["sub"]["admin"] = array("admin.php", "admin"); 
				
				$arTabs["settings"]["sub"]["instellingen"] = array("settings.php", "settings");
				$arTabs["settings"]["sub"]["profiel"] = array("profile.php", "profile");
				$arTabs["settings"]["sub"]["uitloggen"] = array("logout.php", "login");
                */
			} else {
				$arTabs["login"] = array(
					"url" => "login.php?p=" . urlencode($this->filename()), 
					"classes" => array("menu-item", "login"), 
				); 
			}
			if (isset($arTabs[$this->tab()]["classes"])) $arTabs[$this->tab()]["classes"][] = "active";
            console("class.page.php", "current tab: " . $this->tab());
            
			$strHTML = "<div class=\"navbar-collapse collapse\" id=\"navbar-main\"><ul class=\"nav navbar-nav navbar-right\">"; 
			foreach ($arTabs as $strKey => $arDetails) {
				$strTitel = isset($arDetails["title"]) ? $arDetails["title"] : $strKey; 
                if (!isset($arDetails["sub"])){
                    $strHTML .= "<li>";
				    $strHTML .= "<a href=\"" . fixPath($arDetails["url"]) . "\" class=\"" . implode(" ", $arDetails["classes"]) . "\">";
                    $strHTML .= "<span class=\"icon-" . $arDetails["title"] . "\"></span>";
                    $strHTML .= "<span class=\"title\">$strTitel</span></a>";
                    $strHTML .= "</li>";
                }
                else{
                    $strHTML .= "<li class=\"dropdown\">";
                    $strHTML .= "<a href=\"" . fixPath($arDetails["url"]) . "\" class=\"" . implode(" ", $arDetails["classes"]) . "\" data-toggle=\"dropdown\">";
                    $strHTML .= "<span class=\"icon-" . $arDetails["title"] . "\"></span>";
                    $strHTML .= "<span class=\"title\">$strTitel</span> <span class=\"caret\"></span></a>";
                    
                    $strHTML .= "<ul class=\"dropdown-menu\">"; 
					foreach ($arDetails["sub"] as $strSubTitel => $arSubDetails) {
						$strHTML .= "<li><a href=\"" . fixPath($arSubDetails[0]) . "\" class=\"" . $arSubDetails[1] . "\"><span class=\"icon-" . $arSubDetails[1] . "\"></span><span>$strSubTitel</span></a></li>";
					}
					$strHTML .= "</ul>"; 
                    $strHTML .= "</li>";
                }
				
				
			}
			$strHTML .= "</ul></div>"; 
			//$strHTML .= "<div class=\"clock\">" . clock() . "</div>";   
			/*$strHTML .= "<form class=\"search\" action=\"" . fixPath("search.php") . "\" method=\"get\">
							<input class=\"searchfield\" type=\"text\" name=\"q\" " . (isset($_GET["q"])?("value=\"" . inputfield($_GET["q"]) . "\""):"") . " />
							<input class=\"searchbutton\" type=\"submit\" value=\"zoeken\" />
						</form>";   */
            $strHTML .= "<ul class=\"popupmessages\"></ul>";   
			if (!$this->bLoggedInUser) { 
				$strHTML .= "<div class=\"loginbar\">
								Log in:  
								<form action=\"" . fixPath("login.php") . "\" method=\"post\">
								<input type=\"hidden\" name=\"from\" id=\"from\" value=\"" . $this->filename(TRUE) . "\" />
								<input type=\"text\" name=\"username\" id=\"username\" />
								<input type=\"password\" name=\"pass\" id=\"pass\" />
								<input type=\"submit\" name=\"dologin\" value=\"inloggen\" />
								</form>
								of <a href=\"" . fixPath("login.php?p=" . urlencode($this->filename())) . "\">registreer</a>
							</div>";
			} 
			//$strHTML .= "<div id=\"ADMIN\">
			//					<ul><a href=\"#\" rel=\"SQL\">show/hide SQL</a></ul>
			//				</div>";
			return $strHTML; 
		}
		
		public function endTabs() { // returns closing tags for function startTabs()
			return ""; 
		}
		
		public function filename($bQuery = TRUE) { // returns filename of current script ($bQuerye: enkel filename of ook met querystring?)
			$arFN = explode("/", $_SERVER['SCRIPT_FILENAME']); 
			$strFN = $arFN[count($arFN)-1]; 
			if ($bQuery) {
				$arQRY = array(); 
				foreach ($_GET as  $strKey=>$strVal) {
					array_push($arQRY, $strKey . "=" . urlencode($strVal)); 
				} 
				if (count($arQRY)>0) $strFN .= "?" . implode("&", $arQRY); 
			}
			return $strFN;
		}
		
		public function loggedIn($bLoggedIn = TRUE) { // SETS logged in (TODO: moet dit niet in security?...)
			$this->bLoggedInUser = $bLoggedIn;
		}
		
		public function isLoggedIn() { // GETS logged in (TODO: moet dit niet in security?...)
			return $this->bLoggedInUser;
		}
		
		public function footer(){ // footer tekst 
			return "<section class='block block-block contextual-links-region clearfix'>
                        <div class='container'>
	                        <div class='yellowPart'><span>OWAES &ndash; Online Werk Activatie EcoSysteem &ndash; is een <a href='http://www.esf-agentschap.be/' tabindex='-1'>ESF</a> ondersteund onderzoeksproject onder leiding van Howest.</span></div>
	                        <div class='whitePart'>
                            <img src='img/footer/pub_leeuw.png' />
                            <img src='img/footer/logo_eu_.png' />
                            <a href='http://www.esf-agentschap.be' tabindex='-1' target='_blank'><img class='esfLogoFooter' src='img/footer/esfLogo.png' /></a>
                            </div>
                        </div>
                     </section>";
		
		}
		 
		
	}
	 
?>