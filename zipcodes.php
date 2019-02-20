<?php

require_once 'zipcodes.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function zipcodes_civicrm_config(&$config) {
  _zipcodes_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function zipcodes_civicrm_xmlMenu(&$files) {
  _zipcodes_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function zipcodes_civicrm_install() {
  return _zipcodes_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function zipcodes_civicrm_uninstall() {
  return _zipcodes_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function zipcodes_civicrm_enable() {
  return _zipcodes_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function zipcodes_civicrm_disable() {
  return _zipcodes_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function zipcodes_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _zipcodes_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function zipcodes_civicrm_managed(&$entities) {
  return _zipcodes_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function zipcodes_civicrm_caseTypes(&$caseTypes) {
  _zipcodes_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation hook_civicrm_pre
 *
 * Used to parse the address if the country is Belgium.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pre
 */
function zipcodes_civicrm_pre($op, $objectName, $id, &$params) {
  CRM_Zipcodes_Parser::pre($op, $objectName, $id, $params);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function zipcodes_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _zipcodes_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function zipcodes_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    CRM_Core_Resources::singleton()->addScriptFile('be.aivl.zipcodes', 'postcodes.js');
    CRM_Zipcodes_Parser::buildAddressForm($form);
    CRM_Zipcodes_Parser::setStreetAddressOnForm($form);

  }
  if ($formName == 'CRM_Contact_Form_Inline_Address') {
    CRM_Zipcodes_Parser::buildAddressForm($form);
    CRM_Zipcodes_Parser::setStreetAddressOnForm($form);
  }
}

function zipcodes_civicrm_alterContent(  &$content, $context, $tplName, &$object ) {
  if ($object instanceof CRM_Contact_Form_Inline_Address) {
    $locBlockNo = CRM_Utils_Request::retrieve('locno', 'Positive', CRM_Core_DAO::$_nullObject, TRUE, NULL, $_REQUEST);
    $template = CRM_Core_Smarty::singleton();
    $template->assign('blockId', $locBlockNo);
    $template->assign('zipcodes', json_encode(zipcodes_get_all()));
    $content .= $template->fetch('CRM/Contact/Form/Edit/Address/postcode_js.tpl');
  }
  if ($object instanceof CRM_Contact_Form_Contact) {
    $template = CRM_Core_Smarty::singleton();
    $template->assign('zipcodes', json_encode(zipcodes_get_all()));
    $content .= $template->fetch('CRM/Contact/Form/Edit/postcode_contact_js.tpl');
  }
}

function zipcodes_get_all() {
  $location_qry_str = "
    SELECT zip, city, civicrm_state_province.id as state_province_id
    FROM civicrm_zipcodes
    INNER JOIN civicrm_state_province ON civicrm_zipcodes.state = civicrm_state_province.abbreviation AND country_id = 1020 
    ORDER BY `zip`, `city` ASC";
  $zipcodes = array();
  $dao = CRM_Core_DAO::executeQuery($location_qry_str);
  while ($dao->fetch()) {
    $zipcodes[$dao->zip] = array('zip' => $dao->zip, 'city' => $dao->city, 'state' => $dao->state_province_id);
  }
  return $zipcodes;
}

function zipcodes_civicrm_pageRun( &$page ) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()->addScriptFile('be.aivl.zipcodes', 'postcodes.js');
  }
}