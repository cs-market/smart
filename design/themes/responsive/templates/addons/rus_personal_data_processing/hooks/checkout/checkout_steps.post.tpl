{if ($settings.Checkout.disable_anonymous_checkout == "Y" && !$auth.user_id) || ($settings.Checkout.disable_anonymous_checkout != "Y" && !$auth.user_id && !$contact_info_population) || $smarty.session.failed_registration == true}
    <div class="controls">
        <input type="checkbox" id="elm_personal_data" value="Y" checked="checked" />
        <label class="cm-required" for="elm_personal_data">{__("addons.rus_personal_data_processing.confidentiality")}</label>
        <br />
        <span class="ty-policy-description">{$policy_description nofilter}</span>
    </div>
{/if}