1. If "AB: Advanced banners" addon is used in your store, open the "design/backend/templates/addons/ab__advanced_banners/hooks/banners/general_content.override.tpl" file and add the following code to the end of file

[code]
{if $addons.ecl_banners_availability.status == 'A'}
    {include file="addons/ecl_banners_availability/hooks/banners/general_content.post.tpl"}
{/if}
[/code] 