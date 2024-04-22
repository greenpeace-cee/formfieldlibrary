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
   * @param bool $abTestingEnabled
   *
   * @return array
   */
  public function addFieldToForm(CRM_Core_Form $form, $field, bool $abTestingEnabled=false): array {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    $prop['time'] = FALSE;
    try {
      $form->add('checkbox', $field['name'], $field['title'], [], $is_required, $prop);
      if ($this->areABVersionsEnabled($field)) {
        $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
        $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, FALSE);
        $form->add('checkbox', $field['name_ab'], $field['title'], [], $bVersionIsRequired, $prop);
      }
    } catch (CRM_Core_Exception $e) {
      return $field;
    }

    if (!empty($field['configuration']['default_checked'])) {
      $defaults[$field['name']] = '1';
      $defaults[$this->getSubmissionKey($field['name'], $field, FALSE)] = '1';
      $form->setDefaults($defaults);
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
    $configuration = parent::processConfiguration($submittedValues);
    return ['default_checked' => $submittedValues['default_checked']];
  }

  /**
   * Return the submitted field value
   *
   * @param $field
   * @param $submittedValues
   * @param bool $isVersionA
   * @return array
   */
  public function getSubmittedFieldValue($field, $submittedValues, bool $isVersionA = true): array {
    $value = false;
    if (!empty($submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)])) {
      $value = true;
    }
    return ['value' => $value];
  }

}
