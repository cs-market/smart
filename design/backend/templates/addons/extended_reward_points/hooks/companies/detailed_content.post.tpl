{include file="common/subheader.tpl" title=__("extended_reward_points") target="#reward_points_company_hook"}
<div id="reward_points_company_hook" class="in collapse">
    <div class="control-group">
        <label class="control-label" for="elm_company_reward_points_mechanics">{__("extended_reward_points.reward_points_mechanics")}:</label>
        <div class="controls">
            <select name="company_data[reward_points_mechanics]" id="elm_company_reward_points_mechanics">
                <option value="{"RewardPointsMechanics::FULL_PAYMENT"|enum}" {if $company_data.reward_points_mechanics == "RewardPointsMechanics::FULL_PAYMENT"|enum} selected="selected" {/if}>{__("extended_reward_points.full_payment")}</option>
                <option value="{"RewardPointsMechanics::PARTIAL_PAYMENT"|enum}" {if $company_data.reward_points_mechanics == "RewardPointsMechanics::PARTIAL_PAYMENT"|enum} selected="selected" {/if}>{__("extended_reward_points.partial_payment")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_company_max_rp_discount">{__("extended_reward_points.max_rp_discount")} (%):</label>
        <div class="controls">
            <input class="input-mini cm-trim cm-value-integer" id="elm_company_max_rp_discount" size="6" maxlength="3" type="text" name="company_data[max_rp_discount]" value="{$company_data.max_rp_discount}"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_company_max_product_discount">{__("extended_reward_points.max_product_discount")} (%):</label>
        <div class="controls">
            <input class="input-mini cm-trim cm-value-integer" id="elm_company_max_product_discount" size="6" maxlength="3" type="text" name="company_data[max_product_discount]" value="{$company_data.max_product_discount}"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_company_reward_points_ttl">{__("extended_reward_points.reward_points_ttl")}:</label>
        <div class="controls">
            <input class="input-mini cm-trim cm-value-integer" id="elm_company_reward_points_ttl" size="6" maxlength="3" type="text" name="company_data[reward_points_ttl]" value="{$company_data.reward_points_ttl}"/>
        </div>
    </div>
</div>
