<div class="control-group">
    <label class="control-label" for="elm_company_allow_order_reviews">{__("order_reviews.allow_order_reviews")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[allow_order_reviews]" value="N" />
        <input type="checkbox" name="company_data[allow_order_reviews]" id="elm_company_allow_order_reviews" value="Y" {if $company_data.allow_order_reviews == 'Y'} checked="checked"{/if} />
    </div>
</div>
