<?php

require_once 'phonevalidator.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function phonevalidator_civicrm_config(&$config) {
  _phonevalidator_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function phonevalidator_civicrm_xmlMenu(&$files) {
  _phonevalidator_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function phonevalidator_civicrm_install() {
  return _phonevalidator_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function phonevalidator_civicrm_uninstall() {
  return _phonevalidator_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function phonevalidator_civicrm_enable() {
  return _phonevalidator_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function phonevalidator_civicrm_disable() {
  return _phonevalidator_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function phonevalidator_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _phonevalidator_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function phonevalidator_civicrm_managed(&$entities) {
  return _phonevalidator_civix_civicrm_managed($entities);
}

function phonevalidator_civicrm_navigationMenu( &$params ) {
 // get the id of Administer Menu
  $contactMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Contacts', 'id', 'name');

  // skip adding menu if there is no Contacts menu
  if ($contactMenuId) {
    // get the maximum key under Contacts menu
    $maxKey = max( array_keys($params[$contactMenuId]['child']));
    $params[$contactMenuId]['child'][$maxKey+1] = array (
      'attributes' => array (
        'label' => 'UK Phone Number Validator',
        'name' => 'PhoneValidator',
        'url' => 'civicrm/phonevalidator',
        'permission' => 'administer CiviCRM',
        'operator' => NULL,
        'separator' => TRUE,
        'parentID' => $contactMenuId,
        'navID' => $maxKey+1,
        'active' => 1
      )
    );
  }
}
