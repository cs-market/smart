{if $runtime.mode == 'update' && $addons.ecl_staff_notes.user_notes == 'Y'}
{capture name="sidebar"}
{include file="addons/ecl_staff_notes/views/staff_notes/notes_form.tpl" notes_type="users" notes_object_id=$user_data.user_id hide_staff_form=Y}
<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    $(document).ready(function(){
        $('#notify_customer').attr('checked', false);
    });
}(Tygh, Tygh.$));
//]]>
</script>
{/capture}
{/if}