<input type="hidden" name="pshort" value="{if !$addons.ecl_search_improvements.multiple_checkboxes || $addons.ecl_search_improvements.multiple_checkboxes.pshort == 'Y'}Y{else}N{/if}" />
<input type="hidden" name="pfull" value="{if !$addons.ecl_search_improvements.multiple_checkboxes || $addons.ecl_search_improvements.multiple_checkboxes.pfull == 'Y'}Y{else}N{/if}" />
<input type="hidden" name="pname" value="{if !$addons.ecl_search_improvements.multiple_checkboxes || $addons.ecl_search_improvements.multiple_checkboxes.pname == 'Y'}Y{else}N{/if}" />
<input type="hidden" name="pkeywords" value="{if !$addons.ecl_search_improvements.multiple_checkboxes || $addons.ecl_search_improvements.multiple_checkboxes.pkeywords == 'Y'}Y{else}N{/if}" />
<input type="hidden" name="match" value="{$addons.ecl_search_improvements.search_type}" />
<input type="hidden" name="pcode_from_q" value="{if !$addons.ecl_search_improvements.multiple_checkboxes || $addons.ecl_search_improvements.multiple_checkboxes.pcode == 'Y'}Y{else}N{/if}" />
{if !$addons.ecl_search_improvements.multiple_checkboxes || $addons.ecl_search_improvements.multiple_checkboxes.pcode == 'Y'}
<input type="hidden" name="pcode" value="Y" />
{/if}