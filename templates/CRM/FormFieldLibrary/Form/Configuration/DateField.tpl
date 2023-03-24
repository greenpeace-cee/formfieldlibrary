{crmScope extensionKey='formfieldlibrary'}
{include file="CRM/FormFieldLibrary/Form/Configuration/AbstractField.tpl"}
<div class="crm-section">
    <div class="label">{$form.default_date.label}</div>
    <div class="content">{$form.default_date.html}
      <p class="description">{ts 1="https://www.php.net/manual/en/datetime.formats.php"}See <a href="%1">php.net</a> for possible formats{/ts}</p>
    </div>
    <div class="clear"></div>
</div>
{/crmScope}
