<div class="sidebar-field" id="elm_pcode_field">
    <label>{__("search_by_sku")}</label>
	<input type="text" name="pcode" value="{$search.pcode}" onfocus="this.select();"/>
</div>
<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    $(document).ready(function(){
		$('#pcode').parent().parent().remove();
		$('#simple_search').append($('#elm_pcode_field'));
    });
}(Tygh, Tygh.$));
//]]>
</script>
<input id="elm_match_field" type="hidden" name="match" value="{$addons.ecl_search_improvements.admin_search_type}" />