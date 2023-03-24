<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Core_Config;
use CRM_Core_Exception;
use CRM_Core_Form;
use CRM_Formfieldlibrary_ExtensionUtil as E;

class FileUploadField extends AbstractField {

  /**
   * Add the field to the form
   *
   * @param CRM_Core_Form $form
   * @param $field
   * @param bool $abTestingEnabled
   * @return array
   */
  public function addFieldToForm(CRM_Core_Form $form, $field, bool $abTestingEnabled=false): array {
    $config = CRM_Core_Config::singleton();
    // set default max file size as 2MB
    $maxFileSize = $config->maxFileSize ?: 2;

    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    try {
      $form->add('file', $field['name'], $field['title'], [], $is_required);
      $form->addUploadElement($field['name']);
      $form->setMaxFileSize($maxFileSize * 1024 * 1024);
      $form->addRule($field['name'],
        ts('File size should be less than %1 MByte(s)',
          [1 => $maxFileSize]
        ),
        'maxfilesize',
        $maxFileSize * 1024 * 1024
      );

      if ($this->areABVersionsEnabled($field)) {
        $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
        $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, FALSE);

        $form->add('file', $field['name_ab'], $field['title'], [], $bVersionIsRequired);
        $form->addUploadElement($field['name_ab']);
        $form->setMaxFileSize($maxFileSize * 1024 * 1024);
        $form->addRule($field['name_ab'],
          ts('File size should be less than %1 MByte(s)',
            [1 => $maxFileSize]
          ),
          'maxfilesize',
          $maxFileSize * 1024 * 1024
        );
      }

    } catch (CRM_Core_Exception $e) {
    }
    return $field;
  }

  /**
   * Return the template name of this field.
   *
   * return false|string
   */
  public function getFieldTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Field/GenericField.tpl";
  }


  /**
   * Return the submitted field value
   *
   * @param $field
   * @param array $submittedValues
   * @param bool $isVersionA
   * @return array
   */
  public function getSubmittedFieldValue($field, $submittedValues, bool $isVersionA = true): array {
    $return = ['upload_file_path' => ''];
    if (isset($submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)])) {
      $return['upload_file_path'] = $submittedValues[$this->getSubmissionKey($field['name'], $field, $isVersionA)]['name'];
    }
    return $return;
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
    if (isset($submittedValues[$field['name']])) {
      unlink($submittedValues[$field['name']]['name']);
    }
  }

  /**
   * Returns all the names of the possible outputs of this field.
   * Most fields would return one value. But for example a date and time field
   * returns the date value and time value.
   *
   * @return array
   */
  public function getOutputNames(): array {
    return array(
      'upload_file_path' => E::ts('Uploaded File Path'),
    );
  }

}
