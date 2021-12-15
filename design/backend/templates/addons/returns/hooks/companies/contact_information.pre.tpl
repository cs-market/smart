<div class="control-group">
    <label class="control-label" for="elm_company_support_returns">{__("support_returns")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[support_returns]" value="N" />
        <input type="checkbox" name="company_data[support_returns]" id="elm_company_support_returns" value="Y" {if $company_data.support_returns === "YesNo::YES"|enum}checked="checked"{/if} />
    </div>
</div>
