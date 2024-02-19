{if "MULTIVENDOR"|fn_allowed_for}
{include file="common/subheader.tpl" title=__("telegram.telegram")}

<div class="control-group">
    <label for="elm_company_tg_enabled" class="control-label">{__("telegram.tg_enabled")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[tg_enabled]" value="N">
        <input id="elm_company_tg_enabled" type="checkbox" name="company_data[tg_enabled]" value="Y" {if $company_data.tg_enabled == "YesNo::YES"|enum}checked="_checked"{/if}>
    </div>
</div>
{/if}
