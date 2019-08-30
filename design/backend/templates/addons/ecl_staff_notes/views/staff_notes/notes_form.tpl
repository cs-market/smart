{if $show_staff_notes}
<div class="hidden" id="staff_notes">
<div class="sidebar-row" id="staff_notes_row">
    <h6>{__("staff_notes")}</h6>
	{if $hide_staff_form != 'Y'}
	<form action="{""|fn_url}{$_page_part}" name="{$product_search_form_prefix}search_form" method="post" class="cm-disable-empty {$form_meta}">
	{/if}
		<input type="hidden" name="type" value="{$notes_type}" />
		<input type="hidden" name="object_id" value="{$notes_object_id}" />
		<input type="hidden" name="redirect_url" value="{$config.current_url}" />
		
		<div class="sidebar-field">
			<textarea name="staff_notes" id="elm_staff_notes" cols="55" rows="15" style="max-width: 100%">{$staff_notes}</textarea>
		</div>
	{if $hide_staff_form != 'Y'}	
		{include file="buttons/save.tpl" but_name="dispatch[staff_notes.save]" but_meta="right"}
	</form>
	{/if}
</div>
<hr>
</div>
<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    $(document).ready(function(){
        $('.sidebar-wrapper').append($('#staff_notes'));
		$('#staff_notes').show();
    });
}(Tygh, Tygh.$));
//]]>
</script>
{/if}