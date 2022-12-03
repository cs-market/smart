{include file="common/subheader.tpl" title=__("autoimport") target="#company_autoexim"}
<div id="company_autoexim" class="collapsed in">
    <div class="control-group">
        <label class="control-label" for="elm_company_export_orders">{__("export_orders")}:</label>
        <div class="controls">
            <select name="company_data[export_orders]" id="elm_export_orders">
                {*foreach from=['N' => '---', 'C' => 'CSV', 'X' => 'XML'] item="type" key="value"*}
                {foreach from=['N' => '---', 'C' => 'CSV'] item="type" key="value"}
                    <option value="{$value}" {if $value == $company_data.export_orders}selected="selected"{/if}>{$type}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_company_export_statuses">{__("export_statuses")}:</label>
        <div class="controls checkbox-list">
            {include file="common/status.tpl" status=','|explode:$company_data.export_statuses display="checkboxes" name="company_data[export_statuses]"}
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_company_autoimport">{__("autoimport")}:</label>
        <div class="controls">
            <input type="hidden" name="company_data[autoimport]" value="N" />
            <input type="checkbox" name="company_data[autoimport]" id="elm_company_autoimport" value="Y" {if $company_data.autoimport == 'Y'} checked="checked"{/if} />
        </div>
    </div>
</div>
