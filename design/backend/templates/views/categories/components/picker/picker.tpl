{*
    $item_ids                   array                   List of categories ID
    $picker_id                  string                  Picker unique ID
    $input_name                 string                  Select input name
    $multiple                   bool                    Whether to multiple selection
    $show_advanced              bool                    Show advanced button
    $autofocus                  bool                    Whether to auto focus on input
    $autoopen                   bool                    Whether to auto open dropdown
    $allow_clear                bool                    Show clear button
    $empty_variant_text         string                  Empty variant text
    $view_mode                  enum (simple|external)  View mode
    $meta                       string                  Object picker class
    $select_group_class         string                  Select group class
    $advanced_class             string                  Advanced class
    $simple_class               string                  Simple class
    $select_class               string                  Select class
    $selected_external_class    string                  Selected external class
    $selection_class            string                  Selection class
    $result_class               string                  Result class
*}

{$picker_id = $picker_id|default:uniqid()}
{$select_id=$id|default:"product_categories_`$picker_id`"}
{$input_name = $input_name|default:"object_picker_simple_`$picker_id`"}
{$multiple = $multiple|default:false}
{$show_advanced = $show_advanced|default:false}
{$autofocus = $autofocus|default:false}
{$autoopen = $autoopen|default:false}
{$allow_clear = $allow_clear|default:false}
{$item_ids = $item_ids|default:[]|array_filter}
{$empty_variant_text = $empty_variant_text|default:__("none")}
{$dropdown_css_class = $dropdown_css_class|default:""}
{$allow_add = $allow_add|default:false}
{$allow_sorting = $allow_sorting|default:false}
{$required = $required|default:false}
{$disabled = $disabled|default:false}
{$allow_multiple_created_objects = $allow_multiple_created_objects|default:false}
{$close_on_select = $close_on_select|default:true}
{$container_css_class = $container_css_class|default:".object-picker--categories"}

<input type="hidden" name="{$allow_multiple_created_objects}" value=""/>

<div class="object-picker {if $view_mode == "external"}object-picker--external{/if} object-picker--categories {$meta}" data-object-picker="object_picker_{$picker_id}">
    <div class="object-picker__simple {if $type == "list"}object-picker__simple--list{/if} object-picker__simple--categories {if $show_advanced}object-picker__simple--advanced{/if} {$simple_class}">
        {if $show_advanced && !$disabled}
            <div class="object-picker__advanced object-picker__advanced--categories {$advanced_class}">
                {include file="pickers/categories/picker.tpl"
                    picker_id="object_picker_advanced_{$picker_id}"
                    company_ids=$runtime.company_id
                    rnd="category"
                    data_id="categories"
                    view_mode="button"
                    but_meta="object-categories-add__picker object-picker__advanced-btn object-picker__advanced-btn--categories `$object_picker_advanced_btn_class`"
                    but_icon="icon-reorder"
                    but_text=false
                    multiple=true
                }
            </div>
        {/if}

        <select {if $multiple}multiple{/if}
            {if $disabled}disabled{/if}
            {if $tabindex}tabindex="{$select2_tabindex}"{/if}
            id="{$select_id}"
            name="{$input_name}"
            class="cm-object-picker object-picker__select object-picker__select--categories {$select_class}"
            data-ca-object-picker-dropdown-css-class="{$dropdown_css_class}"
            data-ca-object-picker-close-on-select="{$close_on_select}"
            data-ca-object-picker-object-type="categories"
            data-ca-object-picker-escape-html="false"
            data-ca-object-picker-ajax-url="{"categories.get_categories_list"|fn_url}"
            data-ca-object-picker-ajax-delay="250"
            data-ca-object-picker-template-result-selector="#object_picker_result_template_{$picker_id}"
            data-ca-object-picker-template-selection-selector="#object_picker_selection_template_{$picker_id}"
            data-ca-object-picker-template-selection-load-selector="#object_picker_selection_load_template_{$picker_id}"
            data-ca-object-picker-autofocus="{$autofocus|to_json}"
            data-ca-object-picker-autoopen="{$autoopen}"
            data-ca-object-picker-allow-sorting="{$allow_sorting}"
            data-ca-dispatch="{$submit_url}"
            data-ca-target-form="{$submit_form}"
            data-ca-required="{$required}"
            data-ca-object-picker-placeholder="{$empty_variant_text|escape:"javascript"}"
            data-ca-object-picker-placeholder-value=""
            data-ca-object-picker-width="{$width}"
            data-ca-object-picker-extended-picker-id="object_picker_advanced_{$picker_id}"
            data-ca-object-picker-extended-picker-text-key="{$picker_text_key}"
            {if $allow_add}
                data-ca-object-picker-enable-create-object="true"
                data-ca-object-picker-template-result-new-selector="#object_picker_result_new_selector_categories_template_{$picker_id}"
                data-ca-object-picker-template-selection-new-selector="#object_picker_selection_new_selector_categories_template_{$picker_id}"
                data-ca-object-picker-created-object-holder-selector="{$created_object_holder_selector}"
                data-ca-object-picker-allow-multiple-created-objects="{$allow_multiple_created_objects}"
            {/if}
            {if $show_empty_variant}
                data-ca-object-picker-allow-clear="{$allow_clear}"
                data-ca-object-picker-predefined-variants="{[["id" => 0, "text" => {$empty_variant_text}, "data" => ["name" => {$empty_variant_text}]]]|to_json}"
            {/if}
        >
            {foreach $item_ids as $item_id}
                <option value="{$item_id}" selected="selected"></option>
            {/foreach}
        </select>
    </div>
</div>

<script type="text/template" id="object_picker_result_template_{$picker_id}" data-no-defer="true" data-no-execute="§">
    <div class="object-picker__result object-picker__result--categories {$result_class}">
        {include file="views/categories/components/picker/item.tpl"
            type="result"
            title_pre=$selection_title_pre
            title_post=$selection_title_post
        }
    </div>
</script>

<script type="text/template" id="object_picker_selection_template_{$picker_id}" data-no-defer="true" data-no-execute="§">
    <div class="object-picker__selection object-picker__selection--categories {$selection_class}">
        {include file="views/categories/components/picker/item.tpl"
            type="selection"
            title_pre=$selection_title_pre
            title_post=$selection_title_post
        }
    </div>
</script>

<script type="text/template" id="object_picker_selection_load_template_{$picker_id}" data-no-defer="true" data-no-execute="§">
    {include file="views/categories/components/picker/item.tpl" type="load"}
</script>

<script type="text/template" id="object_picker_result_new_selector_categories_template_{$picker_id}" data-no-defer="true" data-no-execute="§">
    <div class="object-picker__result-categories object-picker__result-categories--new">
        {include file="views/categories/components/picker/item.tpl"
            type="new_item"
            title_pre=__("add")
            title_post=$selection_title_post
            help=true
        }
    </div>
</script>

<script type="text/template" id="object_picker_selection_new_selector_categories_template_{$picker_id}" data-no-defer="true" data-no-execute="§">
    <div class="object-picker__selection-categories object-picker__selection-categories--new">
        {include file="views/categories/components/picker/item.tpl"
            type="new_item"
            title_pre="$selection_title_pre"
            title_post=$selection_title_post
            help=false
            icon=false
        }
    </div>
</script>
