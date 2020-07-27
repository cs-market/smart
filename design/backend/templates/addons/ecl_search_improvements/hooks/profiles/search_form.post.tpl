<div class="sidebar-field" id="elm_phone_field">
    <label for="elm_phone_sb">{__("phone")}</label>
    <div class="break">
        <input type="text" name="phone" id="elm_phone_sb" value="{$search.phone}" />
    </div>
</div>
<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    $(document).ready(function(){
		$('#simple_search').append($('#elm_phone_field'));
        if ($('#elm_phone').length) {
            $('#elm_phone').parent().parent().remove();
        }
    });
}(Tygh, Tygh.$));
//]]>
</script>
