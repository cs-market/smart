<div class="control-group">
    <label for="helpdesk_message" class="control-label">{__('text')}:</label>
    <div class="controls">
        <textarea id="helpdesk_message" name="ticket_data[message]" cols="55" rows="8" class="cm-wysiwyg input-large cm-helpdesk-message">{$ticket_data.message|default:$message.message}</textarea>
        <input type="hidden" name="ticket_data[viewed]" value="N">
    </div>
</div>
