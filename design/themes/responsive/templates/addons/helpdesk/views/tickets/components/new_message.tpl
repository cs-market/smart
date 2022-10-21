<div class="ty-control-group">
    <label for="helpdesk_message" class="ty-control-group__title cm-required">{__("new_message")}:</label>
    <div class="controls">
        <textarea id="helpdesk_message" name="ticket_data[message]" cols="55" rows="2" class="input-large cm-focus" oninput="Tygh.$(this).height('5px');Tygh.$(this).height((Tygh.$(this).prop('scrollHeight')+21+'px'));"></textarea>
    </div>
</div>
{if !$auth.company_id|in_array:[2186,2187,1790,2184]}
<div class="ty-control-group ty-float-left">
    {*<label class="ty-control-group__title" for="box_new_file">{__("files")}:</label>*}
    <div id="box_new_file" class="margin-top controls">
        <div class="clear cm-row-item">
            <div class="float-left">{include file="common/fileuploader.tpl" hide_server=true var_name="ticket_data[`0`]"}</div>
        </div>
    </div>
</div>
{/if}
