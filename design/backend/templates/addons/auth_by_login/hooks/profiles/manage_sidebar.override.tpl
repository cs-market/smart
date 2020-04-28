{capture name='extra'}
<div class="sidebar-field">
    <label for="elm_user_login">{__("login")}</label>
    <div class="break">
        <input type="text" name="user_login" id="elm_user_login" value="{$search.user_login}" />
    </div>
</div>
{/capture}

{hook name="profiles:manage_sidebar"}
{include file="common/saved_search.tpl" dispatch="profiles.manage" view_type="users"}
{include file="views/profiles/components/users_search_form.tpl" dispatch="profiles.manage" extra=$smarty.capture.extra}
{/hook}