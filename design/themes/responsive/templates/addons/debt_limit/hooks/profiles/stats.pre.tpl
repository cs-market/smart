<div class="ty-control-group debt-stats">
    <span class="ty-control-group__title">{__("debt")}:</span>
    <span class="ty-control-group__label" for="previous_period">{__("debt_limit")}: {include file="common/price.tpl" value=$auth.limit}</span>
    <span class="ty-control-group__label" for="current_period">{__("fact")}: {include file="common/price.tpl" value=$auth.debt}</span>
</div>
