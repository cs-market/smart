<div class="control-group">
    <label class="control-label" for="elm_company_tracking">{__("product_tracking")}:</label>
    <div class="controls">
        <select name="company_data[tracking]">
            <option value="{"ProductTracking::TRACK"|enum}" {if $company_data.tracking == "ProductTracking::TRACK"|enum} selected="selected" {/if}>{__("yes")}</option>
            <option value="{"ProductTracking::DO_NOT_TRACK"|enum}" {if $company_data.tracking == "ProductTracking::DO_NOT_TRACK"|enum} selected="selected" {/if}>{__("no")}</option>
        </select>
    </div>
</div>
