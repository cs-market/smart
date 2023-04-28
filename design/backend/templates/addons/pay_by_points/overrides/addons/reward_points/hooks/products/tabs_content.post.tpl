{assign var="data" value=$product_data}

<div id="content_reward_points" class="hidden">
    {include file="common/subheader.tpl" title=__("price_in_points") target="#reward_points_products_hook"}
    <div id="reward_points_products_hook" class="in collapse">
        <fieldset>
        {assign var="is_auto" value=$addons.reward_points.auto_price_in_points}
            <div class="control-group">
                <label class="control-label" for="pd_is_pbp">{__("pay_by_points")}</label>
                <div class="controls">
                    <input type="hidden" name="product_data[is_pbp]" value="N" />
                    <input type="checkbox" name="product_data[is_pbp]" id="pd_is_pbp" value="Y" {if $data.is_pbp == "Y" || $runtime.mode == "add"}checked="checked"{/if} onclick="{if $is_auto != 'Y'}Tygh.$.disable_elms(['price_in_points'], !this.checked);{else}Tygh.$.disable_elms(['is_oper'], !this.checked); Tygh.$.disable_elms(['price_in_points'], !this.checked || !Tygh.$('#is_oper').prop('checked'));{/if}">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="pd_points_eq_price">{__("pay_by_points__points_eq_price")}</label>
                <div class="controls">
                    <input type="hidden" name="product_data[points_eq_price]" value="N" />
                    <input type="checkbox" name="product_data[points_eq_price]" id="pd_points_eq_price" value="Y" {if $data.points_eq_price == "Y"}checked="checked"{/if} onclick="{if $is_auto != 'Y'}Tygh.$.disable_elms(['price_in_points'], this.checked);{else}Tygh.$.disable_elms(['is_oper'], this.checked); Tygh.$.disable_elms(['price_in_points'], this.checked || !Tygh.$('#is_oper').prop('checked'));{/if}">
                </div>
            </div>

            {if $is_auto == "Y"}
            <div class="control-group">
                <label class="control-label" for="is_oper">{__("override_per")}</label>
                <div class="controls">
                    {math equation="x*y" x=$data.price|default:"0" y=$addons.reward_points.point_rate assign="rate_pip"}
                    <input type="hidden" id="price_in_points_exchange" value="{$rate_pip|ceil}" />
                    <input type="hidden" name="product_data[is_oper]" value="N" />
                    <input type="checkbox" id="is_oper" name="product_data[is_oper]" value="Y" {if $data.is_oper == "Y"}checked="checked"{/if} onclick="Tygh.$.disable_elms(['price_in_points'], !this.checked);" {if $data.is_pbp != "Y"} disabled="disabled"{/if}>
                </div>
            </div>
            {/if}

            <div class="control-group">
                <label class="control-label" for="price_in_points">{__("price_in_points")}</label>
                <div class="controls">
                    <input type="text" id="price_in_points" name="product_data[point_price]" value="{$data.point_price|default:0}" size="10"  {if $data.is_pbp != "Y" || $data.points_eq_price == "Y" || ($is_auto == "Y" && $data.is_oper != "Y")}disabled="disabled"{/if}>
                </div>
            </div>
        </fieldset>
    </div>

    <input type="hidden" name="object_type" value="{$object_type}">
            
    {include file="common/subheader.tpl" title=__("earned_points") target="#reward_points_products_earned_hook"}
    <div id="reward_points_products_earned_hook" class="in collapse">
        <fieldset>
            <div class="control-group">
                <label class="control-label" for="pd_earned_points_eq_price">{__("pay_by_points__earned_points_eq_price")}</label>
                <div class="controls">
                    <input type="hidden" name="product_data[earned_points_eq_price]" value="N" />
                    <input type="checkbox" name="product_data[earned_points_eq_price]" id="pd_earned_points_eq_price" value="Y" {if $data.earned_points_eq_price == "Y"}checked="checked"{/if}>
                </div>
            </div>

            <input type="hidden" name="product_data[is_op]" value="N">
            <label for="rp_is_op" class="checkbox">
                <input type="checkbox" name="product_data[is_op]" id="rp_is_op" value="Y" {if $data.is_op == "Y"}checked="checked"{/if} onclick="Tygh.$.disable_elms([{foreach from=$reward_usergroups item=m}'earned_points_{$object_type}_{$m.usergroup_id}',{/foreach}{foreach from=$reward_usergroups item=m}'points_type_{$object_type}_{$m.usergroup_id}',{/foreach}], !this.checked);">
                {__("override_gc_points")}
            </label>

            <div class="table-responsive-wrapper">
                <table class="table table-middle table-responsive">
                <thead class="cm-first-sibling">
                    <tr>
                        <th width="20%">{__("usergroup")}</th>
                        <th width="40%">{__("amount")}</th>
                        <th width="40%">{__("amount_type")}</th>
                        <th width="15%">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                {foreach from=$reward_points item=m}
                    <tr>
                        <td data-th="{__("usergroup")}">
                            <input type="hidden" name="product_data[reward_points][{$m.usergroup_id}][usergroup_id]" value="{$m.usergroup_id}">
                            {$reward_usergroups.{$m.usergroup_id}.usergroup}
                        </td>
                        <td data-th="{__("amount")}">
                            <input type="text" id="earned_points_{$object_type}_{$m.usergroup_id}" name="product_data[reward_points][{$m.usergroup_id}][amount]" value="{$reward_points[$m.usergroup_id].amount|default:"0"}" {if $data.is_op != "Y"}disabled="disabled"{/if}>
                        </td>
                        <td data-th="{__("amount_type")}">
                            <select id="points_type_{$object_type}_{$m.usergroup_id}" name="product_data[reward_points][{$m.usergroup_id}][amount_type]" {if $object_type == $smarty.const.PRODUCT_REWARD_POINTS && $data.is_op != 'Y'}disabled="disabled"{/if}>
                                <option value="A" {if $reward_points[$m.usergroup_id].amount_type == "A"}selected{/if}>{__("absolute")} ({__("points_lower")})</option>
                                <option value="P" {if $reward_points[$m.usergroup_id].amount_type == "P"}selected{/if}>{__("percent")} (%)</option>
                            </select>
                        </td>
                        <td width="15%" class="nowrap {$no_hide_input_if_shared_product} right">{include file="buttons/clone_delete.tpl" dummy_href=true microformats="cm-delete-row" no_confirm=true}</td>
                    </tr>
                {/foreach}
                {math equation="x+1" x=$_key|default:0 assign="new_key"}
                <tr class="{cycle values="table-row , " reset=1}{$no_hide_input_if_shared_product}" id="box_add_oper">
                    <td width="5%" data-th="{__("usergroup")}">
                        <select id="usergroup_id" name="product_data[reward_points][add_{$new_key}][usergroup_id]" class="span3">
                            <option value="0">{__('all')}</option>
                            {foreach from=$reward_usergroups item="usergroup"}
                                <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                            {/foreach}
                        </select>
                        </td>
                    <td width="20%" data-th="{__("amount")}">
                        <input type="text" id="earned_points_{$object_type}_{$m.usergroup_id}" name="product_data[reward_points][add_{$new_key}][amount]" value="0" {if $data.is_op != "Y"}disabled="disabled"{/if}>
                    </td>
                    <td width="25%" data-th="{__("amount_type")}">
                        <select id="points_type_{$object_type}_{$m.usergroup_id}" name="product_data[reward_points][add_{$new_key}][amount_type]" {if $object_type == $smarty.const.PRODUCT_REWARD_POINTS && $data.is_op != 'Y'}disabled="disabled"{/if}>
                            <option value="A" {if $reward_points[$m.usergroup_id].amount_type == "A"}selected{/if}>{__("absolute")} ({__("points_lower")})</option>
                            <option value="P" {if $reward_points[$m.usergroup_id].amount_type == "P"}selected{/if}>{__("percent")} (%)</option>
                        </select>
                    </td>
                    <td width="15%" class="right">
                        {include file="buttons/multiple_buttons.tpl" item_id="add_oper"}
                    </td>
                </tr>
                </tbody>
                </table>
            </div>
        </fieldset>
    </div>
</div>
