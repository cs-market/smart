{if $runtime.mode == 'update' && $addons.ecl_staff_notes.vendor_notes == 'Y' && "MULTIVENDOR"|fn_allowed_for && $smarty.const.ACCOUNT_TYPE != 'vendor'}
{include file="addons/ecl_staff_notes/views/staff_notes/notes_form.tpl" notes_type="companies" notes_object_id=$company_data.company_id}
{/if}