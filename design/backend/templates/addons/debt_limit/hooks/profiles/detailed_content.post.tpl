{include file="common/subheader.tpl" title=__("debt")}
<div class="control-group">
    <label class="control-label" for="debt">{__("debt")}</label>
    <div class="controls">
        <input id="debt" type="text" name="user_data[debt]" value="{$user_data.debt}" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="limit">{__("debt_limit")}</label>
    <div class="controls">
        <input id="limit" type="text" name="user_data[limit]" value="{$user_data.limit}" />
    </div>
</div>
