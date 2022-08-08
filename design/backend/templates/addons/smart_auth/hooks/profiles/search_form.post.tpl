{if $smarty.capture.simple_search|trim}
    {$simple_search = $smarty.capture.simple_search}
    {capture name="simple_search"}
        {$simple_search nofilter}
        <div class="sidebar-field">
            <label for="elm_login">{__("login")}</label>
            <div class="break">
                <input type="text" name="user_login" id="elm_login" value="{$search.login}" />
            </div>
        </div>
    {/capture}
{/if}