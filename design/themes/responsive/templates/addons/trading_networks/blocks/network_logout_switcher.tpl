{if $auth.network_id}
<a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" class="ty-aurora-account-switcher-fake-button">
    <i class="ty-icon-aurora-account"></i>
    {$user_info.firstname}
    <i class="ty-icon-down-micro"></i>
</a>
{/if}
