{assign var="field_name" value=$field.name}

<div class="crm-section">
    <div class="label">{$form.$field_name.label}</div>
    <div class="content">{$form.$field_name.html}</div>
    <div class="clear"></div>
</div>