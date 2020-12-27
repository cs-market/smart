{if $product_group}
    {assign var="id" value=$product_group.group_id|lower}
{else}
    {assign var="id" value="0"}
{/if}

{if "ULTIMATE"|fn_allowed_for && !$runtime.company_id}
    {assign var="show_update_for_all" value=true}
{/if}

{if "ULTIMATE"|fn_allowed_for && $settings.Stores.default_state_update_for_all == 'not_active' && !$runtime.simple_ultimate && !$runtime.company_id}
    {assign var="disable_input" value=true}
{/if}

<div id="content_group{$st}">

<form action="{""|fn_url}" enctype="multipart/form-data" method="post" name="update_status_{$st}_form" class="form-horizontal">
<input type="hidden" name="group_id" value="{$id}">

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
<fieldset>
    <div class="control-group">
        <label for="group_{$id}" class="cm-required control-label">{__("name")}:</label>
        <div class="controls">
            <input type="text" size="70" id="group_{$id}" name="product_group_data[group]" value="{$product_group.group}" class="input-large">
        </div>
    </div>

    <div class="control-group">
        <label for="min_order_{$id}" class="control-label">{__("order_split.min_order")}:</label>
        <div class="controls">
            <input type="text" size="70" id="min_order_{$id}" name="product_group_data[min_order]" value="{$product_group.min_order}" class="input-large">
        </div>
    </div>

    {include file="common/select_status.tpl" input_name="product_group_data[status]" id="elm_product_group_status_{$id}" obj=$product_group}
</fieldset>
</div>


<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[product_groups.update]" cancel_action="close" save=$id}
</div>

</form>
<!--content_group{$id}--></div>
