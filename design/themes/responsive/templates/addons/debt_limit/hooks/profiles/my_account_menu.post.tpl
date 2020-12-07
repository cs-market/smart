{if $auth.user_id && ($user_info.limit|intval || $user_info.debt|intval)}
<li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a" href="#" rel="nofollow">{__("my_debt")}&nbsp;<span class="ty-reward-points-count">({$user_info.debt|default:"0"}{if $user_info.limit|intval}/{$user_info.limit|default:"0"}{/if})</span></a></li>
{/if}