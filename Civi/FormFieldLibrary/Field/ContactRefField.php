<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CiviCRM_API3_Exception;
use CRM_Core_Exception;
use CRM_Core_Form;
use CRM_Formfieldlibrary_ExtensionUtil as E;

class ContactRefField extends AbstractField {

  /**
   * Add the field to the form
   *
   * @param CRM_Core_Form $form
   * @param $field
   * @param bool $abTestingEnabled
   * @return array
   */
  public function addFieldToForm(CRM_Core_Form $form, $field, bool $abTestingEnabled=false): array {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    $configuration = $field['configuration'];

    $api_params = array();
    if (!empty($configuration['limit_groups'])) {
      $api_params['group'] = ['IN' => $configuration['limit_groups']];
    }
    $limitContactTypes = array();
    $limitContactSubTypes = array();
    if (isset($configuration['limit_contact_types']) && is_array($configuration['limit_contact_types'])) {
      foreach($configuration['limit_contact_types'] as $limit_contact_type) {
        if (stripos($limit_contact_type, 'sub_') === 0) {
          // This is a subtype
          $limitContactSubTypes[] = substr($limit_contact_type, 4);
        } else {
          $limitContactTypes[] = $limit_contact_type;
        }
      }
    }
    if (count($limitContactTypes)) {
      $api_params['contact_type'] = ['IN' => $limitContactTypes];
    }
    if (count($limitContactSubTypes)) {
      $api_params['contact_sub_type'] = ['IN' => $limitContactSubTypes];
    }
    $props = array(
      'placeholder' => E::ts('Select a contact'),
      'entity' => 'Contact',
      'api' => array('params' => $api_params),
      'create' => false,
      'multiple' => false,
      'style' => 'min-width: 250px;',
      'class' => 'huge',
    );
    $form->addEntityRef( $field['name'], $field['title'], $props, $is_required);
    if ($this->areABVersionsEnabled($field)) {
      $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
      $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, FALSE);
      $form->addEntityRef( $field['name_ab'], $field['title'], $props, $bVersionIsRequired);
    }
    if (isset($configuration['default_contact']) && $configuration['default_contact']) {
      $defaults[$field['name']] = $configuration['default_contact'];
      $defaults[$this->getSubmissionKey($field['name'], $field, FALSE)] = $configuration['default_contact'];
      $form->setDefaults($defaults);
    }
    return $field;
  }

  /**
   * Returns true when this field has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration(): bool {
    return true;
  }

  /**
   * When this field type has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param CRM_Core_Form $form
   * @param array $field
   */
  public function buildConfigurationForm(CRM_Core_Form $form, array $field=array()) {
    parent::buildConfigurationForm($form, $field);
    try {
      $groupsApi = civicrm_api3('Group', 'get', [
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return;
    }
    $groups = array();
    foreach($groupsApi['values'] as $group) {
      $groups[$group['id']] = $group['title'];
    }
    try {
      $form->add('select', 'limit_groups', E::ts('Limit to Contacts in group(s)'), $groups, FALSE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- Show all groups -'),
        'multiple' => TRUE,
      ]);
    } catch (CRM_Core_Exception $e) {
    }

    try {
      $contactTypesApi = civicrm_api3('ContactType', 'get', [
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
      $contactTypes = [];
      foreach($contactTypesApi['values'] as $contactType) {
        $contactTypeName = $contactType['name'];
        if (isset($contactType['parent_id']) && $contactType['parent_id']) {
          $contactTypeName = 'sub_' . $contactTypeName;
        }
        $contactTypes[$contactTypeName] = $contactType['label'];
      }
      $form->add('select', 'limit_contact_types', E::ts('Limit to Contact Type(s)'), $contactTypes, FALSE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- All contact types -'),
        'multiple' => TRUE,
      ]);
    } catch (CRM_Core_Exception $e) {
    }

    $api_params = array();
    $props = array(
      'placeholder' => E::ts('Select a contact'),
      'entity' => 'Contact',
      'api' => array('params' => $api_params),
      'create' => false,
      'multiple' => false,
      'style' => 'min-width: 250px;',
      'class' => 'huge',
    );
    $form->addEntityRef( 'default_contact', E::ts('Default Contact'), $props);

    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      $defaults = array();
      if (isset($configuration['limit_groups'])) {
        $defaults['limit_groups'] = $configuration['limit_groups'];
      }
      if (isset($configuration['limit_contact_types'])) {
        $defaults['limit_contact_types'] = $configuration['limit_contact_types'];
      }
      if (isset($configuration['default_contact'])) {
        $defaults['default_contact'] = $configuration['default_contact'];
      }
      $form->setDefaults($defaults);
    }

  }

  /**
   * When this field type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Form/Configuration/ContactRefField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration = parent::processConfiguration($submittedValues);
    $configuration['limit_groups'] = $submittedValues['limit_groups'];
    $configuration['limit_contact_types'] = $submittedValues['limit_contact_types'];
    $configuration['default_contact'] = $submittedValues['default_contact'];
    return $configuration;
  }

  /**
   * Export a configuration.
   *
   * Use this function to manipulate the configuration which is exported.
   * E.g. change option_group_id to a name and do the reverse on import.
   *
   * @param $configuration
   * @return mixed
   */
  public function exportConfiguration($configuration) {
    try {
      $groupsApi = civicrm_api3('Group', 'get', [
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $configuration;
    }
    $groups = array();
    foreach($groupsApi['values'] as $group) {
      if (in_array($group['id'], $configuration['limit_groups'])) {
        $groups[] = $group['name'];
      }
    }
    $configuration['limit_groups'] = $groups;
    return $configuration;
  }

  /**
   * Import a configuration.
   *
   * Use this function to manipulate the configuration which is imported.
   * E.g. change option_group_name to an id.
   *
   * @param $configuration
   * @return mixed
   */
  public function importConfiguration($configuration) {
    try {
      $groupsApi = civicrm_api3('Group', 'get', [
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $configuration;
    }
    $groups = array();
    foreach($groupsApi['values'] as $group) {
      if (in_array($group['name'], $configuration['limit_groups'])) {
        $groups[] = $group['id'];
      }
    }
    $configuration['limit_groups'] = $groups;
    return $configuration;
  }

}
