{if $registration_pages}
{hook name="auth_info:login_form"}
	<div class="ty-login-info__txt">
		{__("text_login_form")}
		{foreach from=$registration_pages item=page }
			<p>
				<a href="{"pages.view&page_id=`$page.page_id`"|fn_url}">{$page.page}</a>
			</p>
		{/foreach}
	</div>
	{if $smarty.session.custom_registration}
		{$cid = $smarty.session.custom_registration}
		{if "welcome_text.`$cid`"|fn_is_lang_var_exists}
			<div class="ty-login-info__welcome-txt">
				{__("welcome_text.`$cid`") nofilter}
			</div>
		{/if}
	{/if}
{/hook}
{/if}