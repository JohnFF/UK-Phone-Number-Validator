<?php

require_once 'CRM/Core/Page.php';

// Contact Validator Settings
const NUM_RECORDS_AT_ONCE = 50;

// Database Indices Constants
const PHONE_TYPE_LANDLINE_INDEX = 1;
const PHONE_TYPE_MOBILE_INDEX = 2;

// UK phone number constants
const UK_NUMBER_LENGTH = 11;

// MySQL strings to keep output consistent 
const RETRIEVE_QUERY = "SELECT contact_id, display_name, source, civicrm_phone.id AS phone_id, phone, phone_ext, phone_type_id as phone_type
	FROM civicrm_phone 
	INNER JOIN civicrm_contact 
		ON civicrm_phone.contact_id = civicrm_contact.id 
	WHERE civicrm_contact.is_deleted!='1' ";

const COUNT_QUERY = "SELECT COUNT(*) AS count 
	FROM civicrm_phone
	INNER JOIN civicrm_contact 
		ON civicrm_phone.contact_id = civicrm_contact.id 
	WHERE civicrm_contact.is_deleted!='1' AND ";

define("BROKEN_VALUES_FORMULA", "
	( (CHAR_LENGTH(REPLACE(phone, ' ', '')) != ".UK_NUMBER_LENGTH.") OR (SUBSTRING(phone, 1, 1)!='0') OR (REPLACE(phone, ' ', '') NOT REGEXP '^[0-9]+$') )
");

class CRM_Phonevalidator_Page_PhoneValidator extends CRM_Core_Page {

	private $brokenNumbersSql = array(); 
	private $landlineNumbersInMobilesSql = array(); 
	private $mobileNumbersInLandlinesSql = array(); 

	private function initSqlForBrokenNumbers(){

		$this->brokenNumbersSql['retrieve'] = RETRIEVE_QUERY." AND ".BROKEN_VALUES_FORMULA." 
		ORDER BY civicrm_contact.id  
		LIMIT 0, ".NUM_RECORDS_AT_ONCE; 

		$this->brokenNumbersSql['total'] = COUNT_QUERY.BROKEN_VALUES_FORMULA;

		// broken_nozero
		$this->brokenNumbersSql['noZero'] = COUNT_QUERY." (phone NOT LIKE '0%')";

		// broken_space
		$this->brokenNumbersSql['hasSpace'] = COUNT_QUERY." (phone LIKE '% %') "; 

		// broken_noteleven
		$this->brokenNumbersSql['notEleven'] = COUNT_QUERY." (CHAR_LENGTH(phone) != 11) ";
	
		// broken_containsbracket
		$this->brokenNumbersSql['hasBracket'] = COUNT_QUERY." (phone LIKE '%(%')" ;
	}

	private function runAdditionalQuery( $countSql, $index ){
		$dao = CRM_Core_DAO::executeQuery( $countSql );
		
		$result = 0;

		while ( $dao->fetch() ){
			$result = $dao->count;	
		}

		$this->assign( $index, $result );
	}

	private function initSqlForLandlineNumbersInMobiles(){

		$this->landlineNumbersInMobilesSql['retrieve'] = RETRIEVE_QUERY." AND 
			(phone NOT LIKE '07%') AND (phone LIKE '0%') AND NOT ".BROKEN_VALUES_FORMULA."  
			AND phone_type_id=".PHONE_TYPE_MOBILE_INDEX." ORDER BY civicrm_contact.id 
		LIMIT 0, ".NUM_RECORDS_AT_ONCE; 

		$this->landlineNumbersInMobilesSql['total'] = COUNT_QUERY." 
			(phone NOT LIKE '07%') AND (phone LIKE '0%') AND NOT ".BROKEN_VALUES_FORMULA."  
			AND phone_type_id=".PHONE_TYPE_MOBILE_INDEX;
	}

	private function initSqlForMobileNumbersInLandlines(){

		$this->mobileNumbersInLandlinesSql['retrieve'] = RETRIEVE_QUERY." AND  
			(phone LIKE '07%') AND NOT (".BROKEN_VALUES_FORMULA.")  
			AND phone_type_id!=".PHONE_TYPE_MOBILE_INDEX." ORDER BY civicrm_contact.id 
		LIMIT 0, ".NUM_RECORDS_AT_ONCE; 

		$this->mobileNumbersInLandlinesSql['total'] = COUNT_QUERY." 
			(phone LIKE '07%') AND NOT (".BROKEN_VALUES_FORMULA.")  
			AND phone_type_id!=".PHONE_TYPE_MOBILE_INDEX;

	}

	private function retrieveNumbers( $recordSql, $totalSql, $prefix ){

		// 1. Retrieve the relevant MySQL records data
		$dao = CRM_Core_DAO::executeQuery( $recordSql );

		// 2. Add it in the correct way
		$data = array();
		$iRow = 0;	
		while ( $dao->fetch() ){
			$data[$iRow] = array('contact_id' => $dao->contact_id, 'display_name' => $dao->display_name, 'phone_id' => $dao->phone_id, 'phone' =>  $dao->phone, 'phone_type'=>$dao->phone_type, 'phone_ext' => $dao->phone_ext, 'source' => $dao->source);
		$iRow ++;	
		}

		if ($totalSql != ""){
			$daoTotal = CRM_Core_DAO::executeQuery( $totalSql );
			$totalBadNumbers = 0;
			while ( $daoTotal->fetch() ){
				$totalBadNumbers = $daoTotal->count;	
			}
		}

		// 3. Assign it to a global variable 
		$this->assign( $prefix.'output', $data );
		$this->assign( $prefix.'count', $iRow );
		$this->assign( $prefix.'total', $totalBadNumbers );
	}

	public function run() {
		// Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
		CRM_Utils_System::setTitle(ts('UK Phone Number Validator'));

		$this->initSqlForBrokenNumbers();
		$this->initSqlForLandlineNumbersInMobiles();
		$this->initSqlForMobileNumbersInLandlines();

		$this->retrieveNumbers($this->brokenNumbersSql['retrieve'], $this->brokenNumbersSql['total'], 'broken_' );
		$this->runAdditionalQuery( $this->brokenNumbersSql['hasSpace'], 'broken_count_space' );
		$this->runAdditionalQuery( $this->brokenNumbersSql['noZero'], 'broken_count_nozero' );
		$this->runAdditionalQuery( $this->brokenNumbersSql['notEleven'], 'broken_count_noteleven' );
		$this->runAdditionalQuery( $this->brokenNumbersSql['hasBracket'], 'broken_count_containsbracket' );

		$this->retrieveNumbers( $this->mobileNumbersInLandlinesSql['retrieve'], $this->mobileNumbersInLandlinesSql['total'], 'mob_in_ll_' );

		$this->retrieveNumbers( $this->landlineNumbersInMobilesSql['retrieve'], $this->landlineNumbersInMobilesSql['total'], 'll_in_mob_');

		parent::run();
	}
}
