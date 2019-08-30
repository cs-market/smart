{if $runtime.mode == 'update' && $addons.ecl_staff_notes.product_notes == 'Y'}
{include file="addons/ecl_staff_notes/views/staff_notes/notes_form.tpl" notes_type="products" notes_object_id=$product_data.product_id}
{/if}