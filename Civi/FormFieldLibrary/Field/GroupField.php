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

class GroupField extends AbstractField {

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
    // Add a drop down for group type
    $group_types = [];
    try {
      $group_type_api = civicrm_api3('OptionValue', 'get', [
        'is_active' => 1,
        'option_group_id' => 'group_type',
        'options' => ['limit' => 0],
      ]);
      foreach($group_type_api['values'] as $group_type) {
        $group_types[$group_type['value']] = $group_type['label'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    try {
      $form->add('select', 'group_type', E::ts('Group Type'), $group_types, FALSE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- All Group Types -'),
        'multiple' => TRUE,
      ]);
    } catch (CRM_Core_Exception $e) {
    }
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'group_type' => $field['configuration']['group_type'],
      ));
    }
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    // Add the show_label to the configuration array.
    $configuration['group_type'] = $submittedValues['group_type'];
    return $configuration;
  }

  /**
   * When this field type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Form/Configuration/GroupTypeField.tpl";
  }

  /**
   * Add the field to the task form
   *
   * @param CRM_Core_Form $form
   * @param $field
   */
  public function addFieldToForm(CRM_Core_Form $form, $field) {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }

    $groups = [];
    $groupApiParams['is_active'] = 1;
    if (isset($field['configuration']['group_type']) && is_array($field['configuration']['group_type']) && count($field['configuration']['group_type'])) {
      $groupApiParams['group_type'] = array('IN' => $field['configuration']['group_type']);
    }
    $groupApiParams['options']['limit'] = 0;
    try {
      $groupApi = civicrm_api3('Group', 'get', $groupApiParams);
      foreach($groupApi['values'] as $group) {
        $groups[$group['id']] = $group['title'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    try {
      $form->add('select', $field['name'], $field['title'], $groups, $is_required, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }
  }


}
