{assign var="id" value=$id|default:"main_login"}

{capture name="login"}
    <h3 class="ty-center">{__('my_account')}</h3>
    <form name="{$id}_form" action="{""|fn_url}" method="post" {if $style == "popup"}class="cm-ajax cm-ajax-full-render"{/if}>
        {if $style == "popup"}
            <input type="hidden" name="result_ids" value="{$id}_login_popup_form_container" />
            <input type="hidden" name="login_block_id" value="{$id}" />
            <input type="hidden" name="quick_login" value="1" />
        {/if}

        <input type="hidden" name="return_url" value="{'categories.view&category_id=9059'|fn_url}" />
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />

        {if $style == "checkout"}
            <div class="ty-checkout-login-form">{include file="common/subheader.tpl" title=__("returning_customer")}
        {/if}

        <div class="ty-control-group">
            <label for="login_{$id}" class="ty-login__filed-label ty-control-group__label cm-required cm-trim hidden">{__("phone")} {__('or')} {__("login")}</label>
            <input type="text" id="login_{$id}" name="user_login" size="30" value="{if $stored_user_login}{$stored_user_login}{else}{$config.demo_username}{/if}" class="ty-login__input cm-focus" placeholder="{__("phone")} {__('or')} {__("login")}" />
        </div>

        <div class="ty-control-group ty-password-forgot">
            <label for="psw_{$id}" class="ty-login__filed-label ty-control-group__label ty-password-forgot__label cm-required hidden">{__("password")}</label><a href="{"pages.view&page_id=128"|fn_url}" class="ty-password-forgot__a"  tabindex="5">{__("forgot_password_question")}</a>
            <input type="password" id="psw_{$id}" name="password" size="30" value="{$config.demo_password}" class="ty-login__input" maxlength="32" placeholder="{__("password")}" />
        </div>

        {if $style == "popup"}
            {if $login_error}
                <div class="ty-login-form__wrong-credentials-container">
                    <span class="ty-login-form__wrong-credentials-text ty-error-text">{__("error_incorrect_login")}</span>
                </div>
            {/if}

        {/if}

        {include file="common/image_verification.tpl" option="login" align="left"}

        {if $style == "checkout"}
            </div>
        {/if}

        {hook name="index:login_buttons"}
            <div>
                <input type="hidden" name="remember_me" id="remember_me_{$id}" value="Y" />
                {include file="buttons/button.tpl" but_text=__("sign_in") but_onclick=$but_onclick but_name="dispatch[auth.login]" but_meta="ty-btn__login-button ty-btn__full-width"}
                {include file="buttons/button.tpl" but_text=__("register") but_onclick=$but_onclick but_href="pages.view&page_id=169" but_meta="ty-btn__login-button ty-btn__full-width"}
            </div>
        {/hook}
    </form>
{/capture}

{if $style == "popup"}
    <div id="{$id}_login_popup_form_container">
        {$smarty.capture.login nofilter}
    <!--{$id}_login_popup_form_container--></div>
{else}
    <div class="ty-login">
        {$smarty.capture.login nofilter}
    </div>

    {capture name="mainbox_title"}{__("sign_in")}{/capture}
{/if}
