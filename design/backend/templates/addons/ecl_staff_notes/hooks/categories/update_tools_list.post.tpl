{if $runtime.mode == 'update' && $addons.ecl_staff_notes.category_notes == 'Y'}
{include file="addons/ecl_staff_notes/views/staff_notes/notes_form.tpl" notes_type="categories" notes_object_id=$category_data.category_id}
{/if}