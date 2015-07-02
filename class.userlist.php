<?php
	class userlist { /*
			wordt gebruikt om een lijst te genereren van gebruikers, bv. op gebruikerspagina of groepspagina. 
		*/
		private $arUserlist = NULL; 
		private $arSQLwhere = array(); 
		private $arSQLjoin = array(); 
		private $arOrder= array(); 
		 
		public function userlist() { 
			$this->arOrder[] = "u.lastname"; 
			$this->arSQLwhere["actief"] = " actief = 1 ";  
		}
		
		public function filter($strType, $value = NULL) {
			switch(strtolower($strType)) {
				case "visible":
					$iValue = is_null($value) ? "1" : ($value?1:0); 
					$this->arSQLwhere["visible"] = " visible = $iValue "; 
					break; 
				case "friends":
					$iSource = is_null($value) ? me() : $value; 
					$this->arSQLjoin["friends"] = " inner join tblFriends f on (f.user = " . $iSource . " and friend = u.id and confirmed = 1) "; 
					break; 
				case "followed":
					$iSource = is_null($value) ? me() : $value;  // user // follower // startdate
					$this->arSQLjoin["followed"] = " inner join tblFollowers fd on (fd.user = " . $iSource . " and fd.followed = u.id) "; 
					break; 
				case "followers":
					$iSource = is_null($value) ? me() : $value;  // user // follower // startdate
					$this->arSQLjoin["followers"] = " inner join tblFollowers fw on (fw.user = u.id and fw.followed = " . $iSource . ") "; 
					break; 
				case "admin":
					$iValue = is_null($value) ? "1" : ($value?1:0); 
					$this->arSQLwhere["admin"] = "u.admin = $iValue"; 
					break; 
				case "frozen":
					$iValue = is_null($value) ? "0" : ($value?0:1); 
					$this->arSQLwhere["actief"] = " actief = $iValue "; 
					break; 
				case "algemenevoorwaarden":
					$iValue = is_null($value) ? "1" : ($value?1:0); 
					$this->arSQLwhere["algemenevoorwaarden"] = " algemenevoorwaarden = $iValue "; 
					break; 
			}
		}
		
		public function group($iGroup, $bMustBeConfirmed = TRUE) { // enkel de gebruikers die in een bepaalde groep zitten 
			$arWhere = array(
				"gu.user = u.id", 
				"gu.groep = " . intval($iGroup), 
			); 
			if ($bMustBeConfirmed) $arWhere[]  = "gu.confirmed = 1"; 
			$this->arSQLjoin["group"] = "inner join tblGroupUsers gu on " . implode(" and ", $arWhere);  
		}
		

		public function search($strSearch) {  
			$arSearchQRY = array();  
			$arFields = array(
				"u.alias", 
				"u.lastname", 
				"u.firstname", 
				"u.description", 
			); 
			preg_match_all("/[a-zA-Z0-9_-]+/", $strSearch, $arMatches, PREG_SET_ORDER);      
			if(count($arMatches)>0) {
				foreach ($arMatches as $arSearch) { 
					$arFieldSearches = array(); 
					foreach ($arFields as $strField) $arFieldSearches[] = "$strField like '%" . $arSearch[0] . "%'";  
					$arSearchQRY[] = "(" . implode(" or ", $arFieldSearches) . ")"; 
				} 
				$this->arSQLwhere["search"] = implode(" and ", $arSearchQRY);  
			}
		}
		
		public function getList() { // returns een array van users (class.user)
			if (is_null($this->arUserlist)) {
				$arUserlist = array();
				$strSQL = "select u.* from tblUsers u "; 
				foreach($this->arSQLjoin as $strKey=>$strVal) { 
					$strSQL .= " $strVal ";
				} 
				if (count($this->arSQLwhere)>0) {
					$strSQL .= " where u.deleted = 0 "; 
					foreach($this->arSQLwhere as $strKey=>$strVal) { 
						$strSQL .= " and (" . $strVal . ")"; 
					} 
				} 
				$strSQL .= " order by " . implode(",", $this->arOrder); 
				$oOWAES = new database($strSQL, true); 
				while ($oOWAES->nextRecord()) { 
					$oUser = user($oOWAES->get("id"));  
					$oUser->load($oOWAES->record()); 
					$oUser->location($oOWAES->get("location"), $oOWAES->get("location_lat"), $oOWAES->get("location_long"));

					array_push ($arUserlist, $oUser);  
				}  
				$this->arUserlist = $arUserlist; 
			}
			return $this->arUserlist;
		}
		 

	}
	
	