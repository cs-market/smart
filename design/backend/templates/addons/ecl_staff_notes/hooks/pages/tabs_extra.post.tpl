{if $runtime.mode == 'update' && $addons.ecl_staff_notes.page_notes == 'Y'}
{include file="addons/ecl_staff_notes/views/staff_notes/notes_form.tpl" notes_type="pages" notes_object_id=$page_data.page_id}
{/if}