<?php

require_once 'paymentcontributionsync.civix.php';
// phpcs:disable
use CRM_Paymentcontributionsync_ExtensionUtil as E;
// phpcs:enable

function paymentcontributionsync_civicrm_postCommit($op, $objectName, $objectId, &$objectRef) {
  if (!($objectName == 'EntityFinancialTrxn' && $op == 'create')) {
    return;
  }
  // 2 EntityFinancialTrxn records are created on a transfer (one reverses the original payment, the other records the updated payment).
  // Catch the EntityFinancialTrxn with the positive amount, this has the updated payment_instrument_id and check_number.
  if ($objectRef->entity_table === 'civicrm_financial_item' && $objectRef->amount > 0) {
    // Get the contribution ID.
    $cidSql = "SELECT cc.id, cc.total_amount 
      FROM civicrm_financial_item cfi
      JOIN civicrm_line_item cli ON cfi.entity_id = cli.id AND cfi.entity_table = 'civicrm_line_item'
      JOIN civicrm_contribution cc ON contribution_id = cc.id
      WHERE cfi.id = %1
      AND cli.entity_table = 'civicrm_contribution'";
    $params = [1 => [$objectRef->entity_id, 'Integer']];
    $contribution = CRM_Core_DAO::executeQuery($cidSql, $params);
    $contribution->fetch();

    // Get the financial trxn's Check Number and Payment Method.
    $trxnSql = "SELECT total_amount, check_number, payment_instrument_id 
      FROM civicrm_financial_trxn cft 
      JOIN civicrm_entity_financial_trxn ceft ON ceft.financial_trxn_id = cft.id 
      WHERE ceft.entity_table = 'civicrm_contribution' AND ceft.entity_id = $contribution->id";
    $trxnDao = CRM_Core_DAO::executeQuery($trxnSql);
    // Ensure that all transactions have been for the entire amount of the contribution (to ensure only one payment exists, though it may have been transferred multiple times).
    $onePayment = TRUE;
    while ($trxnDao->fetch()) {
      if (abs($trxnDao->total_amount) != $contribution->total_amount) {
        $onePayment = FALSE;
        break;
      }
    }
    if ($onePayment) {
      // The last row fetched is the most recent transfer, let's push its data to the contribution.
      $updateSql = "UPDATE civicrm_contribution 
        SET check_number = '$trxnDao->check_number', 
        payment_instrument_id = $trxnDao->payment_instrument_id
        WHERE id = $contribution->id";
      CRM_Core_DAO::executeQuery($updateSql);
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function paymentcontributionsync_civicrm_config(&$config) {
  _paymentcontributionsync_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function paymentcontributionsync_civicrm_xmlMenu(&$files) {
  _paymentcontributionsync_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function paymentcontributionsync_civicrm_install() {
  _paymentcontributionsync_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function paymentcontributionsync_civicrm_postInstall() {
  _paymentcontributionsync_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function paymentcontributionsync_civicrm_uninstall() {
  _paymentcontributionsync_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function paymentcontributionsync_civicrm_enable() {
  _paymentcontributionsync_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function paymentcontributionsync_civicrm_disable() {
  _paymentcontributionsync_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function paymentcontributionsync_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _paymentcontributionsync_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function paymentcontributionsync_civicrm_managed(&$entities) {
  _paymentcontributionsync_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function paymentcontributionsync_civicrm_caseTypes(&$caseTypes) {
  _paymentcontributionsync_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function paymentcontributionsync_civicrm_angularModules(&$angularModules) {
  _paymentcontributionsync_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function paymentcontributionsync_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _paymentcontributionsync_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function paymentcontributionsync_civicrm_entityTypes(&$entityTypes) {
  _paymentcontributionsync_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function paymentcontributionsync_civicrm_themes(&$themes) {
  _paymentcontributionsync_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function paymentcontributionsync_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function paymentcontributionsync_civicrm_navigationMenu(&$menu) {
//  _paymentcontributionsync_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _paymentcontributionsync_civix_navigationMenu($menu);
//}
