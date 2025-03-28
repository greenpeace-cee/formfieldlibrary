{crmScope extensionKey="formfieldlibrary"}
{if (!isset($isAVersion) || $isAVersion)}
    {assign var="field_name" value=$field.name}
{/if}
{if (isset($isAVersion) && !$isAVersion)}
    {assign var="field_name" value=$field.name_ab}
{/if}
{assign var="template" value="`$field_name`_template"}
{assign var="html_message" value="`$field_name`_html_message"}
{assign var="subject" value="`$field_name`_subject"}
{assign var="plaintext" value="`$field_name`_plaintext"}
{capture assign="tokens"}{literal}{$tokens_{/literal}{$field_name}{literal}|@json_encode}{/literal}{/capture}

<div class="crm-accordion-wrapper crm-html_email-accordion ">
  <div class="crm-accordion-header">{$field.title}</div>
  <div class="crm-accordion-body">
    {if $field.configuration.enable_template}
    <div class="crm-section">
        <div class="label">{$form.$template.label}</div>
        <div class="content">
          {$form.$template.html}
        </div>
        <div class="clear"></div>
    </div>
    {/if}
    {if $field.configuration.enable_subject}
    <div class="crm-section">
      <div class="label">{$form.$subject.label}</div>
      <div class="content">
        {$form.$subject.html}
        <input class="crm-token-selector big" data-field="{$subject}" />
      </div>
      <div class="clear"></div>
    </div>
    {/if}
    <div class="crm-section">
      <div class="label">{$form.$html_message.label}</div>
      <div class="content">
        <input class="crm-token-selector big" data-field="{$html_message}" />
        {$form.$html_message.html}
      </div>
      <div class="clear"></div>
    </div>
    {if $field.configuration.enable_plaintext}
      <div class="crm-section">
        <div class="label">{$form.$plaintext.label}</div>
        <div class="content">
          <input class="crm-token-selector big" data-field="{$plaintext}" /> <br />
          {$form.$plaintext.html}
        </div>
        <div class="clear"></div>
      </div>
    {/if}
  </div>
</div>
{if (!isset($isAVersion) || $isAVersion)}
{literal}
<script type="text/javascript">
  function selectTemplate_{/literal}{$field_name}{literal}( val, html_message, text_message, subject) {
    if (subject) {
      document.getElementById(subject).value ="";
    }
    if (text_message && document.getElementById(text_message)) {
      document.getElementById(text_message).value ="";
    }
    CRM.wysiwyg.setVal('#' + html_message, '');

    if (val) {
      CRM.api3('MessageTemplate', 'getsingle', {"id": val}).then(function (data) {
        if (subject) {
          document.getElementById(subject).value = data.msg_subject;
        }
        if (text_message && document.getElementById(text_message)) {
          document.getElementById(text_message).value = data.msg_text;
        }
        CRM.wysiwyg.setVal('#' + html_message, data.msg_html);
      });
    }
  }

CRM.$(function($) {
  function insertToken() {
    var token = $(this).val();
    var field = $(this).data('field');
    CRM.wysiwyg.insert('#' + field, token);
    $(this).select2('val', '');
  }

  var form = $('form.{/literal}{$form.formClass}{literal}');
  $('input.crm-token-selector', form)
  .addClass('crm-action-menu fa-code')
  .change(insertToken)
  .crmSelect2({
    data: {/literal}{eval var=$tokens}{literal},
    placeholder: '{/literal}{ts escape='js'}Tokens{/ts}{literal}'
  });
});
</script>
{/literal}
{/if}
{/crmScope}
