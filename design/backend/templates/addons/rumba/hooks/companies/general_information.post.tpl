<fieldset>
    {include file="common/subheader.tpl" title=__("mobile_app_links")}
    <div class="control-group">
        <label class="control-label" for="elm_company_app_store">{__("app_store")}:</label>
        <div class="controls">
            <input type="text" name="company_data[app_store]" id="elm_company_app_store" size="32" value="{$company_data.app_store}" class="input-large">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_company_play_market">{__("play_market")}:</label>
        <div class="controls">
            <input type="text" name="company_data[play_market]" id="elm_company_play_market" size="32" value="{$company_data.play_market}" class="input-large">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_company_tracking">{__("app_gallery")}:</label>
        <div class="controls">
            <input type="text" name="company_data[app_gallery]" id="elm_company_app_gallery" size="32" value="{$company_data.app_gallery}" class="input-large">
        </div>
    </div>
</fieldset>
