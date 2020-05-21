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
  public function buildConfigurationForm(\CRM_Core_Form $form, $field=[]) {
    // Example add a checkbox to the form.
    $option_group_api = civicrm_api3('OptionGroup', 'get', ['is_active' => 1, 'options' => ['limit' => 0]]);
    $option_groups = [];
    foreach($option_group_api['values'] as $option_group) {
      $option_groups[$option_group['id']] = $option_group['title'];
    }
    $form->add('select', 'option_group_id', E::ts('Option Group'), $option_groups, true, [
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ]);
    $form->add('select', 'value_attribute', E::ts('Value attribute'), ['value' => E::ts('Value'), 'id' => E::ts('Id')], true, [
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ]);
    $default_values = [];
    if (isset($field['configuration'])) {
      $default_values = self::getOptionGroupValues($field['configuration']['option_group_id'], $field['configuration']['value_attribute']);
    }
    $form->add('select', 'default_value_id', E::ts('Default value'), $default_values, false, [
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ]);
    if (isset($field['configuration'])) {
      $form->setDefaults([
        'option_group_id' => $field['configuration']['option_group_id'],
        'value_attribute' => $field['configuration']['value_attribute'],
        'default_value_id' => $field['configuration']['default_value_id'],
      ]);
    } else {
      $form->setDefaults([
        'value_attribute' => 'value',
      ]);
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
    $configuration['default_value_id'] = $submittedValues['default_value_id'];
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
    $option_group_name = civicrm_api3('OptionGroup', 'getvalue', ['return' => 'name', 'id' => $configuration['option_group_id']]);
    $configuration['option_group_id'] = $option_group_name;
    if ($configuration['default_value_id']) {
      $default_value_name = civicrm_api3('OptionValue', 'getvalue', ['return' => 'name', 'id' => $configuration['default_value_id']]);
      $configuration['default_value_id'] = $default_value_name;
    }
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
    $option_group_id = civicrm_api3('OptionGroup', 'getvalue', ['return' => 'id', 'name' => $configuration['option_group_id']]);
    $configuration['option_group_id'] = $option_group_id;
    if ($configuration['default_value_id']) {
      $default_value_id = civicrm_api3('OptionValue', 'getvalue', ['return' => 'id', 'name' => $configuration['default_value_id']]);
      $configuration['default_value_id'] = $default_value_id;
    }
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
    $options = self::getOptionGroupValues($field['configuration']['option_group_id'], $field['configuration']['value_attribute']);
    $form->add('select', $field['name'], $field['title'], $options, $is_required, [
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ]);
    if (isset($field['configuration']) && isset($field['configuration']['default_value_id'])) {
      $form->setDefaults([
        $field['name'] => $field['configuration']['default_value_id'],
      ]);
    }

  }

  /**
   * Get the option group values keyed by the specificied attribute
   *
   * @param $option_group_id
   * @param $value_attr
   *
   * @return array option values
   */
  public static function getOptionGroupValues($option_group_id, $value_attr) {
    $options = [];
    if (!$option_group_id or !$value_attr) {
      return $options;
    }
    $optionApi = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => $option_group_id,
      'is_active' => 1,
      'options' => ['limit' => 0]
    ]);
    foreach($optionApi['values'] as $option) {
      $options[$option[$value_attr]] = $option['label'];
    }
    return $options;
  }

  /**
   * AJAX function to return OptionGroup values
   *
   * Formatted ready for CRM.utils.setOptions
   */
  public static function getOptionGroupValuesAJAX() {
    $option_group_id = \CRM_Utils_Request::retrieve('option_group_id', 'String');
    $value_attr = \CRM_Utils_Request::retrieve('value_attr', 'String');
    $options = [];
    foreach (self::getOptionGroupValues($option_group_id, $value_attr) as $key => $value) {
      $options[] = ['key' => $key, 'value' => $value];
    }
    \CRM_Utils_JSON::output($options);
  }

}
