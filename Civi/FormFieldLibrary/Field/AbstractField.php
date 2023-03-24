<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Core_Form;
use CRM_Formfieldlibrary_ExtensionUtil as E;

/**
 * All the functions in this class could be overriden by child classes.
 * Class AbstractField
 *
 * @package Civi\FormFieldLibrary\Field
 */
abstract class AbstractField {

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
    $form->add('checkbox', 'enable_a_b_versions', E::ts('Enable A/B Versions'));
    $defaults['enable_a_b_versions'] = '0';
    if ($this->areABVersionsEnabled($field)) {
      $defaults['enable_a_b_versions'] = '1';
    }
    $form->setDefaults($defaults);
  }

  /**
   * When this field type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Form/Configuration/AbstractField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration['enable_a_b_versions'] = $submittedValues['enable_a_b_versions'] ? '1' : '0';
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
    return $configuration;
  }

  /**
   * Add the field to the form
   *
   * @param \CRM_Core_Form $form
   * @param $field
   * @param bool $abTestingEnabled
   * @return array
   */
  public function addFieldToForm(CRM_Core_Form $form, $field, bool $abTestingEnabled=false): array {
    // $form->add('text', $field['name'], $field['title'], $field['is_required']);
    return $field;
  }

  /**
   * Return the template name of this field.
   *
   * @return false|string
   */
  public function getFieldTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Field/GenericField.tpl";
  }

  protected function isBVersionRequired(bool $isRequired, bool $abTestingEnabled, CRM_Core_Form $form): bool {
    $bVersionIsRequired = $isRequired;
    if ($form->isSubmitted() && !$abTestingEnabled) {
      $bVersionIsRequired = false;
    }
    return $bVersionIsRequired;
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
    return [
      'value' => $submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)]
    ];
  }

  /**
   * Return whether the field is submitted
   *
   * @param $field
   * @param $submittedValues
   * @param bool $isVersionA
   * @return bool
   */
  public function isFieldValueSubmitted($field, $submittedValues, bool $isVersionA = true): bool {
    return isset($submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)]);
  }

  /**
   * Returns the submission key based on whether we need version A or Version B.
   * By default Version A.
   *
   * @param string $name
   * @param array $field
   * @param bool $isVersionA
   *
   * @return string
   */
  protected function getSubmissionKey(string $name, array $field, bool $isVersionA = true): string {
    if ($isVersionA || !$this->areABVersionsEnabled($field)) {
      return $name;
    } else {
      return 'version_b_'.$name;
    }
  }

  public function areABVersionsEnabled(array $field): bool {
    if (!isset($field['configuration'])) {
      return false;
    }
    if (!isset($field['configuration']['enable_a_b_versions'])) {
      return false;
    }
    return (bool) $field['configuration']['enable_a_b_versions'];
  }

  /**
   * Method is called as soon as a batch action is finished.
   * Child classes can do clean up in this method.
   *
   * @param $field
   * @param $submittedValues
   *
   * @return void
   */
  public function onBatchFinished($field, $submittedValues) {
    // Do nothing.
  }

  /**
   * Returns all the names of the possible outputs of this field.
   * Most fields would return one value. But for example a date and time field
   * returns the date value and time value.
   *
   * @return array
   */
  public function getOutputNames(): array {
    return [
      'value' => E::ts('Value'),
    ];
  }

}
