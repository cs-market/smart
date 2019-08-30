<div id="colors_variables">
    <style>
        {$mobile_app_styles nofilter}
    </style>
<!--colors_variables--></div>

{capture name="general"}
<div class="clearfix">
    <div class="span6">
        {include file="common/subheader.tpl" title="{__(app_params)}"}

        <div class="control-group">
            <label class="control-label" for="m_settings_app_settings_utility_shopName">{__("mobile_app.shopName")}:</label>
            <div class="controls">
                <input type="text" name="m_settings[app_settings][utility][shopName]"
                    value="{$config_data.app_settings.utility.shopName}"
                    id="m_settings_app_settings_utility_shopName"
                />
            </div>
        </div>

        <br /><br />

        <div class="control-group">
            <label class="control-label" for="m_settings_app_settings_utility_pushNotifications">{__("mobile_app.pushNotifications")}:</label>
            <div class="controls">
                <select
                    name="m_settings[app_settings][utility][pushNotifications]"
                    id="m_settings_app_settings_utility_pushNotifications"
                >
                    <option value="0" {if $config_data.app_settings.utility.pushNotifications == 0}selected{/if}>{__("no")}</option>
                    <option value="1" {if $config_data.app_settings.utility.pushNotifications == 1}selected{/if}>{__("yes")}</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="m_settings_app_settings_utility_fcmApiKey">{__("mobile_app.fcmApiKey")}:</label>
            <div class="controls">
                <input type="text" name="m_settings[app_settings][utility][fcmApiKey]"
                    value="{$config_data.app_settings.utility.fcmApiKey}"
                    id="m_settings_app_settings_utility_fcmApiKey"
                />
            </div>
        </div>

        <br /><br />

        <div class="control-group">
            <label for="config_data_app_settings_build_appName" 
                   class="control-label cm-required"
            >{__("mobile_app.appName")}:</label>
            <div class="controls">
                <input type="text" name="m_settings[app_settings][build][appName]"
                    id="config_data_app_settings_build_appName"
                    value="{$config_data.app_settings.build.appName}"
                    maxlength="30"
                />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label cm-required" for="config_data_app_settings_build_appShortDescription">{__("mobile_app.appShortDescription")}:</label>
            <div class="controls">
                <textarea 
                    name="m_settings[app_settings][build][appShortDescription]" 
                    cols="30" 
                    rows="3" 
                    maxlength="80"
                    data-target="appShortDescription"
                    id="config_data_app_settings_build_appShortDescription"
                >{$config_data.app_settings.build.appShortDescription}</textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label cm-required" for="config_data_app_settings_build_appFullDescription">{__("mobile_app.appFullDescription")}:</label>
            <div class="controls">
                <textarea 
                    name="m_settings[app_settings][build][appFullDescription]" 
                    cols="30" 
                    rows="10" 
                    maxlength="4000"
                    data-target="appFullDescription"
                    id="config_data_app_settings_build_appFullDescription"
                >{$config_data.app_settings.build.appFullDescription}</textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label cm-required cm-email" for="config_data_app_settings_build_supportEmail">{__("mobile_app.supportEmail")}:</label>
            <div class="controls">
                <input type="email" name="m_settings[app_settings][build][supportEmail]"
                    value="{$config_data.app_settings.build.supportEmail}"
                    id="config_data_app_settings_build_supportEmail"
                    data-target="supportEmail"
                />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label cm-required" for="config_data_app_settings_build_privacyPolicyUrl">{__("mobile_app.privacyPolicyUrl")}:</label>
            <div class="controls">
                <input type="text" name="m_settings[app_settings][build][privacyPolicyUrl]"
                    value="{$config_data.app_settings.build.privacyPolicyUrl}"
                    id="config_data_app_settings_build_privacyPolicyUrl"
                    data-target="privacyPolicyUrl"
                />
            </div>
        </div>
    </div>

    <div class="span9 mobile-app__images-container">
        {include file="common/subheader.tpl" title="{__(images_params)}"}
        <div class="control-group">
            <label class="control-label" for="config_data_app_settings_build_crop_when_resize">{__("mobile_app.crop_when_resize")}{include file="common/tooltip.tpl" tooltip=__("tt_mobile_app.crop_when_resize")}:</label>
            <div class="controls">
                <innpu type="checkbox" name="m_settings[app_settings][images][crop_when_resize]" value="N" checked/>
                <input type="checkbox" name="m_settings[app_settings][images][crop_when_resize]"
                       value="Y"
                       id="config_data_app_settings_build_crop_when_resize"
                       data-target="crop_when_resize"
                       {if $config_data.app_settings.images.crop_when_resize == "Y"}
                           checked
                       {/if}
                />
            </div>
        </div>

        {foreach $image_types as $image_type_data}
            <div class="control-group">
                <label class="control-label">{__("mobile_app.`$image_type_data.name`")}{if !$image_type_data.no_tooltip}{include file="common/tooltip.tpl" tooltip=__("tt_mobile_app.`$image_type_data.name`")}{/if}</label>
                <div class="controls">
                    {include file="common/attach_images.tpl" image_name=$image_type_data.name image_object_type=$image_type_data.type image_pair=$app_images[$image_type_data.type] hide_alt=true hide_thumbnails=true no_thumbnail=true}
                </div>
            </div>
        {/foreach}
    </div>

</div>
{/capture}

{capture name="colors"}
<div class="clearfix">
    {include file="addons/mobile_app/components/categories.tpl"}

    {include file="addons/mobile_app/components/drawer.tpl"}

    {include file="addons/mobile_app/components/navbar.tpl"}

    {include file="addons/mobile_app/components/product_screen.tpl"}

    {include file="addons/mobile_app/components/main.tpl"}
</div>
{/capture}

<div id="content_mobile_app_configurator">

    <form action="{""|fn_url}" method="post" name="app_config">
        <input type="hidden" name="setting_id" value="{$setting_id}" />

        <div class="cm-j-tabs cm-track tabs">
            <ul class="nav nav-tabs">
                <li id="mobile_app_tab_general" class="cm-js active">
                    <a>{__("general")}</a>
                </li>
                <li id="mobile_app_tab_colors" class="cm-js">
                    <a>{__("mobile_app.configure_colors")}</a>
                </li>
            </ul>
        </div>

        <div class="cm-tabs-content">
            <div id="content_mobile_app_tab_general" class="hidden">{$smarty.capture.general nofilter}</div>
            <div id="content_mobile_app_tab_colors" class="hidden">{$smarty.capture.colors nofilter}</div>
        </div>

    </form>
</div>