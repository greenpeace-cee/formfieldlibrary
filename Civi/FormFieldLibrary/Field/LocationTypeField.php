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

class LocationTypeField extends AbstractField {

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
    $default_values = self::getLocationTypes();
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
        'default_value_id' => $field['configuration']['default_value_id'],
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
    return "CRM/FormFieldLibrary/Form/Configuration/LocationTypeField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
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
    try {
      if ($configuration['default_value_id']) {
        $default_value_name = civicrm_api3('LocationType', 'getvalue', ['return' => 'name', 'id' => $configuration['default_value_id']]);
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
      if ($configuration['default_value_id']) {
        $default_value_id = civicrm_api3('LocationType', 'getvalue', ['return' => 'id', 'name' => $configuration['default_value_id']]);
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
   */
  public function addFieldToForm(CRM_Core_Form $form, $field) {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    $options = self::getLocationTypes();
    try {
      $form->add('select', $field['name'], $field['title'], $options, $is_required, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }
    if (isset($field['configuration']) && isset($field['configuration']['default_value_id'])) {
      $form->setDefaults([
        $field['name'] => $field['configuration']['default_value_id'],
      ]);
    }

  }

  /**
   * Get the location types.
   *
   * @return array
   */
  public static function getLocationTypes(): array {
    $options = [];
    try {
      $optionApi = civicrm_api3('LocationType', 'get', [
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
      foreach($optionApi['values'] as $option) {
        $options[$option['id']] = $option['display_name'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    return $options;
  }

}
