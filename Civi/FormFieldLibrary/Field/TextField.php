<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary\Field;

use CRM_Core_Form;
use CRM_Formfieldlibrary_ExtensionUtil as E;

class TextField extends AbstractField {

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
    try {
      $form->add('text', $this->getSubmissionKey($field['name'], $field, true), $field['title'], [], $is_required);
      if ($this->areABVersionsEnabled($field)) {
        $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
        $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, false);
        $form->add('text', $this->getSubmissionKey($field['name'], $field, false), $field['title'], [], $bVersionIsRequired);
      }
    } catch (\CRM_Core_Exception $e) {
    }
    return $field;
  }

}
