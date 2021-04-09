{$marketplace = "store"}

{if "MULTIVENDOR"|fn_allowed_for}
    {$marketplace = "marketplace"}
{/if}

{include file="common/subheader.tpl" title=__("commerceml.main_information")}

<table class="table table-middle table--relative table-objects table-striped">
    <thead>
        <tr>
            <th>{__("commerceml.field_in_$marketplace")}</th>
            <th>{__("commerceml.field_in_crm")}</th>
            <th class="commerceml-sync-data-setting-custom-field">{__("commerceml.custom_field_name")}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_product_name_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_product_name_source" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_product_name_source]">
                    <option value="name" {if empty($sync_settings.catalog.convertor_product_name_source) || $sync_settings.catalog.convertor_product_name_source == "name"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_product_name_source.name")}</option>
                    <option value="full_name" {if $sync_settings.catalog.convertor_product_name_source == "full_name"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_product_name_source.full_name")}</option>
                </select>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_product_code_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_product_code_source" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_product_code_source]">
                    <option value="article" {if empty($sync_settings.catalog.convertor_product_code_source) || $sync_settings.catalog.convertor_product_code_source == "article"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_product_code_source.article")}</option>
                    <option value="code" {if $sync_settings.catalog.convertor_product_name_source == "code"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_product_code_source.code")}</option>
                    <option value="bar" {if $sync_settings.catalog.convertor_product_name_source == "bar"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_product_code_source.bar")}</option>
                </select>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_full_description_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_full_description_source" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_full_description_source]">
                    <option value="none" {if empty($sync_settings.catalog.convertor_full_description_source) || $sync_settings.catalog.convertor_full_description_source == "none"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_full_description_source.none")}</option>
                    <option value="description" {if $sync_settings.catalog.convertor_full_description_source == "description"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_full_description_source.description")}</option>
                    <option value="html_description" {if $sync_settings.catalog.convertor_full_description_source == "html_description"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_full_description_source.html_description")}</option>
                    <option value="full_name" {if $sync_settings.catalog.convertor_full_description_source == "full_name"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_full_description_source.full_name")}</option>
                </select>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_short_description_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_short_description_source" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_short_description_source]">
                    <option value="none" {if empty($sync_settings.catalog.convertor_short_description_source) || $sync_settings.catalog.convertor_short_description_source == "none"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_short_description_source.none")}</option>
                    <option value="description" {if $sync_settings.catalog.convertor_short_description_source == "description"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_short_description_source.description")}</option>
                    <option value="html_description" {if $sync_settings.catalog.convertor_short_description_source == "html_description"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_short_description_source.html_description")}</option>
                    <option value="full_name" {if $sync_settings.catalog.convertor_short_description_source == "full_name"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_short_description_source.full_name")}</option>
                </select>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_page_title_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_page_title_source" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_page_title_source]">
                    <option value="none" {if empty($sync_settings.catalog.convertor_page_title_source) || $sync_settings.catalog.convertor_page_title_source == "none"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_page_title_source.none")}</option>
                    <option value="name" {if $sync_settings.catalog.convertor_page_title_source == "name"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_page_title_source.name")}</option>
                    <option value="full_name" {if $sync_settings.catalog.convertor_page_title_source == "full_name"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_page_title_source.full_name")}</option>
                </select>
            </td>
            <td></td>
        </tr>

        {if $runtime.company_id == 0 || $settings.Vendors.allow_vendor_manage_features == "YesNo::YES"|enum}
            <tr>
                <td class="commerceml-sync-data-setting-name">{__("commerceml.cml_catalog_convertor_brand_source")}</td>
                <td class="commerceml-sync-data-setting-variants">
                    <select id="elm_sync_data_commerceml_cml_catalog_convertor_brand_source" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_brand_source]">
                        <option value="none" {if empty($sync_settings.catalog.convertor_brand_source) || $sync_settings.catalog.convertor_brand_source == "none"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_brand_source.none")}</option>
                        <option value="manufacturer" {if $sync_settings.catalog.convertor_brand_source == "manufacturer"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_brand_source.manufacturer")}</option>
                        <option value="property" {if $sync_settings.catalog.convertor_brand_source == "property"}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_brand_source.property")}</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_brand_property_source]" id="elm_sync_data_commerceml_cml_catalog_convertor_brand_property_source" size="32" value="{$sync_settings.catalog.convertor_brand_property_source}" class="input-large" />
                </td>
            </tr>
        {/if}

        <tr>
            <td class="commerceml-sync-data-setting-name">{__("commerceml.cml_catalog_convertor_promo_text_property_source")}</td>
            <td class="commerceml-sync-data-setting-variants">
                <input type="text" id="elm_sync_data_commerceml_cml_catalog_convertor_promo_text_property_source" size="32" value="{__("commerceml.cml_catalog_convertor_promo_text_property_source.custom_field")}" class="input-large" disabled="disabled" />
            </td>
            <td>
                <input type="text" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_promo_text_property_source]" id="elm_sync_data_commerceml_cml_catalog_convertor_promo_text_property_source" size="32" value="{$sync_settings.catalog.convertor_promo_text_property_source}" class="input-large" />
            </td>
        </tr>
    </tbody>
</table>

{include file="common/subheader.tpl" title=__("commerceml.shipping_information")}

<div>
    <div class="well">
        {__("commerceml.shipping_information_info_block")}
    </div>
</div>

<table width="100%" class="table table-middle table--relative table-objects table-striped">
    <thead>
        <tr>
            <th>{__("commerceml.field_in_$marketplace")}</th>
            <th>{__("commerceml.fields_in_crm")}</th>
            <th>{__("commerceml.show_as_product_feature")}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_weight_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_weight_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_weight_property_source_list]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_weight_property_source_list}</textarea>
            </td>
            <td>
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_display_weight]" value="N">
                <input id="elm_sync_data_commerceml_cml_catalog_convertor_display_weight" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_display_weight]" value="Y" {if $sync_settings.catalog.convertor_display_weight == "Y"}checked="checked"{/if}>
            </td>
        </tr>
        <tr>
            <td>
                {__("commerceml.cml_catalog_convertor_free_shipping_property_source_list")}
                <div class="muted description">{__("commerceml.cml_catalog_convertor_free_shipping_property_source_list.tooltip")}</div>
            </td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_free_shipping_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_free_shipping_property_source_list]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_free_shipping_property_source_list}</textarea>
            </td>
            <td>
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_display_free_shipping]" value="N">
                <input id="elm_sync_data_commerceml_cml_catalog_convertor_display_free_shipping" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_display_free_shipping]" value="Y" {if $sync_settings.catalog.convertor_display_free_shipping == "Y"}checked="checked"{/if}>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_shipping_cost_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_shipping_cost_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_shipping_cost_property_source_list]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_shipping_cost_property_source_list}</textarea>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_number_of_items_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_number_of_items_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_number_of_items_property_source_list]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_number_of_items_property_source_list}</textarea>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_box_length_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_box_length_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_box_length_property_source_list]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_box_length_property_source_list}</textarea>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_box_width_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_box_width_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_box_width_property_source_list]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_box_width_property_source_list}</textarea>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_box_height_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_box_height_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_box_height_property_source_list]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_box_height_property_source_list}</textarea>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>

{if "ULTIMATE"|fn_allowed_for || $runtime.company_id == 0 || $settings.Vendors.allow_vendor_manage_features == "YesNo::YES"|enum}
    {include file="common/subheader.tpl" title=__("commerceml.product_features")}

    <div>
        <div class="well">
            {__("commerceml.product_features_info_block")}
        </div>
    </div>

    <fieldset>
        <div class="control-group">
            <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_convertor_property_allowlist">{__("commerceml.cml_catalog_convertor_property_allowlist")}:</label>
            <div class="controls">
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_property_allowlist" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_property_allowlist]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_property_allowlist}</textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_convertor_property_blocklist">{__("commerceml.cml_catalog_convertor_property_blocklist")}:</label>
            <div class="controls">
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_property_blocklist" name="sync_data_settings[{$sync_provider_id}][catalog][convertor_property_blocklist]" rows="5" cols="19" class="input-large user-success">{$sync_settings.catalog.convertor_property_blocklist}</textarea>
            </div>
        </div>
    </fieldset>
{/if}