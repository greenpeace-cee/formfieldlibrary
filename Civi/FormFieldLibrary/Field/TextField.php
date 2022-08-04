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
   */
  public function addFieldToForm(CRM_Core_Form $form, $field) {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }
    try {
      $form->add('text', $field['name'], $field['title'], [], $is_required);
    } catch (\CRM_Core_Exception $e) {
    }
  }

}
