<td class="center mobile-hide" data-th="{__("sort_promotions_by_data__available_from")}">
    <span>{if $promotion.from_date > 0}{$promotion.from_date|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{else}-{/if}</span>
</td>
<td class="center mobile-hide" data-th="{__("sort_promotions_by_data__available_to")}">
    <span>{if $promotion.to_date > 0}{$promotion.to_date|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{else}-{/if}</span>
</td>
