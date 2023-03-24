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

class ParticipantStatusField extends AbstractField {

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
    $options = [];
    try {
      $optionApi = civicrm_api3('ParticipantStatusType', 'get', [
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
      foreach($optionApi['values'] as $option) {
        $options[$option['id']] = $option['label'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    try {
      $form->add('select', 'default_status_id', E::ts('Default Participant Status'), $options, FALSE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }
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
  public function getConfigurationTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Form/Configuration/ParticipantStatusField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration = parent::processConfiguration($submittedValues);
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
    try {
      $status_name = civicrm_api3('ParticipantStatusType', 'getvalue', [
        'return' => 'name',
        'id' => $configuration['default_status_id'],
      ]);
      $configuration['default_status_id'] = $status_name;
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
      $status_id = civicrm_api3('ParticipantStatusType', 'getvalue', [
        'return' => 'id',
        'name' => $configuration['default_status_id'],
      ]);
      $configuration['default_status_id'] = $status_id;
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
    $options = [];
    try {
      $optionApi = civicrm_api3('ParticipantStatusType', 'get', [
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
      foreach($optionApi['values'] as $option) {
        $options[$option['id']] = $option['label'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
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
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        $field['name'] => $field['configuration']['default_status_id'],
      ));
      if (isset($field['name_ab'])) {
        $form->setDefaults(array(
          $field['name_ab'] => $field['configuration']['default_status_id'],
        ));
      }
    }
    return $field;
  }

}
