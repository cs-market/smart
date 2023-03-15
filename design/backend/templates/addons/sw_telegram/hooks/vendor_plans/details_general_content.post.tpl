{if $addons.sw_telegram.tg_allow_in_tarifs == 'Y'}
    <div class="control-group">
        <label class="control-label" for="elm_noty_tg_{$id}">{__("sw_telegram.noty_vendors")}:</label>
                <div class="controls">
                        <input class="checkbox" type="hidden" name="plan_data[noty_tg]" value="N">
                         <input class="checkbox" type="checkbox" name="plan_data[noty_tg]" id="elm_noty_tg_{$id}" {if $plan.noty_tg == 'Y'}checked="checked"{/if}  value="Y" />
        </div>
    </div>
{/if}