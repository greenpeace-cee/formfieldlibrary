<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Formfieldlibrary_ExtensionUtil as E;

class DateField extends AbstractField {

  /**
   * Add the field to the form
   *
   * @param \CRM_Core_Form $form
   * @param $field
   */
  public function addFieldToForm(\CRM_Core_Form $form, $field) {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    $config = \CRM_Core_Config::singleton();

    $params = array(
      'date' => TRUE,
      'time' => FALSE,
    );

    $defaults = array();
    if (isset($field['configuration']['default_date'])) {
      $defaultDate = new \DateTime();
      $defaultDate->modify($field['configuration']['default_date']);
      $defaults[$field['name']] = $defaultDate->format('Y-m-d');
    }

    $form->add('datepicker', $field['name'], $field['title'], array(), $is_required, $params);
    $form->setDefaults($defaults);
  }

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
   * @param \CRM_Core_Form $form
   * @param array $field
   * @throws \Exception
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $field=array()) {
    $form->add('text', 'default_date', E::ts('Default date'), array('style' => 'min-width:250px', 'class' => 'huge'), false);
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'default_date' => $field['configuration']['default_date'],
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
    return "CRM/FormFieldLibrary/Form/Configuration/DateField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues   *
   * @return array
   */
  public function processConfiguration($submittedValues) {
    return array('default_date' => $submittedValues['default_date']);
  }

  /**
   * Return the submitted field value
   *
   * @param $field
   * @param $submittedValues
   * @return array
   */
  public function getSubmittedFieldValue($field, $submittedValues) {
    $date = new \DateTime($submittedValues[$field['name']]);
    return array('date' => $date->format('Ymd'));
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
      'date' => E::ts('Date'),
    );
  }

}
