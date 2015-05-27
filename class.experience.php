<?php  
	class experience { 
		private $iUser = 0; 
		private $arDetails = array(); 
		private $arTotal = NULL; 
		private $iLevel = NULL;
		private $strKey = NULL; 
		private $bAdd = FALSE;  
		
		public function experience($iUser = NULL) { 
			$this->user( (is_null($iUser)) ? me() : $iUser ); 
		}
		
		public function user($iUser = NULL) { // get / set user 
			if (!is_null($iUser)) {
				$this->iUser = $iUser; 
			} else return user($this->iUser);  
		}
		
		public function sleutel($strKey = NULL, $bAdd = NULL) { /*
		- get / set key (een key is leeg of uniek, gebruik een key als een experience maar 1x gegeven mag worden aan een user: bv. "owaes.30" of "quest.8".
		- als bAdd==true : als item met key bestaat wordt waarde ++
		 */
			if (!is_null($strKey)) $this->strKey = $strKey; 
			if (!is_null($bAdd)) $this->bAdd = $bAdd;   
			return $this->strKey;  
		}
		
		public function detail($strKey, $strValue = NULL) { // get / set detail
			if (!is_null($strValue)) $this->arDetails[$strKey] = $strValue; 
			return (isset($this->arDetails[$strKey])) ? $this->arDetails[$strKey] : NULL; 
		}
		
		public function add($iNumber, $bConfirmed = FALSE) { // experience toevoegen (standaard niet confirmed) 
			$arLevels = settings("levels"); 
			$iLevel = $this->level(); 
			$iMultiplier = isset($arLevels[$iLevel]["multiplier"]) ? $arLevels[$iLevel]["multiplier"] : 1; 
			$iNumber *= $iMultiplier; 

			$oDB = new database(); 
			 
			$strKey = (is_null($this->sleutel()) ? "" : $this->sleutel()); 
			if ($strKey != "") { 
				$strSQL = "select * from tblExperience where user = '" . $this->iUser . "' and idk = '" . $this->sleutel() . "';"; 
				$oDB->execute($strSQL);  
				if ($oDB->length() > 0) {
					if ($this->bAdd) {
						$strSQL = "update tblExperience set experience = " . ($oDB->get("experience")+$iNumber) . "  where id = " . $oDB->get("id") . " and user = '" . $this->iUser . "' and idk = '" . $this->sleutel() . "'; ";   
						$oDB->execute($strSQL);  
						if (isset($this->arTotal["all"])) $this->arTotal["all"] += $iNumber; 
						if (isset($this->arTotal["confirmed"]) && $bConfirmed) $this->arTotal["confirmed"] += $iNumber; 
					}
					return FALSE; 
				} 
			}
			 
			$strSQL = "insert into tblExperience (idk, user, experience, datum, details, confirmed) values ('" . $strKey . "', '" . $this->iUser . "', '" . $iNumber . "', '" . owaestime() . "', '" . $oDB->escape(json_encode($this->arDetails)) . "', '" . ($bConfirmed?1:0) . "'); ";
			$oDB->execute($strSQL);  
			if (isset($this->arTotal["all"])) $this->arTotal["all"] += $iNumber; 
			if (isset($this->arTotal["confirmed"]) && $bConfirmed) $this->arTotal["confirmed"] += $iNumber; 
			 
			$oExpAction = new action($this->iUser); 
			$oExpAction->type("experience"); 
			$oExpAction->checkID(); 
			if (is_null($oExpAction->tododate())) {
				$oExpAction->tododate(owaestime()); 
				$oExpAction->update();  
			} elseif ($oExpAction->done()) {
				$oExpAction->tododate($oExpAction->donedate()+(12*60*60)); // vanaf 12 uur na laatste show
				$oExpAction->done(FALSE); 
				$oExpAction->update();  
			}
		}
		
		public function level($bShowNotConfirmed = FALSE) { // returns huidige level (of eventueel volgende met parameter TRUE)
			$iExp = $this->total($bShowNotConfirmed);  
			$this->iLevel = 0; 
			foreach (settings("levels") as $iLevel=>$arSettings) {
				if (($iExp >= $arSettings["threshold"]) && ($iLevel > $this->iLevel)) $this->iLevel = $iLevel;  
			} 
			return $this->iLevel; 
		}
		
		public function leveltreshold($bNext = TRUE) {  // returns experience nodig voor volgende level (bNext = false > vorige level)
			$arLevels = settings("levels"); 
			if ($bNext) {
				return (isset($arLevels[$this->level()+1])) ? $arLevels[$this->level()+1]["threshold"] : $this->total(TRUE);  
			} else {
				return (isset($arLevels[$this->level()])) ? $arLevels[$this->level()]["threshold"] : $this->total(TRUE);  
			}
		}
		
		public function confirm() { 
			$oDB = new database(); 
			$strSQL = "update tblExperience set confirmed = 1 where user = '" . $this->iUser . "' and confirmed = 0; ";
			$oDB->execute($strSQL); 
			if (isset($this->arTotal["confirmed"])) { 
				if (isset($this->arTotal["all"])) {
					$this->arTotal["confirmed"] = $this->arTotal["all"]; 
				} else unset($this->arTotal["confirmed"]); 
			}
		}
		
		public function timeline($iDays = 60) {
			$oDB = new database(); 
			$oDB->execute("select * from tblExperience where user = '" . $this->iUser . "' and confirmed = 1 order by datum;"); 
			$arTimeline = array(); 
			$iStart = 0; 
			$iPrev = -1; 
			$dPrev = 0; 
			while ($oDB->nextRecord()) {
				$iStart+=$oDB->get("experience"); 
				if (round($iStart) > $iPrev) {
					if ($oDB->get("datum") > (owaestime()-($iDays*24*60*60))) {
						if (date('d M Y', $dPrev) == date('d M Y', intval($oDB->get("datum")))) array_pop($arTimeline); 
						$arTimeline[] = array(intval($oDB->get("datum")), round($iStart)); 
					}
					$dPrev = intval($oDB->get("datum")); 
					$iPrev = round($iStart); 
				} 
			} 
			if (count($arTimeline) > 0) $arTimeline[] = array(owaestime(), $arTimeline[count($arTimeline)-1][1]); 
			return $arTimeline;
		}
		
		public function total($bShowNotConfirmed = FALSE, $iValue = NULL) { // als parameter bShowNotConfirmed == TRUE > ook punten die nog niet bevestigd werden door gebruiker
			$strKey = $bShowNotConfirmed ? "all" : "confirmed"; 
			if (!is_null($iValue)) $this->arTotal[$strKey] = $iValue; 
			if (!isset($this->arTotal[$strKey])) { 
				$oDB = new database();
				if ($bShowNotConfirmed) { 
					$oDB->execute("select round(sum(experience)) as totaal from tblExperience where user = " . $this->iUser . ";"); 
					if ($oDB->record()) $this->arTotal["all"] = intval($oDB->get("totaal")); 
				} else {

					$arUsers = loadedUsers();  // voert query uit voor alle users die in memory zitten
					if (!in_array($this->iUser, $arUsers)) $arUsers[] = $this->iUser;  
					foreach ($arUsers as $iUser) user($iUser)->experience()->total(FALSE, 0);
					
					$oDB->execute("select user, round(sum(experience)) as totaal from tblExperience where user in (" . implode(",", $arUsers) . ") and confirmed = 1 group by user;"); 
					while ($oDB->nextRecord()) { 
						user($oDB->get("user"))->experience()->total(FALSE, intval($oDB->get("totaal"))); 
						if ($oDB->get("user") == $this->iUser) $this->arTotal[$strKey] = intval($oDB->get("totaal")); 
					} 
				} 
			}  
			return $this->arTotal[$strKey]; 
		}

	} 
	 
	