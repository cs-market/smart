{if $auth.network_id}
<a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" class="ty-account-switcher__fake-button">
    <i class="ty-icon-aurora-house"></i>
    <span class="ty-account-switcher__dropdown-title">{$user_info.firstname}</span>
    <i class="ty-icon-down-micro"></i>
</a>
{/if}
