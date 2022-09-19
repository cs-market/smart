{if !$name}
    {if $user_price_search.name}
        {$name = $user_price_search.name}
    {else}
        {$name = 'user_price'}
    {/if}
{/if}
{if !$current_url}
    {$current_url = $config.current_url}
{/if}

<div id="user_price_search">

    <div class="">
        <label for="user_price_pname">{__("customer")}</label>
        <input type="text" name="user_price_pname" id="user_price_pname" value="{$user_price_search.pname}" size="30" />
    </div>
    {include file="buttons/button.tpl"
        but_text="{__("search")}"
        but_role="action"
        but_id="user_price_search_button"
    }
    <script type="text/javascript" class="cm-ajax-force">
        (function($, _){
            $(document).ready(function(){
                const product_id = {$user_price_search.product_id};
                const name = "{$name}";
                $('#user_price_search_button').click(function(){
                    Tygh.$.ceAjax('request',
                        fn_url("products.get_user_price"),
                        {
                            method: 'get',
                            result_ids: 'user_price_*',
                            hidden: true,
                            data: {
                                product_id: product_id,
                                name: name,
                                pname: $("#user_price_pname").val()
                            }
                        }
                    );
                });
                $('#user_price_pagination .cm-delete-price-row').click(function() {
                    jelm = $(this);
                    if (jelm.is('tr') || jelm.hasClass('cm-row-item')) {
                        holder = jelm;
                    } else if (jelm.parents('.cm-row-item').length) {
                        holder = jelm.parents('.cm-row-item:first');
                    } else if (jelm.parents('tr').length && !$('.cm-picker', jelm.parents('tr:first')).length) {
                        holder = jelm.parents('tr:first');
                    }
                    if (holder.length) {
                        holder.addClass('cm-opacity');
                        $('.input-user-price', holder).val('');
                    }
                });
            });
        }(Tygh.$, Tygh));
    </script>
<!--user_price_search--></div>

{include file="common/pagination.tpl"
    div_id=user_price_pagination
    current_url=$current_url
    search=$user_price_search
}

{$_key = 0}
<div class="table-wrapper">
    <table class="table table-middle" width="100%">
        <thead class="cm-first-sibling">
            <tr>
                <th width="65%">{__("user")}</th>
                <th width="20%">{__("price")}</th>
                {hook name="user_price:manage_header"}
                {/hook}
                <th width="15%">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$user_price item="price" key="_key" name="user_price"}
                <tr class="cm-row-item">
                    <td width="65%" class="{$no_hide_input_if_shared_product}">
                        {$extra_url = "&user_type=C"}
                        {include file="addons/user_price/pickers/users/picker.tpl"
                        user_info=$price.user_data
                        item_ids=$price.iser_id
                        display="radio"
                        but_meta="btn"
                        extra_url=$extra_url
                        view_mode="user_price_button"
                        data_id="issuer_info"
                        input_name="product_data[user_price][{$_key}][user_id]"
                        extra_class="user_price__picker_view"
                        }
                    </td>
                    <td width="20%" class="{$no_hide_input_if_shared_product}">
                        <input type="text" name="product_data[user_price][{$_key}][price]" value="{$price.price}" class="input input-user-price" />
                    </td>
                    {hook name="user_price:manage_body"}
                    {/hook}
                    <td width="15%" class="nowrap {$no_hide_input_if_shared_product} right">
                        {include file="buttons/clone_delete.tpl" microformats="cm-delete-price-row" no_confirm=true}
                    </td>
                </tr>
            {/foreach}
            {math equation="x+1" x=$_key|default:0 assign="new_key"}
            <tr class="{cycle values="table-row , " reset=1}{$no_hide_input_if_shared_product}" id="box_add_user_price">
                <td width="65%">
                    {$extra_url = "&user_type=C"}
                    {include file="addons/user_price/pickers/users/picker.tpl"
                    user_info=[]
                    item_ids=''
                    display="radio"
                    but_meta="btn"
                    extra_url=$extra_url
                    view_mode="user_price_button"
                    data_id="issuer_info"
                    input_name="product_data[user_price][{$new_key}][user_id]"
                    extra_class="user_price__picker_view"
                    }
                </td>
                <td width="20%">
                    <input type="text" name="product_data[user_price][{$new_key}][price]" value="" class="input" />
                </td>
                {hook name="user_price:manage_body_new_line"}
                {/hook}
                <td width="15%" class="right">
                    {include file="buttons/multiple_buttons.tpl" item_id="add_user_price" hide_clone=true}
                </td>
            </tr>
        </tbody>
    </table>
</div>

{include file="common/pagination.tpl" div_id=user_price_pagination}
