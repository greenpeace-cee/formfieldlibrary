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

class EventField extends AbstractField {

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

    $api_params = array();
    $api_params['is_active'] = '1';
    $props = array(
      'placeholder' => E::ts('Select an event'),
      'entity' => 'Event',
      'api' => array('params' => $api_params),
      'create' => false,
      'multiple' => false,
      'style' => 'min-width: 250px;',
      'class' => 'huge',
      'select' => ['minimumInputLength' => 0]
    );
    $form->addEntityRef( $field['name'], $field['title'], $props, $is_required);

    if ($this->areABVersionsEnabled($field)) {
      $bVersionIsRequired = $this->isBVersionRequired($is_required, $abTestingEnabled, $form);
      $field['name_ab'] = $this->getSubmissionKey($field['name'], $field, false);
      $form->addEntityRef( $field['name_ab'], $field['title'], $props, $bVersionIsRequired);
    }

    return $field;
  }

}
