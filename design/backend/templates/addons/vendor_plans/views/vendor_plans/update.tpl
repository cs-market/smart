{assign var="id" value=0}
{if $plan.plan_id}
    {assign var="id" value=$plan.plan_id}
{/if}

{$action_context = $action_context|default:$smarty.request._action_context}

<div id="content_plan_{$id}">

<form action="{""|fn_url}"
      method="post"
      enctype="multipart/form-data"
      name="update_plan_form_{$id}"
      class="{if $ajax_mode}cm-ajax {/if}form-horizontal form-edit"
      data-ca-vendor-plans-is-update-form="{if $id}true{else}false{/if}"
      data-ca-vendor-plans-selected-storefronts="{$plan.storefront_ids|json_encode}"
      data-ca-vendor-plans-affected-vendors="{$affected_vendors|json_encode}"
      data-ca-vendor-plans-vendors-update-dialog-id="update_plan_vendors_update_dialog_{$id}"
      {if $action_context}data-ca-ajax-done-event="ce.{$action_context}.vendor_plan_save"{/if}
>
<input type="hidden" name="plan_id" value="{$id}" />

{capture name="tabsbox"}
    
    <div id="content_plan_general_{$id}">
        {hook name="vendor_plans:details_general_content"}

        <div class="control-group">
            <label class="control-label cm-required" for="elm_plan_{$id}">{__("name")}:</label>
            <div class="controls">
                <input type="text" id="elm_plan_{$id}" name="plan_data[plan]" size="35" value="{$plan.plan}" class="input-large" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_is_default_{$id}">{__("vendor_plans.best_choise")}:</label>
            <div class="controls">
                <input type="hidden" name="plan_data[is_default]" value="0" />
                <input type="checkbox" id="elm_is_default_{$id}" name="plan_data[is_default]" size="10" value="1"{if $plan.is_default} checked="checked"{/if} />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_plan_description_{$id}">{__("description")}:</label>
            <div class="controls">
                 <textarea id="elm_plan_description_{$id}"
                    name="plan_data[description]"
                    cols="55"
                    rows="8"
                    class="cm-wysiwyg input-large"
                >{$plan.description}</textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_position_{$id}">{__("position")}:</label>
            <div class="controls">
                <input type="text" id="elm_position_{$id}" name="plan_data[position]" size="10" value="{$plan.position}" class="input-text-short" />
            </div>
        </div>

        {include file="common/select_status.tpl" input_name="plan_data[status]" id="plan_data_`$id`" obj=$plan hidden=true}

        {/hook}
    </div>

    <div id="content_plan_commission_{$id}">
        
        <div class="control-group">
            <label class="control-label" for="elm_price_{$id}">{__("price")} ({$currencies.$primary_currency.symbol nofilter}):</label>
            <div class="controls">
                <input type="text" id="elm_price_{$id}" name="plan_data[price]" size="10" value="{$plan.price}" class="input-text-short" />
                <select name="plan_data[periodicity]" class="input-small">
                    {foreach from=$periodicities key=key item=item}
                        <option value="{$key}"{if $key == $plan.periodicity} selected="selected"{/if}>{$item}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_commission_{$id}">{__("vendor_plans.transaction_fee")}:</label>
            <div class="controls">
                <input id="elm_commission_{$id}" type="text" name="plan_data[commission]" class="input-mini" value="{$plan.commission}" size="4"> % + <input type="text" name="plan_data[fixed_commission]" value="{$plan.fixed_commission}" class="input-mini" size="4"> {$currencies.$primary_currency.symbol nofilter}</div>
        </div>
    </div>

    <div id="content_plan_restrictions_{$id}">

        {hook name="vendor_plans:update_restrictions"}

            <div class="control-group">
                <label class="control-label" for="elm_products_limit_{$id}">{__("vendor_plans.products_limit")}:</label>
                <div class="controls">
                    <input type="text" id="elm_products_limit_{$id}" name="plan_data[products_limit]" size="10" value="{$plan.products_limit}" class="input-text-short" />
                    <p class="muted description">{__("vendor_plans.products_limit_tooltip")}</p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="elm_revenue_limit_{$id}">{__("vendor_plans.revenue_up_to")} ({$currencies.$primary_currency.symbol nofilter}):</label>
                <div class="controls">
                    <input type="text" id="elm_revenue_limit_{$id}" name="plan_data[revenue_limit]" size="10" value="{$plan.revenue_limit}" class="input-text-short" />
                    <p class="muted description">{__("vendor_plans.revenue_up_to_tooltip")}</p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="elm_vendor_store_{$id}">{__("vendor_plans.vendor_store")}:</label>
                <div class="controls">
                    <input type="hidden" name="plan_data[vendor_store]" value="0" />
                    <input type="checkbox" id="elm_vendor_store_{$id}" name="plan_data[vendor_store]" size="10" value="1"{if $plan.vendor_store} checked="checked"{/if} />
                    <p class="muted description">{__("vendor_plans.vendor_store_tooltip")}</p>
                </div>
            </div>

        {/hook}

    </div>
    
    <div id="content_plan_categories_{$id}" class="hidden">
        {$item_ids = ($plan.categories && !$plan.categories|is_array) ? (","|explode:$plan.categories) : ($plan.categories)}

        {hook name="vendor_plans:details_categories"}
            <input type="hidden" name="plan_data[categories]" class="cm-picker-value"/>
            {include file="views/categories/components/picker/picker.tpl"
                input_name="plan_data[categories][]"
                simple_class="cm-field-container"
                multiple=true
                item_ids=$item_ids
                show_advanced=true
                view_mode="external"
                result_class="object-picker__result--inline"
                selection_class="object-picker__selection--product-categories"
                close_on_select=false
                allow_multiple_created_objects=true
            }
        {/hook}
    </div>

    <div id="content_plan_storefronts_{$id}" class="hidden">
        {hook name="vendor_plans:details_storefronts"}
            {include file="pickers/storefronts/picker.tpl"
                multiple=true
                input_name="plan_data[storefronts]"
                item_ids=$plan.storefronts
                data_id="storefront_ids"
                use_keys="N"
                but_meta="pull-right"
            }
        {/hook}
    </div>

    {hook name="vendor_plans:details_tabs_content"}{/hook}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox}

{if $id}
    {include file="addons/vendor_plans/views/vendor_plans/components/storefronts_update_for_plan_dialog.tpl"
        plan_id = $id
        affected_vendors_count = $plan.companies_count
    }
{/if}

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[vendor_plans.update]" cancel_action="close" save=$id}
</div>

</form>
<!--content_plan_{$id}--></div>
