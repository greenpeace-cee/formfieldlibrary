<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CiviCRM_API3_Exception;
use CRM_Core_Exception;
use CRM_Core_Form;
use CRM_Core_SelectValues;
use CRM_Formfieldlibrary_ExtensionUtil as E;
use CRM_Utils_String;
use CRM_Utils_Token;

class Markup extends AbstractField {

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
  public function buildConfigurationForm(CRM_Core_Form $form, array $field=array()) {
    parent::buildConfigurationForm($form, $field);
    try {
      $form->add('checkbox', 'enable_template', E::ts('Enable template'), FALSE, []);
    } catch (CRM_Core_Exception $e) {
    }
    try {
      $form->add('checkbox', 'enable_subject', E::ts('Enable subject'), FALSE, []);
    } catch (CRM_Core_Exception $e) {
    }
    try {
      $form->add('checkbox', 'enable_plaintext', E::ts('Enable Plain Text'), FALSE, []);
    } catch (CRM_Core_Exception $e) {
    }
    $message_templates = [];
    try {
      $message_template_api = civicrm_api3('MessageTemplate', 'get', [
        'is_active' => 1,
        'workflow_id' => ["IS NULL" => 1],
        'options' => ['limit' => 0],
      ]);
      foreach ($message_template_api['values'] as $message_template) {
        $message_templates[$message_template['id']] = $message_template['msg_title'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    try {
      $form->add('select', 'default_template', E::ts('Default template'), $message_templates, FALSE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'enable_template' => $field['configuration']['enable_template'],
        'enable_subject' => $field['configuration']['enable_subject'],
        'default_template' => $field['configuration']['default_template'],
        'enable_plaintext' => $field['configuration']['enable_plaintext'],
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
    return "CRM/FormFieldLibrary/Form/Configuration/Markup.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration = parent::processConfiguration($submittedValues);
    $configuration['enable_template'] = $submittedValues['enable_template'];
    $configuration['enable_subject'] = $submittedValues['enable_subject'];
    $configuration['default_template'] = $submittedValues['default_template'];
    $configuration['enable_plaintext'] = $submittedValues['enable_plaintext'];
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
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }

    $subjectField = $field['name'].'_subject';
    if (!$field['configuration']['enable_subject']) {
      $subjectField = '';
    }
    $plainTextField = $field['name'].'_plaintext';
    $plainTextABField = $field['name'].'_plaintext';
    if (!$field['configuration']['enable_plaintext']) {
      $plainTextField = '';
      $plainTextABField = '';
    }

    //get the tokens.
    $tokens = CRM_Core_SelectValues::contactTokens();
    //sorted in ascending order tokens by ignoring word case
    $form->assign('tokens_'.$field['name'], CRM_Utils_Token::formatTokensForDisplay($tokens));

    if ($this->areABVersionsEnabled($field)) {
      $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, FALSE);
      $subjectABField = $field['name_ab'].'_subject';
      if (!$field['configuration']['enable_subject']) {
        $subjectABField = '';
      }
      $plainTextABField = $field['name_ab'].'_plaintext';
      if (!$field['configuration']['enable_plaintext']) {
        $plainTextABField = '';
      }
    }

    $message_templates = [];
    try {
      $message_template_api = civicrm_api3('MessageTemplate', 'get', [
        'is_active' => 1,
        'workflow_id' => ["IS NULL" => 1],
        'options' => ['limit' => 0],
      ]);
      foreach ($message_template_api['values'] as $message_template) {
        $message_templates[$message_template['id']] = $message_template['msg_title'];
      }
    } catch (CiviCRM_API3_Exception $e) {
    }
    try {
      $form->add('select', $field['name'] . '_template', E::ts('Template'), $message_templates, FALSE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
        'onChange' => "selectTemplate_{$field['name']}(this.value, '" . $field['name'] . "_html_message', '" . $field['name'] . "_plaintext', '" . $subjectField . "');",
      ]);
      if ($this->areABVersionsEnabled($field)) {
        $form->add('select', $field['name_ab'] . '_template', E::ts('Template'), $message_templates, FALSE, [
          'style' => 'min-width:250px',
          'class' => 'crm-select2 huge',
          'placeholder' => E::ts('- select -'),
          'onChange' => "selectTemplate_{$field['name']}(this.value, '" . $field['name_ab'] . "_html_message', '" . $field['name_ab'] . "_plaintext', '" . $subjectABField . "');",
        ]);
      }
    } catch (CRM_Core_Exception $e) {
    }

    try {
      $form->add('wysiwyg', $field['name'] . '_html_message', E::ts('Message'), [
        'cols' => '80',
        'rows' => '8',
      ], $is_required);
      if ($this->areABVersionsEnabled($field)) {
        $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
        $form->add('wysiwyg', $field['name_ab'] . '_html_message', E::ts('Message'), [
          'cols' => '80',
          'rows' => '8',
        ], $bVersionIsRequired);
      }
    } catch (CRM_Core_Exception $e) {
    }
    $subject_is_required = $is_required;
    if (!$field['configuration']['enable_subject']) {
      $subject_is_required = false;
    }
    if ($subjectField) {
      try {
        $form->add('text', $field['name'] . '_subject', E::ts('Subject'), ['class' => 'huge'], $subject_is_required);
        if ($this->areABVersionsEnabled($field)) {
          $bVersionIsRequired = $this->isBVersionRequired($subject_is_required, $abTestingEnabled, $form);
          $form->add('text', $field['name_ab'] . '_subject', E::ts('Subject'), ['class' => 'huge'], $bVersionIsRequired);
        }
      } catch (CRM_Core_Exception $e) {
      }
    }

    if ($plainTextField) {
      try {
        $form->add('textarea', $plainTextField, E::ts('Plain Text'), ['cols' => '80', 'rows' => '8']);
        if ($this->areABVersionsEnabled($field)) {
          $form->add('textarea', $plainTextABField, E::ts('Plain Text'), ['cols' => '80', 'rows' => '8']);
        }
      } catch (CRM_Core_Exception $e) {
      }
    }

    if (isset($field['configuration']) && isset($field['configuration']['default_template'])) {
      $messageTemplates = \Civi\Api4\MessageTemplate::get(FALSE)
        ->addSelect('msg_subject', 'msg_html', 'msg_text')
        ->addWhere('id', '=', $field['configuration']['default_template'])
        ->execute();
      $form->setDefaults(array(
        $field['name'] . '_template' => $field['configuration']['default_template'],
        $field['name'] . '_subject' => $messageTemplates[0]['msg_subject'],
        $field['name'] . '_html_message' => $messageTemplates[0]['msg_html'],
        $field['name'] . '_plaintext' => $messageTemplates[0]['msg_text'],
      ));
      if (isset($field['name_ab'])) {
        $form->setDefaults(array(
          $field['name_ab'] . '_template' => $field['configuration']['default_template'],
          $field['name_ab'] . '_subject' => $messageTemplates[0]['msg_subject'],
          $field['name_ab'] . '_html_message' => $messageTemplates[0]['msg_html'],
          $field['name_ab'] . '_plaintext' => $messageTemplates[0]['msg_text'],
        ));
      }
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
    $return['message'] = $submittedValues[$this->getSubmissionKey($field['name'].'_html_message', $field, $isVersionA)];
    $return['subject'] = $submittedValues[$this->getSubmissionKey($field['name'] . '_subject', $field, $isVersionA)] ?? '';
    $return['message_plain_text'] = $submittedValues[$this->getSubmissionKey($field['name'] . '_plaintext', $field, $isVersionA)] ?? '';
    if (!$field['configuration']['enable_plaintext']) {
      $return['message_plain_text'] = CRM_Utils_String::htmlToText($return['message']);
    }
    return $return;
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
    if (isset($submittedValues[$this->getSubmissionKey($field['name'].'_html_message', $field, $isVersionA)])) {
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
  public function getOutputNames(): array {
    return [
      'message' => E::ts('Message'),
      'message_plain_text' => E::ts('Plain text message'),
      'subject' => E::ts('Subject'),
    ];
  }

  /**
   * Return the template name of this field.
   *
   * return false|string
   */
  public function getFieldTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Field/Markup.tpl";
  }

}
