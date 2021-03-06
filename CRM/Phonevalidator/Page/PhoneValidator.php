<?php

require_once 'CRM/Core/Page.php';

// Contact Validator Settings
const NUM_RECORDS_AT_ONCE = 50;
const SHOW_PHONE_TYPE_NONE_SELECTED = "no_phone_type_selected";
const SHOW_PHONE_TYPE_NONE_SELECTED_LABEL = "phone";
const SHOW_CONTACT_TYPE_NONE_SELECTED = "no_contact_type_selected";
const SHOW_CONTACT_TYPE_NONE_SELECTED_LABEL = "contact";

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
	private $phoneTypeToShow;
	private $contactTypeToShow;
	private $phoneTypeLimit;

	private function initPhoneTypeLimit(){
		$dao = CRM_Core_DAO::executeQuery( "SELECT MAX(civicrm_option_value.value) AS max_id FROM civicrm_option_group JOIN civicrm_option_value ON civicrm_option_group.id = civicrm_option_value.option_group_id WHERE civicrm_option_group.name='phone_type'" );
		
		$result = 0;

		while ( $dao->fetch() ){
			$result = $dao->max_id;	
		}

		$this->phoneTypeLimit = $result;
	}

	private function getPhoneTypeLabel($cleanPhoneTypeId){
		$dao = CRM_Core_DAO::executeQuery( "SELECT label FROM civicrm_option_group JOIN civicrm_option_value ON civicrm_option_group.id = civicrm_option_value.option_group_id WHERE civicrm_option_group.name='phone_type' AND value=".$cleanPhoneTypeId );
		
		$result = SHOW_PHONE_TYPE_NONE_SELECTED_LABEL;

		while ( $dao->fetch() ){
			$result = $dao->label;	
		}

		return strtolower($result);
	}

	private function initPhoneTypeToShow(){
		$returnValue = SHOW_PHONE_TYPE_NONE_SELECTED;
		$returnLabel = SHOW_PHONE_TYPE_NONE_SELECTED_LABEL;
		$rawPhoneType = $_GET['phone_type'];
		if ($rawPhoneType != NULL){
			$cleanPhoneTypeId = intval($rawPhoneType); // Use intval to prevent SQL injection
			if ($cleanPhoneType >= 0 AND $cleanPhoneTypeId <= $this->phoneTypeLimit){
				$returnValue = $cleanPhoneTypeId;
				$returnLabel = $this->getPhoneTypeLabel($cleanPhoneTypeId);
			}
		}
		$this->assign('selected_show_phone_type', $returnValue);
		$this->assign('selected_show_phone_type_label', $returnLabel);
		$this->phoneTypeToShow = $returnValue;
	}

	private function initContactTypeToShow(){
		$returnValue = SHOW_CONTACT_TYPE_NONE_SELECTED;
		$returnLabel = SHOW_CONTACT_TYPE_NONE_SELECTED_LABEL;
		$rawContactType = $_GET['contact_type'];
		if ($rawContactType != NULL && $rawContactType != SHOW_CONTACT_TYPE_NONE_SELECTED){
			$returnValue = mysql_real_escape_string($rawContactType); // Use mysql_real_escape_string to prevent SQL injection
			$returnLabel = strtolower($returnValue);
		}
		$this->assign('selected_show_contact_type', $returnValue);
		$this->assign('selected_show_contact_type_label', $returnLabel);
		$this->contactTypeToShow = $returnValue;
	}

	private function addPhoneTypeFilterToQueryIfNeeded(&$sqlQuery){
		if ($this->phoneTypeToShow != SHOW_PHONE_TYPE_NONE_SELECTED){
			$sqlQuery .= " AND phone_type_id=".$this->phoneTypeToShow; 
		}
	}

	private function addContactTypeFilterToQueryIfNeeded(&$sqlQuery){
		if ($this->contactTypeToShow != SHOW_CONTACT_TYPE_NONE_SELECTED){
			$sqlQuery .= " AND contact_type='".$this->contactTypeToShow."'";	
		}
	}

	private function initSqlForBrokenNumbers(){
		$this->brokenNumbersSql['retrieve'] = RETRIEVE_QUERY." AND ".BROKEN_VALUES_FORMULA;
		$this->addPhoneTypeFilterToQueryIfNeeded($this->brokenNumbersSql['retrieve']);
		$this->addContactTypeFilterToQueryIfNeeded($this->brokenNumbersSql['retrieve']);
		$this->brokenNumbersSql['retrieve'] .= " ORDER BY civicrm_contact.id  
							LIMIT 0, ".NUM_RECORDS_AT_ONCE; 

		$this->brokenNumbersSql['total'] = COUNT_QUERY.BROKEN_VALUES_FORMULA;
		$this->addPhoneTypeFilterToQueryIfNeeded($this->brokenNumbersSql['total']);
		$this->addContactTypeFilterToQueryIfNeeded($this->brokenNumbersSql['total']);

		// broken_nozero
		$this->brokenNumbersSql['noZero'] = COUNT_QUERY." (phone NOT LIKE '0%')";
		$this->addPhoneTypeFilterToQueryIfNeeded($this->brokenNumbersSql['noZero']);
		$this->addContactTypeFilterToQueryIfNeeded($this->brokenNumbersSql['noZero']);

		// broken_noteleven
		$this->brokenNumbersSql['notEleven'] = COUNT_QUERY." (CHAR_LENGTH(phone) != 11) ";
		$this->addPhoneTypeFilterToQueryIfNeeded($this->brokenNumbersSql['notEleven']);
		$this->addContactTypeFilterToQueryIfNeeded($this->brokenNumbersSql['notEleven']);
	
		// broken_containsNonNumber
		$this->brokenNumbersSql['hasNonNumber'] = COUNT_QUERY." (REPLACE(phone, ' ', '') NOT REGEXP '^[0-9]+$')";
		$this->addPhoneTypeFilterToQueryIfNeeded($this->brokenNumbersSql['hasNonNumber']);
		$this->addContactTypeFilterToQueryIfNeeded($this->brokenNumbersSql['hasNonNumber']);
	}

	private function runQueryAndAssign( $countSql, $index ){
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

		$this->initPhoneTypeLimit();
		$this->initPhoneTypeToShow();
		$this->initContactTypeToShow();
		$this->initSqlForBrokenNumbers();
		$this->initSqlForLandlineNumbersInMobiles();
		$this->initSqlForMobileNumbersInLandlines();

		$this->retrieveNumbers($this->brokenNumbersSql['retrieve'], $this->brokenNumbersSql['total'], 'broken_' );
		$this->runQueryAndAssign( $this->brokenNumbersSql['noZero'], 'broken_count_nozero' );
		$this->runQueryAndAssign( $this->brokenNumbersSql['notEleven'], 'broken_count_noteleven' );
		$this->runQueryAndAssign( $this->brokenNumbersSql['hasNonNumber'], 'broken_count_containsnonnumber' );

		$this->retrieveNumbers( $this->mobileNumbersInLandlinesSql['retrieve'], $this->mobileNumbersInLandlinesSql['total'], 'mob_in_ll_' );

		$this->retrieveNumbers( $this->landlineNumbersInMobilesSql['retrieve'], $this->landlineNumbersInMobilesSql['total'], 'll_in_mob_');

		parent::run();
	}
}
