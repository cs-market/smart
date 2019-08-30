{if $runtime.mode == 'update' && $addons.ecl_staff_notes.news_notes == 'Y'}
{include file="addons/ecl_staff_notes/views/staff_notes/notes_form.tpl" notes_type="news" notes_object_id=$news_data.news_id}
{/if}