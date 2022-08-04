<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Core_Exception;
use CRM_Core_Form;
use CRM_Formfieldlibrary_ExtensionUtil as E;

class CheckboxField extends AbstractField {

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
      $form->add('checkbox', $field['name'], $field['title'], [], $is_required, $prop);
    } catch (CRM_Core_Exception $e) {
      return;
    }

    if (isset($field['configuration']['default_checked'])) {
      $form->setDefaults(array(
        $field['name'] => 1,
      ));
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
      $form->add('checkbox', 'default_checked', E::ts('Default Checked'), []);
    } catch (CRM_Core_Exception $e) {
    }
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'default_checked' => $field['configuration']['default_checked'],
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
    return "CRM/FormFieldLibrary/Form/Configuration/CheckboxField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues   *
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    return ['default_checked' => $submittedValues['default_checked']];
  }

  /**
   * Return the submitted field value
   *
   * @param $field
   * @param $submittedValues
   * @return array
   */
  public function getSubmittedFieldValue($field, $submittedValues): array {
    $value = false;
    if (!empty($submittedValues[$field['name']])) {
      $value = true;
    }
    return ['value' => $value];
  }

}
