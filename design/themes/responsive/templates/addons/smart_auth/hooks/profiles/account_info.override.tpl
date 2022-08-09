{hook name="profiles:account_info"}
    <div class="ty-control-group">
        <label for="login" class="ty-control-group__title cm-trim">{__("login")}</label>
        <input type="text" id="login" size="32" maxlength="128" value="{$user_data.user_login}" class="ty-input-text" disabled="_disabled" />
    </div>

    <div class="ty-control-group">
        <label for="email" class="ty-control-group__title cm-required cm-trim">{__("email")}</label>
        <input type="text" id="email" name="user_data[email]" size="32" maxlength="128" value="{$user_data.email}" class="ty-input-text cm-focus" />
    </div>

    <div class="ty-control-group">
        <label for="password1" class="ty-control-group__title cm-required cm-password">{__("password")}</label>
        <input type="password" id="password1" name="user_data[password1]" size="32" maxlength="32" value="{if $runtime.mode == "update"}            {/if}" class="ty-input-text cm-autocomplete-off" />
    </div>

    <div class="ty-control-group">
        <label for="password2" class="ty-control-group__title cm-required cm-password">{__("confirm_password")}</label>
        <input type="password" id="password2" name="user_data[password2]" size="32" maxlength="32" value="{if $runtime.mode == "update"}            {/if}" class="ty-input-text cm-autocomplete-off" />
    </div>
{/hook}
