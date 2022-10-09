{if $cart.ask_to_split_order}
<div class="litecheckout__group">
    <div class="litecheckout__container">
        <div class="litecheckout__item">
            <div class="litecheckout__terms" id="litecheckout_terms">
                <div class="ty-control-group ty-checkout__terms">
                    {foreach from=$cart.ask_to_split_order item="group_name" key=p_group_id}
                    <div class="cm-field-container">
                        <input type="hidden" name="split_order[{$p_group_id}]" value="N">
                        <label for="split_order_{$p_group_id}" class="cm-check-agreement"><input type="checkbox" id="split_order_{$p_group_id}" name="split_order[{$p_group_id}]" value="Y" class="checkbox">{__('product_groups.split_order', ['[group_name]' => $group_name])}</label>
                    </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>
{/if}
