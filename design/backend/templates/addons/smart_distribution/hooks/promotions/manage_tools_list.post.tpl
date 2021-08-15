{capture name="sidebar"}
    {hook name="promotions:manage_sidebar"}
    {include file="addons/smart_distribution/views/promotions/components/promotions_search_form.tpl" dispatch="promotions.manage"}
    {/hook}
{/capture}
{$sidebar = $smarty.capture.sidebar scope=parent}
