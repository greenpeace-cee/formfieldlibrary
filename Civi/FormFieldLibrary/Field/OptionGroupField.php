<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Formfieldlibrary_ExtensionUtil as E;

class OptionGroupField extends AbstractField {

  /**
   * Returns true when this field has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration() {
    return true;
  }

  /**
   * When this field type has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param array $field
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $field=array()) {
    // Example add a checkbox to the form.
    $option_group_api = civicrm_api3('OptionGroup', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $option_groups = array();
    foreach($option_group_api['values'] as $option_group) {
      $option_groups[$option_group['id']] = $option_group['title'];
    }
    $form->add('select', 'option_group_id', E::ts('Option Group'), $option_groups, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $form->add('select', 'value_attribute', E::ts('Value attribute'), array('value' => E::ts('Value'), 'id' => E::ts('Id')), true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'option_group_id' => $field['configuration']['option_group_id'],
        'value_attribute' => $field['configuration']['value_attribute'],
      ));
    } else {
      $form->setDefaults(array(
        'value_attribute' => 'value',
      ));
    }
  }

  /**
   * When this field type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/FormFieldLibrary/Form/Configuration/OptionGroupField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    // Add the show_label to the configuration array.
    $configuration['option_group_id'] = $submittedValues['option_group_id'];
    $configuration['value_attribute'] = $submittedValues['value_attribute'];
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
    $option_group_name = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'name', array('id' => $configuration['option_group_id'])));
    $configuration['option_group_id'] = $option_group_name;
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
    $option_group_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', array('name' => $configuration['option_group_id'])));
    $configuration['option_group_id'] = $option_group_id;
    return $configuration;
  }

  /**
   * Add the field to the task form
   *
   * @param \CRM_Core_Form $form
   * @param $field
   */
  public function addFieldToForm(\CRM_Core_Form $form, $field) {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    $optionApi = civicrm_api3('OptionValue', 'get', array('option_group_id' => $field['configuration']['option_group_id'], 'is_active' => 1, 'options' => array('limit' => 0)));
    $options = array();
    foreach($optionApi['values'] as $option) {
      $value_attr = $field['configuration']['value_attribute'];
      $options[$option[$value_attr]] = $option['label'];
    }
    $form->add('select', $field['name'], $field['title'], $options, $is_required, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
  }

}