<div class="control-group">
    <label class="control-label" for="elm_company_package_switcher">{__("product_packages.package_switcher")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[package_switcher]" value="N" />
        <input type="checkbox" name="company_data[package_switcher]" id="elm_company_package_switcher" value="Y" {if $company_data.package_switcher == 'Y'} checked="checked"{/if} />
    </div>
</div>
