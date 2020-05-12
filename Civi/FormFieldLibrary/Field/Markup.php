<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Formfieldlibrary_ExtensionUtil as E;
use Dompdf\Exception;

class Markup extends AbstractField {

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
    $form->add('checkbox', 'enable_template', E::ts('Enable template'), false, array());
    $form->add('checkbox', 'enable_subject', E::ts('Enable subject'), false, array());
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'enable_template' => $field['configuration']['enable_template'],
        'enable_subject' => $field['configuration']['enable_subject'],
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
    return "CRM/FormFieldLibrary/Form/Configuration/Markup.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues   *
   * @return array
   */
  public function processConfiguration($submittedValues) {
    return array(
      'enable_template' => $submittedValues['enable_template'],
      'enable_subject' => $submittedValues['enable_subject'],
    );
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

    $subjectField = $field['name'].'_subject';
    if (!$field['configuration']['enable_subject']) {
      $subjectField = '';
    }

    //get the tokens.
    $tokens = \CRM_Core_SelectValues::contactTokens();
    //sorted in ascending order tokens by ignoring word case
    $form->assign('tokens_'.$field['name'], \CRM_Utils_Token::formatTokensForDisplay($tokens));

    $message_template_api = civicrm_api3('MessageTemplate', 'get', array('is_active' => 1, 'workflow_id' => array("IS NULL" => 1), 'options' => array('limit' => 0)));
    $message_templates = array();
    foreach($message_template_api['values'] as $message_template) {
      $message_templates[$message_template['id']] = $message_template['msg_title'];
    }
    $form->add('select', $field['name'].'_template', E::ts('Template'), $message_templates, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
      'onChange' => "selectTemplate_{$field['name']}(this.value, '{$field['name']}_html_message', '', '{$subjectField}');",
    ));

    $form->add('wysiwyg', $field['name'].'_html_message', E::ts('Message'), array('cols' => '80', 'rows' => '8'), $is_required);
    $subject_is_required = $is_required;
    if (!$field['configuration']['enable_subject']) {
      $subject_is_required = false;
    }
    if ($subjectField) {
      $form->add('text', $field['name'] . '_subject', E::ts('Subject'), ['class' => 'huge'], $subject_is_required);
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
    $return['message'] = $submittedValues[$field['name'].'_html_message'];
    $return['subject'] = isset($submittedValues[$field['name'].'_subject']) ? $submittedValues[$field['name'].'_subject'] : '';
    $return['message_plain_text'] = \CRM_Utils_String::htmlToText($return['message']);
    return $return;
  }

  /**
   * Return whether the field is submitted
   *
   * @param $field
   * @param $subittedValues
   * @return bool
   */
  public function isFieldValueSubmitted($field, $subittedValues) {
    if (isset($subittedValues[$field['name'].'_html_message'])) {
      return true;
    }
    return false;
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
      'message' => E::ts('Message'),
      'message_plain_text' => E::ts('Plain text message'),
      'subject' => E::ts('Subject'),
    );
  }

  /**
   * Return the template name of this field.
   *
   * return false|string
   */
  public function getFieldTemplateFileName() {
    return "CRM/FormFieldLibrary/Field/Markup.tpl";
  }

}
