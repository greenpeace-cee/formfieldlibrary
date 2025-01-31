<?php
/**
 * @author Klaas Eikelboom <klaas.eikelboom@civicoop.org>
 * @license AGPL-3.0
 */
namespace Civi\FormFieldLibrary\Field;

use CiviCRM_API3_Exception;
use CRM_Core_Exception;
use CRM_Core_Form;
use CRM_Formfieldlibrary_ExtensionUtil as E;

class FromEmailField extends AbstractField {

  /**
   * Returns true when this field has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration(): bool {
    return false;
  }

  /**
   * Add the field to the task form
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
    $from_email = [];
    $default_value = NULL;
    try {
      $fromemail_api = civicrm_api3('OptionValue', 'get', [
        'is_active' => 1,
        'option_group_id' => "from_email_address",
        'domain_id' => 'current_domain',
        'options' => ['limit' => 0],
      ]);
      foreach($fromemail_api['values'] as $value) {
        $from_email[$value['id']] =  htmlentities($value['label']);
        if($value['is_default']){
          $default_value = $value['id'];
        }
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    try {
      $form->add('select', $field['name'], $field['title'], $from_email, $is_required, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
      if ($this->areABVersionsEnabled($field)) {
        $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
        $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, FALSE);
        $form->add('select', $field['name_ab'], $field['title'], $from_email, $bVersionIsRequired, [
          'style' => 'min-width:250px',
          'class' => 'crm-select2 huge',
          'placeholder' => E::ts('- select -'),
        ]);
      }
    } catch (CRM_Core_Exception $e) {
    }
    $form->setDefaults(array(
      $field['name'] => $default_value,
    ));
    if (isset($field['name_ab'])) {
      $form->setDefaults(array(
        $field['name_ab'] => $default_value,
      ));
    }
    return $field;
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
    $return = [];
    $fromEmailId = $submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)];
    try {
      $completeEmail = civicrm_api3('OptionValue', 'getsingle', ['id' => $fromEmailId])['label'];
      $return['from_email'] = $this->pluckEmail($completeEmail);
      $return['from_name']  = $this->pluckName($completeEmail);
    } catch (CiviCRM_API3_Exception $e) {
    }
    return $return;
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
      'from_email' => E::ts('Email'),
      'from_name' => E::ts('Name'),
    ];
  }

  public function pluckEmail($header) {
    preg_match('/<([^<]*)>$/', $header, $matches);
    if (isset($matches[1])) {
      return $matches[1];
    }
    return NULL;
  }

  public function pluckName($header) {
    preg_match('/[^"]+/', $header, $matches);
    if (isset($matches[0])) {
      return $matches[0];
    }
    return NULL;
  }

}
