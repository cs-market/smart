<fieldset>
    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_allow_import_offers">{__("commerceml.cml_catalog_allow_import_offers")}:</label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][allow_import_offers]" value="N">
            <input id="elm_sync_data_commerceml_cml_catalog_allow_import_offers" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][allow_import_offers]" value="Y" {if empty($sync_settings.catalog.allow_import_offers) || $sync_settings.catalog.allow_import_offers == "Y"}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_hide_out_of_stock_products">{__("commerceml.cml_catalog_hide_out_of_stock_products")}:</label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][hide_out_of_stock_products]" value="N">
            <input id="elm_sync_data_commerceml_cml_catalog_hide_out_of_stock_products" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][hide_out_of_stock_products]" value="Y" {if $sync_settings.catalog.hide_out_of_stock_products == "Y"}checked="checked"{/if}>
        </div>
    </div>

    {if "ULTIMATE"|fn_allowed_for || $runtime.company_id == 0}
        <div class="control-group setting-wide">
            <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_allow_import_categories">{__("commerceml.cml_catalog_allow_import_categories")}:</label>
            <div class="controls">
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][allow_import_categories]" value="N">
                <input id="elm_sync_data_commerceml_cml_catalog_allow_import_categories" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][allow_import_categories]" value="Y" {if empty($sync_settings.catalog.allow_import_categories) || $sync_settings.catalog.allow_import_categories == "Y"}checked="checked"{/if}>
            </div>
        </div>
    {/if}

    {if "ULTIMATE"|fn_allowed_for || $runtime.company_id == 0 || $settings.Vendors.allow_vendor_manage_features == "YesNo::YES"|enum}
        <div class="control-group setting-wide">
            <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_allow_import_features">{__("commerceml.cml_catalog_allow_import_features")}:</label>
            <div class="controls">
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][allow_import_features]" value="N">
                <input id="elm_sync_data_commerceml_cml_catalog_allow_import_features" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][allow_import_features]" value="Y" {if empty($sync_settings.catalog.allow_import_features) || $sync_settings.catalog.allow_import_features == "Y"}checked="checked"{/if}>
            </div>
        </div>
    {/if}

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_import_images_as_additional">{__("commerceml.cml_catalog_import_images_as_additional")}:</label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][import_images_as_additional]" value="N">
            <input id="elm_sync_data_commerceml_cml_catalog_import_images_as_additional" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][import_images_as_additional]" value="Y" {if $sync_settings.catalog.import_images_as_additional == "Y"}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_new_product_status">{__("commerceml.cml_catalog_new_product_status")}:</label>
        <div class="controls">
            <select id="elm_sync_data_commerceml_cml_catalog_new_product_status" name="sync_data_settings[{$sync_provider_id}][catalog][new_product_status]">
                <option value="A" {if empty($sync_settings.catalog.new_product_status) || $sync_settings.catalog.new_product_status == "A"}selected="selected"{/if}>{__("commerceml.cml_catalog_new_product_status.a")}</option>
                <option value="H" {if $sync_settings.catalog.new_product_status == "H"}selected="selected"{/if}>{__("commerceml.cml_catalog_new_product_status.h")}</option>
                <option value="D" {if $sync_settings.catalog.new_product_status == "D"}selected="selected"{/if}>{__("commerceml.cml_catalog_new_product_status.d")}</option>
            </select>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_default_lang">{__("commerceml.cml_default_lang")}:</label>
        <div class="controls">
            <select id="elm_sync_data_commerceml_cml_default_lang" name="sync_data_settings[{$sync_provider_id}][default_lang]">
                {foreach $languages as $lang_code => $language}
                    <option value="{$lang_code}" {if $sync_settings.default_lang == $lang_code}selected="selected"{/if}>{$language.name}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_default_category_id">{__("commerceml.cml_catalog_default_category_id")}:<div class="muted description">{__("commerceml.cml_catalog_default_category_id.tooltip")}</div></label>
        <div class="controls">
            {include file="views/categories/components/picker/picker.tpl"
                input_name="sync_data_settings[{$sync_provider_id}][catalog][default_category_id]"
                multiple=false
                show_advanced=true
                show_empty_variant=true
                allow_clear=true
                item_ids=[$sync_settings.catalog.default_category_id]
                dropdown_css_class="object-picker__dropdown--categories"
            }
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_import_mode">{__("commerceml.cml_catalog_import_mode")}:</label>
        <div class="controls">
            <select id="elm_sync_data_commerceml_cml_catalog_import_mode" name="sync_data_settings[{$sync_provider_id}][catalog][import_mode]">
                <option value="all" {if empty($sync_settings.catalog.import_mode) || $sync_settings.catalog.import_mode == "all"}selected="selected"{/if}>{__("commerceml.cml_catalog_import_mode.all")}</option>
                <option value="only_new" {if $sync_settings.catalog.import_mode == "only_new"}selected="selected"{/if}>{__("commerceml.cml_catalog_import_mode.only_new")}</option>
                <option value="only_existing" {if $sync_settings.catalog.import_mode == "only_existing"}selected="selected"{/if}>{__("commerceml.cml_catalog_import_mode.only_existing")}</option>
                <option value="none" {if $sync_settings.catalog.import_mode == "none"}selected="selected"{/if}>{__("commerceml.cml_catalog_import_mode.none")}</option>
            </select>
        </div>
    </div>

    {include file="common/subheader.tpl" title=__("commerceml.automatic_matching_title")}

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_allow_matching_category_by_name">{__("commerceml.cml_catalog_allow_matching_category_by_name")}:</label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][allow_matching_category_by_name]" value="N">
            <input id="cml_catalog_allow_matching_category_by_name" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][allow_matching_category_by_name]" value="Y" {if $sync_settings.catalog.allow_matching_category_by_name == "Y"}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_allow_matching_product_by_product_code">{__("commerceml.cml_catalog_allow_matching_product_by_product_code")}:</label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][allow_matching_product_by_product_code]" value="N">
            <input id="elm_sync_data_commerceml_cml_catalog_allow_matching_product_by_product_code" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][allow_matching_product_by_product_code]" value="Y" {if $sync_settings.catalog.allow_matching_product_by_product_code == "Y"}checked="checked"{/if}>
        </div>
    </div>
</fieldset>