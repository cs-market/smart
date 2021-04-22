<div class="new-ticket">
    <div class="form-wrap form-wrap-default">
        <form enctype="multipart/form-data" name="forms_form" method="post" action="{""|fn_url}">
            {if !$auth.user_id}
            <div class="ty-control-group">
                <label class="ty-control-group__title cm-required cm-email " for="new_ticket_email">{__("email")}</label>
                <input id="new_ticket_email" class="ty-input-text cm-focus " type="text" name="ticket_data[email]" value="{$ticket_data.email}" size="50">
            </div>
            <div class="ty-control-group">
                <label class="ty-control-group__title cm-required" for="new_ticket_name">{__("name")}</label>
                <input id="new_ticket_name" class="ty-input-text" type="text" name="ticket_data[name]" value="{$ticket_data.name}" size="50">
            </div>
            {/if}
            <div class="ty-control-group">
                <label class="ty-control-group__title cm-required" for="new_ticket_subject">{__("subject")}</label>
                <input id="new_ticket_subject" class="ty-input-text" type="text" name="ticket_data[subject]" value="{$ticket_data.subject}" size="50">
            </div>
            {include file="addons/helpdesk/views/tickets/components/new_message.tpl"}
            {include file="common/image_verification.tpl" option="form_builder"}

            <div class="buttons-container">
                {include file="buttons/button.tpl" but_role="submit" but_text=__("submit") but_name="dispatch[tickets.add]"}
            </div>
        </form>
    </div>

{capture name="mainbox_title"}{__("contact_us")}{/capture}
</div>
