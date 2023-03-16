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

class CampaignField extends AbstractField {

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
    $configuration = $field['configuration'];

    $api_params = array();
    $api_params['is_active'] = '1';
    $props = array(
      'placeholder' => E::ts('Select a campaign'),
      'entity' => 'Campaign',
      'api' => array('params' => $api_params),
      'create' => false,
      'multiple' => false,
      'style' => 'min-width: 250px;',
      'class' => 'huge',
      'select' => ['minimumInputLength' => 0]
    );
    $form->addEntityRef( $field['name'], $field['title'], $props, $is_required);
    if (isset($configuration['default_campaign']) && $configuration['default_campaign']) {
      $defaults[$field['name']] = $configuration['default_campaign'];
      $form->setDefaults($defaults);
    }
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
    $api_params = array();
    $api_params['is_active'] = '1';
    $props = array(
      'placeholder' => E::ts('Select a default campaign'),
      'entity' => 'Campaign',
      'api' => array('params' => $api_params),
      'create' => false,
      'multiple' => false,
      'style' => 'min-width: 250px;',
      'class' => 'huge',
      'select' => ['minimumInputLength' => 0]
    );
    $form->addEntityRef( 'default_campaign', E::ts('Default Campaign'), $props);

    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      $defaults = [];
      if (isset($configuration['default_campaign'])) {
        $defaults['default_campaign'] = $configuration['default_campaign'];
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
    return "CRM/FormFieldLibrary/Form/Configuration/CampaignField.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    // Add the show_label to the configuration array.
    $configuration['default_campaign'] = $submittedValues['default_campaign'];
    return $configuration;
  }

}
