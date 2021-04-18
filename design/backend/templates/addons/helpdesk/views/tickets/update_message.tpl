{capture name="mainbox"}
{include file="addons/helpdesk/views/tickets/components/edit_message.tpl"}
{/capture}

{capture name="mainbox_title"}
	{"{__("editing_message")}: `$message.message_id` (`$message.user`)"}
{/capture}

{include file="common/mainbox.tpl" title=$smarty.capture.mainbox_title content=$smarty.capture.mainbox select_languages=$save buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}