<tr class="import-field" id="field_{$id}">
    <td class="import-field__name" data-th="{__("advanced_import.column_header")}">
        <input type="hidden"
               name="fields[{$name}][name]"
               value="{$name}"
        />
        {$name}
    </td>
    <td class="import-field__related_object" data-th="{__("advanced_import.product_property", ["[product]" => $smarty.const.PRODUCT_NAME])}">
        <input type="hidden"
               name="fields[{$name}][related_object_type]"
               id="elm_field_related_object_type_{$id}"
        />
        <select
                data-ca-advanced-import-field-related-object-selector="true"
                data-ca-advanced-import-field-id="{$id}"
                id="elm_import_field_{$id}"
                name="fields[{$name}][related_object]"
                class="input-hidden cm-object-selector import-field__related_object-select"
                data-width="100%"
                data-ca-enable-search="true"
                data-ca-placeholder="-{__("none")}-"
                data-ca-allow-clear="true">
            <option
                data-ca-advanced-import-field-related-object-type="skip">
            </option>
            {foreach $relations as $related_object_type => $group_info}
                <optgroup label="{$group_info.description}">
                    {foreach $group_info.fields as $object_name => $object}
                        {if $object.hidden|default:false}{continue}{/if}
                        <option data-ca-advanced-import-field-related-object-type="{$related_object_type}"
                                value="{$object_name}"
                                {if $object.required}class="selectbox-highlighted"{/if}
                                {if $preset.fields.$name.related_object_type == $related_object_type
                                    && $preset.fields.$name.related_object == $object_name}
                                    selected="selected"
                                {/if}
                        >{strip}
                            {if $object.show_name}
                                {$object_name}
                            {/if}
                            {if $object.show_name && $object.show_description} ({/if}
                            {if $object.show_description}
                                {$object.description}
                            {/if}
                            {if $object.show_name && $object.show_description}){/if}
                        {/strip}</option>
                        {if $object.has_more|default:false}
                            <option disabled="disabled">{__("advanced_import.coming_soon")}</option>
                        {/if}
                    {/foreach}
                </optgroup>
            {/foreach}
        </select>
    </td>
    <td class="import-field__preview" data-th="{__("advanced_import.first_line_import_value")}">
        {if $preview}
            {foreach $preview as $preview_item}
                <div class="import-field__preview-wrapper cm-show-more__wrapper">
                    <div class="import-field__preview-value cm-show-more__block">
                        {if $preset.fields.$name.modifier}
                                {$preview_item.$name.modified}
                            <div class="import-field__preview-info">
                                <a class="import-field__preview-button"><i class="icon-question-sign"></i></a>
                                <div class="popover fade bottom in">
                                    <div class="arrow"></div>
                                    <h3 class="popover-title">{__("advanced_import.modifier_title")}</h3>
                                    <div class="popover-content">
                                        <div class="import-field__preview--original">
                                            <strong>{__("advanced_import.example_imported_title")}</strong>
                                            <p>{$preview_item.$name.original}</p>
                                        </div>
                                        <div class="import-field__preview--modified">
                                            <strong>{__("advanced_import.example_modified_title")}</strong>
                                            <p>{$preview_item.$name.modified}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {else}
                            {$preview_item.$name.original}
                        {/if}
                    </div>
                </div>
            {/foreach}
            <div class="cm-show-more__btn">
                <a href="#" class="cm-show-more__btn-more">{__("advanced_import.show_more")}</a>
                <a href="#" class="cm-show-more__btn-less">{__("advanced_import.show_less")}</a>
            </div>
        {/if}
    </td>
    <td class="import-field__modifier" data-th="{__("advanced_import.modifier")}">
        <div class="control-group">
            <input type="text"
                   name="fields[{$name}][modifier]"
                   class="input-text input-hidden import-field__modifier-input"
                   placeholder="{__("advanced_import.modifier")}"
                   value="{$preset.fields.$name.modifier}"
                   data-ca-advanced-import-original-value="{$preview_item.$name.original|default:""}"
                   data-ca-advanced-import-element="modifier"
            />
        </div>
    </td>
<!--field_{$id}--></tr>