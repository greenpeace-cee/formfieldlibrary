<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Formfieldlibrary_ExtensionUtil as E;

class MessageTemplate extends AbstractField {

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
    $message_template_api = civicrm_api3('MessageTemplate', 'get', array('is_active' => 1, 'workflow_id' => array("IS NULL" => 1), 'options' => array('limit' => 0)));
    $message_templates = array();
    foreach($message_template_api['values'] as $message_template) {
      $message_templates[$message_template['id']] = $message_template['msg_title'];
    }
    $form->add('select', 'default_template', E::ts('Default template'), $message_templates, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'default_template' => $field['configuration']['default_template'],
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
    return "CRM/FormFieldLibrary/Form/Configuration/MessageTemplate.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues   *
   * @return array
   */
  public function processConfiguration($submittedValues) {
    return array('default_template' => $submittedValues['default_template']);
  }


  /**
   * Add the field to the form
   *
   * @param \CRM_Core_Form $form
   * @param $field
   * @throws \Exception
   */
  public function addFieldToForm(\CRM_Core_Form $form, $field) {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }

    $message_template_api = civicrm_api3('MessageTemplate', 'get', array('is_active' => 1, 'workflow_id' => array("IS NULL" => 1), 'options' => array('limit' => 0)));
    $message_templates = array();
    foreach($message_template_api['values'] as $message_template) {
      $message_templates[$message_template['id']] = $message_template['msg_title'];
    }
    $form->add('select', $field['name'], $field['title'], $message_templates, $is_required, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    if (isset($field['configuration']) && isset($field['configuration']['default_template'])) {
      $form->setDefaults(array(
        $field['name'] => $field['configuration']['default_template'],
      ));
    }
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
    $messageTemplateId = $submittedValues[$field['name']];
    $return['id'] = $messageTemplateId;
    $messageTemplate = civicrm_api3('MessageTemplate', 'getsingle', array('id' => $messageTemplateId));
    $return['subject'] = isset($messageTemplate['msg_subject']) ? $messageTemplate['msg_subject'] : '';
    $return['html_body'] = isset($messageTemplate['msg_html']) ? $messageTemplate['msg_html'] : '';
    $return['text_body'] = isset($messageTemplate['msg_text']) ? $messageTemplate['msg_text'] : '';
    if (isset($messageTemplate['msg_html']) && !empty($messageTemplate['msg_html'])) {
      $return['body'] = $messageTemplate['msg_html'];
    } elseif (isset($messageTemplate['msg_text']) && !empty($messageTemplate['msg_text'])) {
      $return['body'] = $messageTemplate['msg_text'];
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
  public function getOutputNames() {
    return array(
      'body' => E::ts('Message'),
      'html_body' => E::ts('HTML Message'),
      'text_body' => E::ts('Text Message'),
      'subject' => E::ts('Subject'),
      'id' => E::ts('ID'),
    );
  }



}