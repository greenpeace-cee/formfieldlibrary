<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\FormFieldLibrary;

use CRM_Core_Component;
use CRM_Extension_System;

class Component {

  /**
   * Name of the Component and the new core extension name
   *
   * @var string[]
   */
  public static $components = [
    'CiviContribute' => 'civi_contribute',
    'CiviCampaign' => 'civi_campaign',
    'CiviEvent' => 'civi_event',
    'CiviMember' => 'civi_member',
    'CiviMail' => 'civi_mail'
  ];

  /**
   * Checks whether a component is enabled.
   * Either by the new core extension or
   * by the old component framework.
   *
   * @param string $component
   *
   * @return bool
   */
  public static function isEnabled(string $component): bool {
    $ext = '';
    if (isset(static::$components[$component])) {
      $ext = static::$components[$component];
    }
    $extManager = CRM_Extension_System::singleton()->getManager();
    if (strlen($ext) && $extManager->getStatus($ext)==='installed') {
      return true;
    } elseif (CRM_Core_Component::isEnabled($component)) {
      return true;
    }
    return false;
  }

}
