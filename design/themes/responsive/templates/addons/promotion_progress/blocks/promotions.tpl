{if $progress_promotions}
    {foreach from=$progress_promotions item='promotion'}
        <div style="width: 100%;">
            <div>{$promotion.name} {if $promotion.short_description|trim}{include file="common/tooltip.tpl" tooltip=$promotion.short_description}{/if}</div>
            <div>
                <div class="ty-float-left"><i class="ty-smart-icon-flag-start"></i>{if $promotion.modify_values_to_price}{include file="common/price.tpl" value=0}{else}0{/if}</div>
                <div class="ty-float-right">{if $promotion.modify_values_to_price}{include file="common/price.tpl" value=$promotion.goal_value}{else}{$promotion.goal_value}{/if}<i class="ty-smart-icon-flag-finish"></i></div>
            </div>
            <div class="ty-progress-bar" style="--progress: {$promotion.current_value / $promotion.goal_value * 100}%;">
                <span class="ty-progress-bar__progress">{if $promotion.modify_values_to_price}{include file="common/price.tpl" value=$promotion.current_value} / {include file="common/price.tpl" value=$promotion.goal_value}{else}{$promotion.current_value}/{$promotion.goal_value}{/if}</span>
            </div>
        </div>
    {/foreach}
{/if}
