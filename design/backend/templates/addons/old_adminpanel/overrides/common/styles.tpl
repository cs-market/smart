{styles}
	{style src="addons/old_adminpanel/ui/jqueryui.css"}
	{style src="addons/old_adminpanel/lib/select2/select2.min.css"}
    {hook name="index:styles"}
        {style src="addons/old_adminpanel/styles.less"}
        {style src="addons/old_adminpanel/glyphs.css"}

        {include file="views/statuses/components/styles.tpl" type=$smarty.const.STATUSES_ORDER}

        {if $language_direction == 'rtl'}
            {style src="addons/old_adminpanel/rtl.less"}
        {/if}
    {/hook}
    {style src="addons/old_adminpanel/font-awesome.css"}
{/styles}