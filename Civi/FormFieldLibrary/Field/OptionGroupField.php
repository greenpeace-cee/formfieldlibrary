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
use CRM_Utils_JSON;
use CRM_Utils_Request;

class OptionGroupField extends AbstractField {

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
  public function buildConfigurationForm(CRM_Core_Form $form, array $field=[]) {
    parent::buildConfigurationForm($form, $field);
    $option_groups = [];
    try {
      $option_group_api = civicrm_api3('OptionGroup', 'get', [
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
      foreach($option_group_api['values'] as $option_group) {
        $option_groups[$option_group['id']] = $option_group['title'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    try {
      $form->add('select', 'option_group_id', E::ts('Option Group'), $option_groups, TRUE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }
    try {
      $form->add('select', 'value_attribute', E::ts('Value attribute'), [
        'value' => E::ts('Value'),
        'id' => E::ts('Id'),
      ], TRUE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }
    $default_values = [];
    if (isset($field['configuration'])) {
      $default_values = self::getOptionGroupValues($field['configuration']['option_group_id'], $field['configuration']['value_attribute']);
    }
    try {
      $form->add('select', 'default_value_id', E::ts('Default value'), $default_values, FALSE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }
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
  public function getConfigurationTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Form/Configuration/OptionGroupField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration = parent::processConfiguration($submittedValues);
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
    $option_group_id = $configuration['option_group_id'];
    try {
      $option_group_name = civicrm_api3('OptionGroup', 'getvalue', [
        'return' => 'name',
        'id' => $configuration['option_group_id'],
      ]);
      $configuration['option_group_id'] = $option_group_name;
      $value_attribute = $configuration['value_attribute'];
      if ($configuration['default_value_id']) {
        $default_value_name = civicrm_api3('OptionValue', 'getvalue', ['return' => 'name', $value_attribute => $configuration['default_value_id'], 'option_group_id' => $option_group_id]);
        $configuration['default_value_id'] = $default_value_name;
      }
    } catch (CiviCRM_API3_Exception $e) {
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
    try {
      $option_group_id = civicrm_api3('OptionGroup', 'getvalue', [
        'return' => 'id',
        'name' => $configuration['option_group_id'],
      ]);
      $configuration['option_group_id'] = $option_group_id;
      $value_attribute = $configuration['value_attribute'];
      if ($configuration['default_value_id']) {
        $default_value_id = civicrm_api3('OptionValue', 'getvalue', ['return' => $value_attribute, 'name' => $configuration['default_value_id'], 'option_group_id' => $option_group_id]);
        $configuration['default_value_id'] = $default_value_id;
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    return $configuration;
  }

  /**
   * Add the field to the task form
   *
   * @param \CRM_Core_Form $form
   * @param $field
   * @param bool $abTestingEnabled
   * @return array
   */
  public function addFieldToForm(CRM_Core_Form $form, $field, bool $abTestingEnabled=false): array {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    $options = self::getOptionGroupValues($field['configuration']['option_group_id'], $field['configuration']['value_attribute']);
    try {
      $form->add('select', $field['name'], $field['title'], $options, $is_required, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
      if ($this->areABVersionsEnabled($field)) {
        $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
        $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, FALSE);
        $form->add('select', $field['name_ab'], $field['title'], $options, $bVersionIsRequired, [
          'style' => 'min-width:250px',
          'class' => 'crm-select2 huge',
          'placeholder' => E::ts('- select -'),
        ]);
      }
    } catch (CRM_Core_Exception $e) {
    }
    if (isset($field['configuration']) && isset($field['configuration']['default_value_id'])) {
      $form->setDefaults([
        $field['name'] => $field['configuration']['default_value_id'],
      ]);
      if (isset($field['name_ab'])) {
        $form->setDefaults([
          $field['name_ab'] => $field['configuration']['default_value_id'],
        ]);
      }
    }
    return $field;
  }

  /**
   * Get the option group values keyed by the specificied attribute
   *
   * @param int $option_group_id
   * @param string $value_attr
   *
   * @return array option values
   */
  public static function getOptionGroupValues(int $option_group_id, string $value_attr): array {
    $options = [];
    if (!$option_group_id or !$value_attr) {
      return $options;
    }
    try {
      $optionApi = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => $option_group_id,
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
      foreach($optionApi['values'] as $option) {
        $options[$option[$value_attr]] = $option['label'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    return $options;
  }

  /**
   * AJAX function to return OptionGroup values
   *
   * Formatted ready for CRM.utils.setOptions
   */
  public static function getOptionGroupValuesAJAX() {
    $options = [];
    try {
      $option_group_id = CRM_Utils_Request::retrieve('option_group_id', 'String');
      $value_attr = CRM_Utils_Request::retrieve('value_attr', 'String');
      foreach (self::getOptionGroupValues($option_group_id, $value_attr) as $key => $value) {
        $options[] = ['key' => $key, 'value' => $value];
      }
    } catch (CRM_Core_Exception $e) {
    }
    CRM_Utils_JSON::output($options);
  }

}
