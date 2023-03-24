<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CiviCRM_API3_Exception;
use CRM_Core_Exception;
use CRM_Core_Form;
use CRM_Formfieldlibrary_ExtensionUtil as E;

class MessageTemplate extends AbstractField {

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
  public function getConfigurationTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Form/Configuration/MessageTemplate.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration = parent::processConfiguration($submittedValues);
    $configuration['default_template'] = $submittedValues['default_template'];
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
    if (isset($configuration['default_template']) && $configuration['default_template']) {
      try {
        $template_title = civicrm_api3('MessageTemplate', 'getvalue', [
          'return' => 'msg_title',
          'id' => $configuration['default_template'],
        ]);
        $configuration['default_template'] = $template_title;
      } catch (CiviCRM_API3_Exception $e) {
      }
    }
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
    if (isset($configuration['default_template'])) {
      try {
        $template_id = civicrm_api3('MessageTemplate', 'getvalue', array('return' => 'id', 'msg_title' => $configuration['default_template']));
        $configuration['default_template'] = $template_id;
      } catch (CiviCRM_API3_Exception $e) {
        // Do nothing.
      }
    }
    return $configuration;
  }


  /**
   * Add the field to the form
   *
   * @param CRM_Core_Form $form
   * @param $field
   * @param bool $abTestingEnabled
   * @return array
   */
  public function addFieldToForm(CRM_Core_Form $form, $field, bool $abTestingEnabled=false): array {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
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
      $form->add('select', $field['name'], $field['title'], $message_templates, $is_required, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
      if ($this->areABVersionsEnabled($field)) {
        $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
        $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, FALSE);
        $form->add('select', $field['name_ab'], $field['title'], $message_templates, $bVersionIsRequired, [
          'style' => 'min-width:250px',
          'class' => 'crm-select2 huge',
          'placeholder' => E::ts('- select -'),
        ]);
      }
    } catch (CRM_Core_Exception $e) {
    }
    if (isset($field['configuration']) && isset($field['configuration']['default_template'])) {
      $form->setDefaults(array(
        $field['name'] => $field['configuration']['default_template'],
      ));
      if (isset($field['name_ab'])) {
        $form->setDefaults(array(
          $field['name_ab'] => $field['configuration']['default_template'],
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
  public function getSubmittedFieldValue($field, $submittedValues, bool $isVersionA = true): array {
    $messageTemplateId = $submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)];
    $return['id'] = $messageTemplateId;
    try {
      $messageTemplate = civicrm_api3('MessageTemplate', 'getsingle', ['id' => $messageTemplateId]);
      $return['subject'] = $messageTemplate['msg_subject'] ?? '';
      $return['html_body'] = $messageTemplate['msg_html'] ?? '';
      $return['text_body'] = $messageTemplate['msg_text'] ?? '';
      if (isset($messageTemplate['msg_html']) && !empty($messageTemplate['msg_html'])) {
        $return['body'] = $messageTemplate['msg_html'];
      } elseif (isset($messageTemplate['msg_text']) && !empty($messageTemplate['msg_text'])) {
        $return['body'] = $messageTemplate['msg_text'];
      }
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
      'body' => E::ts('Message'),
      'html_body' => E::ts('HTML Message'),
      'text_body' => E::ts('Text Message'),
      'subject' => E::ts('Subject'),
      'id' => E::ts('ID'),
    ];
  }

}
