# How to add a custom field type

In this document we would create a new field type. The field type will show a drop down to select a group to the user. 
The configuration of the field is the group types (Access, Mailing) we would like to include in the list.

## Create the base class

Create a base class in *Civi\FormFieldLibrary\Field\GroupField.php*:

```php

namespace Civi\FormFieldLibrary\Field;

use CRM_Formfieldlibrary_ExtensionUtil as E;

class GroupField extends AbstractField {
  
}

```

## Add the configuration screen for this group

In this step we will add the configuration options for this. 
There is only one option we will add and that is the group type option.

```php

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
   * @param array $field
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $field=array()) {
    // Example add a checkbox to the form.
    $group_type_api = civicrm_api3('OptionValue', 'get', array('is_active' => 1, 'option_group_id' => 'group_type', 'options' => array('limit' => 0)));
    $group_types = array();
    foreach($group_type_api['values'] as $group_type) {
      $group_types[$group_type['value']] = $group_type['title'];
    }
    $form->add('select', 'group_type', E::ts('Group Type'), $group_type, false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
      'multiple' => true,
    ));
    if (isset($field['configuration'])) {
      $form->setDefaults(array(
        'group_type' => $field['configuration']['group_type'],
      ));
    }
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues   *
   * @return array
   */
  public function processConfiguration($submittedValues) {
    // Add the show_label to the configuration array.
    $configuration['group_type'] = $submittedValues['group_type'];
    return $configuration;
  }

  /**
   * When this field type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/FormFieldLibrary/Form/Configuration/GroupTypeField.tpl";
  }

```

The function `hasConfiguration` we indicate that this field has additional configuration.

In the function `buildConfigurationForm` we add the configuration fields to an existing form. We also set the default values.

In the function `processConfiguration` we process the submitted data from the form into an array which will be stored
in the *configuration* column of the *SearchTaskField* entity.

In the function `getConfigurationTemplateFileName` we define where the template file for the configuration form could be found.

## Create the template for the configuration form

Create the template file in the location defined in the function `getConfigurationTemplateFileName`. 

In the case above it is in *templates\CRM\FormFieldLibrary\Form\Configuration\GroupTypeField.tpl*:

```
<div class="crm-section">
    <div class="label">{$form.group_type.label}</div>
    <div class="content">{$form.group_type.html}</div>
    <div class="clear"></div>
</div>
```

## Add the field to the form

In this last step we will add the field to the form. 

In this step we will add a drop down field with all the groups available filtered on the group type configured in the configuration.

Add the following functions to `GroupField` class:

```php

/**
   * Add the field to the form
   *
   * @param \CRM_Core_Form $form
   * @param $field
   */
  public function addFieldToForm(\CRM_Core_Form $form, $field) {
    $is_required = false;
    if (isset($field['is_required'])) {
      $is_required = $field['is_required'];
    }

    $groupApiParams['is_active'] = 1;
    if (isset($field['configuration']['group_type']) && is_array($field['configuration']['group_type'])) {
      $groupApiParams['group_type'] = array('IN' => $field['configuration']['group_type']);
    }
    $groupApiParams['options']['limit'] = 0;
    $groupApi = civicrm_api3('Group', 'get', $groupApiParams);
    $groups = array();
    foreach($groupApi['values'] as $group) {
      $groups[$group['id']] = $group['title'];
    }
    $form->add('select', $field['name'], $field['title'], $groups, $is_required, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
  }

  /**
   * Return the template name of this field.
   *
   * return false|string
   */
  public function getFieldTemplateFileName() {
    return "CRM/FormFieldLibrary/Field/GenericField.tpl";
  }

```

The function `addFieldToTaskForm` adds the field to the form.

The function `getFieldTemplateFileName` defined where the template for the file is stored.
This extension contains a generic template for the field. But this could be overridden if you want to do so.

## Add the field to the provider

The last step is to add the field to the Provider.
If you have defined this field in the *FormFieldLibrary* extension then you can add it directly to the class. 
If you have defined the field in your own extension you can use the *civicrm_container* hook to add the field.
I will show both methods.

### Add the field to the provider from within FormFieldLibrary extension

Open the file *Civi\FormFieldLibrary\Library.php*:

In the `__construct` function add the following line:

```php
$this->addFieldType('group', 'Civi\FormFieldLibrary\Field\GroupField', E::ts('Group'));
```

That's it.

### Add the field to the provider from another extension

You have to create a *CompilerPass* class to add the field to the provider. The *CompilerPass* is called as soon 
as the Provider is instanciated and the actions defined in the compiler pass are executed.

Create a file in *Civi\YourExtensionNameSpace\CompilerPass\Fields.php*:

```php

namespace Civi\YourExtensionNameSpace\CompilerPass;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use CRM_YourExtensionNameSpace_ExtensionUtil as E;

class Fields implements CompilerPassInterface {

  public function process(ContainerBuilder $container) {
    if (!$container->hasDefinition('formfieldlibrary')) {
      return;
    }
    $providerDefinition = $container->getDefinition('formfieldlibrary');
    $providerDefinition->addMethodCall('addFieldType', array('group', 'Civi\FormFieldLibrary\Field\GroupField', E::ts('Group')));
  }

}

```
Now add this *CompilerPass* class to the civicrm container. Do this in the *[hook_civicrm_container](https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/)*:

```php

function hook_civicrm_container($container) {
  $container->addCompilerPass(new Civi\YourExtensionNameSpace\CompilerPass\Fields());
}

``` 
That's it.