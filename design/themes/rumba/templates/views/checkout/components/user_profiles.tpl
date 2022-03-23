{include file="views/profiles/components/profiles_scripts.tpl"}

<label for="user_profiles_list"
       class="cm-required cm-multiple-radios hidden"
       data-ca-validator-error-message="{__("checkout_select_profile_before_order")}"></label>

<div id="user_profiles_list"
    class="litecheckout__group"
    data-ca-error-message-target-node="#user_profiles_list_error_message_target">
    {foreach $user_profiles as $profile}
        <div class="ty-tiles litecheckout__field litecheckout__field--xsmall">
            <input type="radio"
                   name="profile_id"
                   id="user_profile_{$profile.profile_id}"
                   class="ty-tiles__radio hidden js-lite-checkout-user-profile-radio js-lite-checkout-profile-selector"
                   data-ca-profile-id="{$profile.profile_id}"
                   value="{$profile.profile_id}"
                   {if $profile.profile_id == $cart.profile_id}checked{/if}
            />

            <label id="user_profiles_{$profile.profile_id}"
                   class="ty-tiles__wrapper"
                   for="user_profile_{$profile.profile_id}"
            >
                <p class="ty-tiles__title">{$profile.s_address} {$profile.s_address_2}</p>

                {if $profile.s_city || $profile.s_state_descr || $profile.s_zipcode}
                    <p class="ty-tiles__text">{if
	                    $profile.s_city}{$profile.s_city}, {/if}{if
	                    $profile.s_state_descr}{$profile.s_state_descr}, {/if}{if
	                    $profile.s_zipcode}{$profile.s_zipcode}{/if}</p>
                {/if}
                {if $profile.s_country_descr}
                    <p class="ty-tiles__text">{$profile.s_country_descr}</p>
                {/if}
            </label>
        </div>
    {/foreach}
</div>
<div class="litecheckout__group"><div id="user_profiles_list_error_message_target" class="litecheckout__item"></div></div>
