<tr>
    <td {if $parent_record}class="right"{/if}>{$record.entity_name}</td>
    <td><code>{$record.entity_id}</code></td>
    <td>
        <input type="hidden" name="records[{$record.entity_type}][{$record.entity_id}][entity_id]" value="{$record.entity_id}">
        <input type="hidden" name="records[{$record.entity_type}][{$record.entity_id}][local_id]" value="">

        {hook name="commerceml:map_record"}
        {if $record.entity_type === "\Tygh\Addons\CommerceML\Dto\CategoryDto::REPRESENT_ENTITY_TYPE"|constant}
            {if $import_settings.allow_import_categories}
                {$empty_variant = __("commerceml.map.category.create")}
            {else}
                {$empty_variant = __("commerceml.map.category.use_default")}
            {/if}

            {include file="views/categories/components/picker/picker.tpl"
                input_name="records[{$record.entity_type}][{$record.entity_id}][local_id]"
                multiple=false
                show_empty_variant=true
                show_advanced=true
                allow_clear=true
                item_ids=[$record.local_id]
                empty_variant_text=$empty_variant
                dropdown_css_class="object-picker__dropdown--categories"
            }
        {elseif $record.entity_type === "\Tygh\Addons\CommerceML\Dto\ProductFeatureDto::REPRESENT_ENTITY_TYPE"|constant}
            {if $import_settings.allow_import_features}
                {$empty_variant = __("commerceml.map.product_feature.create")}
            {else}
                {$empty_variant = __("commerceml.map.product_feature.skip")}
            {/if}

            <div class="cm-commerceml-map-product-feature" data-external-id="{$record.entity_id}">
                {include file="views/product_features/components/picker/picker.tpl"
                    input_name="records[{$record.entity_type}][{$record.entity_id}][local_id]"
                    empty_variant_text=$empty_variant
                    item_ids=[$record.local_id]
                    allow_clear=true
                    search_data=["get_only_selectable" => true]
                }
            </div>
        {elseif $record.entity_type === "\Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE"|constant}
            {if $import_settings.allow_import_features}
                {$empty_variant = __("commerceml.map.product_feature_variant.create")}
            {else}
                {$empty_variant = __("commerceml.map.product_feature_variant.skip")}
            {/if}

            <div class="cm-commerceml-map-product-feature-variant" data-feature-external-id="{$parent_record.entity_id}">
                {include file="views/product_features/components/variants_picker/picker.tpl"
                    input_name="records[{$record.entity_type}][{$record.entity_id}][local_id]"
                    empty_variant_text=$empty_variant
                    item_ids=[$record.local_id]
                    feature_id=$parent_record.local_id
                    allow_clear=true
                }
            </div>
        {else}
            <select name="records[{$record.entity_type}][{$record.entity_id}][local_id]">
                <option value=""> --- </option>
                {foreach $items as $local_id => $local_name}
                    <option value="{$local_id}" {if $local_id == $record.local_id}selected{/if}>{$local_name}</option>
                {/foreach}
            </select>
        {/if}
        {/hook}
    </td>
</tr>