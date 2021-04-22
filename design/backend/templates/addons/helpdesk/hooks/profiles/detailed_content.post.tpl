<div class="control-group">
    <label class="control-label" for="helpdesk_notification">{__('helpdesk_notification')}:</label>
    <input type="hidden" name="user_data[helpdesk_notification]" value="N">
    <div class="controls">
        <input id="helpdesk_notification" type="checkbox" name="user_data[helpdesk_notification]" value="Y" {if $user_data.helpdesk_notification == 'Y'}checked="_checked"{/if}>
    </div>
</div>
