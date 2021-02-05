<div class="control-group">
    <label class="control-label" for="elm_date_avail_till_holder">{__("available_till")}:</label>
    <div class="controls">
        {include file="common/calendar.tpl" date_id="elm_date_avail_till_holder" date_name="product_data[avail_till]" date_val=$product_data.avail_till|default:"" start_year=$settings.Company.company_start_year}
    </div>
</div>
