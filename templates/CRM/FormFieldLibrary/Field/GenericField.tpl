{assign var="field_name" value=$field.name}
{assign var="field_name_ab" value=$field.name_ab}

{if (!isset($isAVersion) || $isAVersion)}
<div class="crm-section" data-b-field="{$field_name_ab}">
    <div class="label">{$form.$field_name.label}</div>
    <div class="content">{$form.$field_name.html}</div>
    <div class="clear"></div>
</div>
{/if}
{if (isset($isAVersion) && !$isAVersion && $form.$field_name_ab)}
  <div class="crm-section">
    <div class="label">{$form.$field_name_ab.label}</div>
    <div class="content">{$form.$field_name_ab.html}</div>
    <div class="clear"></div>
  </div>
{/if}
