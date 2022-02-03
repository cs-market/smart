<div class="ty-control-group">
    <label for="helpdesk_message" class="ty-control-group__title cm-required">{__("new_message")}:</label>
    <div class="controls">
        <textarea id="helpdesk_message" name="ticket_data[message]" cols="55" rows="8" class="input-large"></textarea>
    </div>
</div>

<div class="ty-control-group">
    <label class="ty-control-group__title" for="box_new_file">{__("files")}:</label>
    <div id="box_new_file" class="margin-top controls">
        <div class="clear cm-row-item">
            <div class="float-left">{include file="common/fileuploader.tpl" hide_server=true var_name="ticket_data[`0`]"}</div>
        </div>
    </div>
</div>
