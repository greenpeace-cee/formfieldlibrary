<?php
/**
 * @author Klaas Eikelboom <klaas.eikelboom@civicoop.org>
 * @license AGPL-3.0
 */
namespace Civi\FormFieldLibrary\Field;

use CRM_Formfieldlibrary_ExtensionUtil as E;

class FromEmailField extends AbstractField {

  /**
   * Returns true when this field has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration() {
    return false;
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
    $fromemail_api = civicrm_api3('OptionValue', 'get', [
      'is_active' => 1,
      'option_group_id' => "from_email_address",
      'options' => ['limit' => 0],
    ]);
    $from_email = [];
    $default_value = NULL;
    foreach($fromemail_api['values'] as $value) {
      $from_email[$value['id']] =  htmlentities($value['label']);
      if($value['is_default']){
        $default_value = $value['id'];
      }
    }
    $form->add('select',  $field['name'], $field['title'], $from_email, $is_required, [
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ]);
    $form->setDefaults(array(
      $field['name'] => $default_value,
    ));
  }

  /**
   * Return the submitted field value
   *
   * @param $field
   * @param $submittedValues
   * @return array
   * @throws \Exception
   */
  public function getSubmittedFieldValue($field, $submittedValues) {
    $fromEmailId = $submittedValues[$field['name']];
    $completeEmail = civicrm_api3('OptionValue', 'getsingle', ['id' => $fromEmailId])['label'];
    $return['from_email'] = $this->pluckEmail($completeEmail);
    $return['from_name']  = $this->pluckName($completeEmail);
    return $return;
  }

  /**
   * Returns all the names of the possible outputs of this field.
   * Most fields would return one value. But for example a date and time field
   * returns the date value and time value.
   *
   * @return array
   */
  public function getOutputNames() {
    return array(
      'from_email' => E::ts('Email'),
      'from_name' => E::ts('Name'),
    );
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
