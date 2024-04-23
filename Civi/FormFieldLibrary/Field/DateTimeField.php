<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Core_Exception;
use CRM_Core_Form;
use CRM_Formfieldlibrary_ExtensionUtil as E;
use DateTime;
use Exception;

class DateTimeField extends AbstractField {

  /**
   * Add the field to the form
   *
   * @param CRM_Core_Form $form
   * @param $field
   * @param bool $abTestingEnabled
   *
   * @return array
   */
  public function addFieldToForm(CRM_Core_Form $form, $field, bool $abTestingEnabled=false): array {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    $prop['time'] = TRUE;
    try {
      $form->add('datepicker', $field['name'], $field['title'], [], $is_required, $prop);
      if ($this->areABVersionsEnabled($field)) {
        $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
        $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, FALSE);
        $form->add('datepicker', $field['name_ab'], $field['title'], [], $bVersionIsRequired, $prop);
      }
    } catch (CRM_Core_Exception $e) {
    }

    if (isset($field['configuration']['default_date'])) {
      try {
        $date = new DateTime($field['configuration']['default_date']);
        $default_date = $date->format('Y-m-d H:i:s');
        $defaults[$field['name']] = $default_date;
        $defaults[$this->getSubmissionKey($field['name'], $field, FALSE)] = $default_date;
        $form->setDefaults($defaults);
      } catch (Exception $e) {
      }
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
   * @param \CRM_Core_Form $form
   * @param array $field
   */
  public function buildConfigurationForm(CRM_Core_Form $form, array $field=array()) {
    parent::buildConfigurationForm($form, $field);
    try {
      $form->add('text', 'default_date', E::ts('Default Date and Time'), ['class' => 'huge']);
    } catch (CRM_Core_Exception $e) {
    }
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'default_date' => $field['configuration']['default_date'],
      ));
    } else {
      $form->setDefaults(array(
        'default_date' => 'now'
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
    return "CRM/FormFieldLibrary/Form/Configuration/Date.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration = parent::processConfiguration($submittedValues);
    $configuration['default_date'] = $submittedValues['default_date'];
    return $configuration;
  }

  /**
   * Return the submitted field value
   *
   * @param $field
   * @param $submittedValues
   * @param bool $isVersionA
   * @return array
   */
  public function getSubmittedFieldValue($field, $submittedValues, bool $isVersionA=true): array {
    $value = null;
    if (isset($submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)])) {
      try {
        $date = new DateTime($submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)]);
        $value = $date->format('YmdHis');
      } catch (Exception $e) {
      }
    }
    return array('value' => $value);
  }

}
