<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Formfieldlibrary_ExtensionUtil as E;

class ParticipantStatusField extends AbstractField {

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
    $optionApi = civicrm_api3('ParticipantStatusType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $options = array();
    foreach($optionApi['values'] as $option) {
      $options[$option['id']] = $option['label'];
    }
    $form->add('select', 'default_status_id', E::ts('Default Participant Status'), $options, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'default_status_id' => $field['configuration']['default_status_id'],
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
    return "CRM/FormFieldLibrary/Form/Configuration/ParticipantStatusField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    // Add the show_label to the configuration array.
    $configuration['default_status_id'] = $submittedValues['default_status_id'];
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
    $status_name = civicrm_api3('ParticipantStatusType', 'getvalue', array('return' => 'name', 'id' => $configuration['default_status_id']));
    $configuration['default_status_id'] = $status_name;
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
    $status_id = civicrm_api3('ParticipantStatusType', 'getvalue', array('return' => 'id', 'name' => $configuration['default_status_id']));
    $configuration['default_status_id'] = $status_id;
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
    $optionApi = civicrm_api3('ParticipantStatusType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $options = array();
    foreach($optionApi['values'] as $option) {
      $options[$option['id']] = $option['label'];
    }
    $form->add('select', $field['name'], $field['title'], $options, $is_required, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        $field['name'] => $field['configuration']['default_status_id'],
      ));
    }
  }

}
