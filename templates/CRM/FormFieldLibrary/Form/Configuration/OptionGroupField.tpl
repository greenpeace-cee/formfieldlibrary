{include file="CRM/FormFieldLibrary/Form/Configuration/AbstractField.tpl"}
<div class="crm-section">
    <div class="label">{$form.option_group_id.label}</div>
    <div id="optgroup" class="content">{$form.option_group_id.html}</div>
    <div class="clear"></div>
</div>
<div class="crm-section">
    <div class="label">{$form.value_attribute.label}</div>
    <div id="valattr" class="content">{$form.value_attribute.html}</div>
    <div class="clear"></div>
</div><div class="crm-section">
    <div class="label">{$form.default_value_id.label}</div>
    <div id="defval" class="content">{$form.default_value_id.html}</div>
    <div class="clear"></div>
</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {
  // Update the options list when either the option group or the value attribute change
  $('#optgroup, #valattr').change(function() {
    var option_group_id = $('#optgroup select').val();
    var value_attr = $('#valattr select').val();
    if (!option_group_id || !value_attr) {
      return;
    }
    var url = CRM.url('civicrm/ajax/getoptiongroupvalues', {option_group_id: option_group_id, value_attr: value_attr});
    $.ajax({
      url: url,
      dataType: "json",
      timeout: "5000",
      success: function(opts, status) {
        var $target = $('#defval select');
        var data = $target.data();
        CRM.utils.setOptions($target, opts, data.selectPrompt);
      }
    });
  });
});
</script>
{/literal}
