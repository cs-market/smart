{*
    $item_ids                   array                   List of product ID
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
{$picker_text_key = $picker_text_key|default:"value"}
{$input_name = $input_name|default:"object_picker_simple_`$picker_id`"}
{$multiple = $multiple|default:false}
{$show_advanced = $show_advanced|default:true}
{$autofocus = $autofocus|default:false}
{$autoopen = $autoopen|default:false}
{$allow_clear = $allow_clear|default:false}
{$item_ids = $item_ids|default:[]|array_filter}
{$dropdown_css_class = "object-picker__dropdown object-picker__dropdown--usergroups `$dropdown_css_class`"|default:"object-picker__dropdown object-picker__dropdown--usergroups"}

{if $multiple && $show_advanced}
    {$empty_variant_text = $empty_variant_text|default:__("type_to_search_or_click_button")}
{else}
    {$empty_variant_text = $empty_variant_text|default:__("none")}
{/if}

{$usergroups = ["type"=>"C", "status"=>["A", "H"], "include_default" => true, "company_id" => $company_id]|fn_get_usergroups:$smarty.const.DESCR_SL}

{if !($item_ids|is_array)} 
    {$item_ids = ","|explode:$item_ids}
{/if}

<div class="object-picker {if $view_mode == "external"}object-picker--external{/if} object-picker--usergroups {$meta}" data-object-picker="object_picker_{$picker_id}">
    <div class="object-picker__select-group object-picker__select-group--usergroups {$select_group_class}">
        <div class="object-picker__simple {if $type == "list"}object-picker__simple--list{/if} {if $show_advanced}object-picker__simple--advanced{/if} object-picker__simple--usergroups {$object_picker_simple}">
            <select {if $multiple}multiple{/if}
                    name="{$input_name}"
                    class="cm-object-picker object-picker__select object-picker__select--usergroups {$select_class}"
                    data-ca-object-picker-object-type="usergroup"
                    data-ca-object-picker-escape-html="false"

                    data-ca-object-picker-autofocus="{$autofocus|to_json}"
                    data-ca-object-picker-autoopen="{$autoopen}"
                    data-ca-dispatch="{$submit_url}"
                    data-ca-target-form="{$submit_form}"
                    data-ca-object-picker-placeholder="{__("type_to_search")|escape:"javascript"}"
                    data-ca-object-picker-placeholder-value=""
                    data-ca-object-picker-extended-picker-id="object_extended_picker_{$picker_id}"
                    data-ca-object-picker-dropdown-css-class="{$dropdown_css_class}"
                    data-ca-object-picker-enable-permanent-placeholder="true"
                    {if $view_mode == "external"}
                        data-ca-object-picker-external-container-selector="#object_picker_external_seleceted_products_{$picker_id}"
                    {/if}
                    {if $dropdown_parent_selector}
                        data-ca-object-picker-dropdown-parent-selector="{$dropdown_parent_selector}"
                    {/if}
                    {if $disabled}
                        disabled="disabled"
                    {/if}
            >
                {foreach from=$usergroups key="usergroup_id" item="variant"}
                    <option value="{$usergroup_id}" {if $usergroup_id|in_array:$item_ids} selected="selected" {/if}>{$variant.usergroup}</option>
                {/foreach}
            </select>            
        </div>
    </div>
</div>
