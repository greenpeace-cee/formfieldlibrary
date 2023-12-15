<?php

require_once 'formfieldlibrary.civix.php';
use CRM_Formfieldlibrary_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function formfieldlibrary_civicrm_container(ContainerBuilder $container) {
  // Register the TypeFactory
  $definition = new Definition('Civi\FormFieldLibrary\Library');
  $definition->setPublic(true);
  if (method_exists(Definition::class, 'setPrivate')) {
    $definition->setPrivate(FALSE);
  }
  $container->setDefinition('formfieldlibrary', $definition);
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function formfieldlibrary_civicrm_config(&$config) {
  _formfieldlibrary_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function formfieldlibrary_civicrm_install() {
  _formfieldlibrary_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function formfieldlibrary_civicrm_enable() {
  _formfieldlibrary_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function formfieldlibrary_civicrm_navigationMenu(&$menu) {
  _formfieldlibrary_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _formfieldlibrary_civix_navigationMenu($menu);
} // */
