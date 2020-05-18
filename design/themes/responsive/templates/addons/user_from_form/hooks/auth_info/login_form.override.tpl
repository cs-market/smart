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
{/hook}
{/if}