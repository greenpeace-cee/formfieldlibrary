<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary;

use CRM_Formfieldlibrary_ExtensionUtil as E;

class Library {

  private $fieldClassNames = array();

  private $fieldTitles = array();

  public function __construct() {
    $this->addFieldType('date', 'Civi\FormFieldLibrary\Field\DateField', E::ts('Date field'));
    $this->addFieldType('text', 'Civi\FormFieldLibrary\Field\TextField', E::ts('Text field'));
    $this->addFieldType('file_upload', 'Civi\FormFieldLibrary\Field\FileUploadField', E::ts('File Upload'));
    $this->addFieldType('money', 'Civi\FormFieldLibrary\Field\MoneyField', E::ts('Money field'));
    $this->addFieldType('checkbox', 'Civi\FormFieldLibrary\Field\CheckboxField', E::ts('Checkbox field'));
    $this->addFieldType('contact', 'Civi\FormFieldLibrary\Field\ContactRefField', E::ts('Contact Reference field'));
    $this->addFieldType('option_group', 'Civi\FormFieldLibrary\Field\OptionGroupField', E::ts('Option Group'));
    $this->addFieldType('participant_status', 'Civi\FormFieldLibrary\Field\ParticipantStatusField', E::ts('Participant Status'));
    $this->addFieldType('group', 'Civi\FormFieldLibrary\Field\GroupField', E::ts('Group'));
    $this->addFieldType('message_template', 'Civi\FormFieldLibrary\Field\MessageTemplate', E::ts('Message Template'));
    $this->addFieldType('markup', 'Civi\FormFieldLibrary\Field\Markup', E::ts('Markup / HTML'));
    $this->addFieldType('fromemail', 'Civi\FormFieldLibrary\Field\FromEmailField', E::ts('From Email'));
  }


  /**
   * @param $name
   * @param $class
   * @param $label
   *
   * @return \Civi\FormFieldLibrary\Library
   */
  public function addFieldType($name, $class, $label) {
    $this->fieldClassNames[$name] = $class;
    $this->fieldTitles[$name] = $label;
    return $this;
  }

  /**
   * @return array<String>
   */
  public function getFieldTypes() {
    return $this->fieldTitles;
  }

  /**
   * @param $name
   *
   * @return \Civi\FormFieldLibrary\Field\AbstractField
   */
  public function getFieldTypeByName($name) {
    return new $this->fieldClassNames[$name]();
  }

}
