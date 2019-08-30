
{include file="common/subheader.tpl" title=__("1c.data_1c") target="#exim_1c"}

<div id="exim_1c" class="in collapse">

    <div class="control-group">
        <label for="external_id" class="control-label">{__("1c.category_external_id")}:</label>
        <div class="controls">
        <input type="text" name="category_data[external_id]" id="product_external_id" size="55" value="{$category_data.external_id}" class="input-text-large" />
        </div>
    </div>

    <div class="control-group">
        <label for="alternative_names" class="control-label">{__("1c.alternative_names")}:</label>
        <div class="controls">
        <textarea name="category_data[alternative_names]" id="elm_category_alternative_names" cols="55" rows="4" class="input-large">{$category_data.alternative_names}</textarea>
        </div>
    </div>
</div>