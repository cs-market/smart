{capture name="general"}
    {include file="addons/vendor_rating/settings/components/formula.tpl"}
    {include file="addons/vendor_rating/settings/components/start_rating_period.tpl"}
    {include file="addons/vendor_rating/settings/components/cron_command.tpl"}
{/capture}

{capture name="rating_ranges"}
    {include file="addons/vendor_rating/settings/components/rating_ranges.tpl"}
{/capture}

<div class="cm-j-tabs cm-track tabs">
    <ul class="nav nav-tabs">
        <li id="vendor_rating_tab_general" class="cm-js active">
            <a>{__("general")}</a>
        </li>
        <li id="vendor_rating_tab_rating_ranges" class="cm-js">
            <a>{__("vendor_rating.rating_ranges")}</a>
        </li>
    </ul>
</div>

<div class="cm-tabs-content">
    <div id="content_vendor_rating_tab_general" class="hidden">{$smarty.capture.general nofilter}</div>
    <div id="content_vendor_rating_tab_rating_ranges" class="hidden">{$smarty.capture.rating_ranges nofilter}</div>
</div>
