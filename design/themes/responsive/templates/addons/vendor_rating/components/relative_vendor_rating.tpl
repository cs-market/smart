<span class="ty-vendor-rating">
    {if $rating >= $addons.vendor_rating.bronze_rating_lower_limit && $rating < $addons.vendor_rating.silver_rating_lower_limit}
        <i class="ty-vendor-rating-icon ty-vendor-rating-icon--bronze" title="{__("vendor_rating.vendor_rating")}: {$rating}%"></i>
    {elseif $rating >= $addons.vendor_rating.silver_rating_lower_limit && $rating < $addons.vendor_rating.gold_rating_lower_limit}
        <i class="ty-vendor-rating-icon ty-vendor-rating-icon--silver" title="{__("vendor_rating.vendor_rating")}: {$rating}%"></i>
    {elseif $rating >= $addons.vendor_rating.gold_rating_lower_limit}
        <i class="ty-vendor-rating-icon ty-vendor-rating-icon--gold" title="{__("vendor_rating.vendor_rating")}: {$rating}%"></i>
    {/if}
</span>