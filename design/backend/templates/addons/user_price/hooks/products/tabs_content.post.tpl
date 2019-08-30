<div id="content_user_price" class="hidden">
    {include file="common/subheader.tpl" title=__("user_price") target="#user_price_products_hook"}
    <div id="user_price_products_hook" class="in collapse">
      {$_key = 0}
      <div class="table-wrapper">
          <table class="table table-middle" width="100%">
          <thead class="cm-first-sibling">
          <tr>
              <th width="65%">{__("user")}</th>
              <th width="20%">{__("price")}</th>
              <th width="15%">&nbsp;</th>
          </tr>
          </thead>
          <tbody>
          {foreach from=$product_data.user_price item="price" key="_key" name="user_price"}
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
              <input type="text" name="product_data[user_price][{$_key}][price]" value="{$price.price}" class="input" />
            </td>
            <td width="15%" class="nowrap {$no_hide_input_if_shared_product} right">
              {include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}
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
            <td width="15%" class="right">
                {include file="buttons/multiple_buttons.tpl" item_id="add_user_price"}
            </td>
        </tr>
        </tbody>
        </table>
      </div>
    </div>
</div>
