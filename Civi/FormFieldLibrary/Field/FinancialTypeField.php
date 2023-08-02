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

class FinancialTypeField extends AbstractField {

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
    $configuration = $field['configuration'];

    $api_params = array();
    $api_params['is_active'] = '1';
    $props = array(
      'placeholder' => E::ts('Select a Financail Type'),
      'entity' => 'FinancialType',
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

    if (isset($configuration['default_financial_type']) && $configuration['default_financial_type']) {
      $defaults[$field['name']] = $configuration['default_financial_type'];
      $defaults[$this->getSubmissionKey($field['name'], $field, false)] = $configuration['default_financial_type'];
      $form->setDefaults($defaults);
    }
    return $field;
  }

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
    $api_params = array();
    $api_params['is_active'] = '1';
    $props = array(
      'placeholder' => E::ts('Select a default financial type'),
      'entity' => 'FinancialType',
      'api' => array('params' => $api_params),
      'create' => false,
      'multiple' => false,
      'style' => 'min-width: 250px;',
      'class' => 'huge',
      'select' => ['minimumInputLength' => 0]
    );
    $form->addEntityRef( 'default_financial_type', E::ts('Default Financial Type'), $props);

    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      $defaults = [];
      if (isset($configuration['default_financial_type'])) {
        $defaults['default_financial_type'] = $configuration['default_financial_type'];
      }
      $form->setDefaults($defaults);
    }

  }

  /**
   * When this field type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName(): ?string {
    return "CRM/FormFieldLibrary/Form/Configuration/FinancialTypeField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration = parent::processConfiguration($submittedValues);
    $configuration['default_financial_type'] = $submittedValues['default_financial_type'];
    return $configuration;
  }

}
