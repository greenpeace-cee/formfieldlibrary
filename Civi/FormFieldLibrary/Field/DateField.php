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

class DateField extends AbstractField {

  /**
   * Add the field to the form
   *
   * @param CRM_Core_Form $form
   * @param $field
   */
  public function addFieldToForm(CRM_Core_Form $form, $field) {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    $prop['time'] = FALSE;
    try {
      $form->add('datepicker', $field['name'], $field['title'], [], $is_required, $prop);
    } catch (CRM_Core_Exception $e) {
    }

    if (isset($field['configuration']['default_date'])) {
      try {
        $date = new DateTime($field['configuration']['default_date']);
        $default_date = $date->format('Y-m-d H:i:s');
        $form->setDefaults(array(
          $field['name'] => $default_date,
        ));
      } catch (Exception $e) {
      }
    }
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
    try {
      $form->add('text', 'default_date', E::ts('Default Date'), ['class' => 'huge']);
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
    return ['default_date' => $submittedValues['default_date']];
  }

  /**
   * Return the submitted field value
   *
   * @param $field
   * @param $submittedValues
   * @return array
   */
  public function getSubmittedFieldValue($field, $submittedValues): array {
    $value = null;
    if (isset($submittedValues[$field['name']])) {
      try {
        $date = new DateTime($submittedValues[$field['name']]);
        $value = $date->format('Ymd');
      } catch (Exception $e) {
      }
    }
    return array('value' => $value);
  }

}
