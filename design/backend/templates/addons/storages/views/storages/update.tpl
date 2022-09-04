{if $storage}
    {assign var="id" value=$storage.storage_id|lower}
{else}
    {assign var="id" value="0"}
{/if}

{if "ULTIMATE"|fn_allowed_for && !$runtime.company_id}
    {assign var="show_update_for_all" value=true}
{/if}

{if "ULTIMATE"|fn_allowed_for && $settings.Stores.default_state_update_for_all == 'not_active' && !$runtime.simple_ultimate && !$runtime.company_id}
    {assign var="disable_input" value=true}
{/if}

<div id="content_storage{$st}">

<form action="{""|fn_url}" enctype="multipart/form-data" method="post" name="update_status_{$st}_form" class="form-horizontal">
<input type="hidden" name="storage_id" value="{$id}">

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
<fieldset>

    <div class="control-group">
        <label for="storage_{$id}" class="cm-required control-label">{__("name")}:</label>
        <div class="controls">
            <input type="text" size="70" id="storage_{$id}" name="storage_data[storage]" value="{$storage.storage}" class="input-large">
        </div>
    </div>

    {include file="views/companies/components/company_field.tpl"
        name="storage_data[company_id]"
        id="elm_storage_data_`$storage.storage_id`"
        selected=$storage.company_id
        zero_company_id_name_lang_var="none"
    }

    <div class="control-group">
        <label for="storage_code_{$id}" class="cm-required control-label">{__("storages.storage_code")}:</label>
        <div class="controls">
            <input type="text" size="70" id="storage_code_{$id}" name="storage_data[code]" value="{$storage.code}" class="input-large">
        </div>
    </div>

    {hook name="storages:storage_settings"}
    {if $storage.company_id}
        <div class="control-group">
            <label class="control-label">{__("usergroups")}:</label>
            <div class="controls">
                {include file="common/select_usergroups.tpl" id="ug_id" name="storage_data[usergroup_ids]" usergroups=["type"=>"C", "status"=>["A", "H"], "company_id" => $storage.company_id]|fn_get_usergroups:$smarty.const.DESCR_SL ug_ids=$storage.usergroup_ids usergroup_ids="" input_extra="" list_mode=false}
            </div>
        </div>
    {/if}
    {/hook}
    {include file="common/select_status.tpl" input_name="storage_data[status]" id="elm_storage_status_{$id}" obj=$storage}
</fieldset>
</div>


<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[storages.update]" cancel_action="close" save=$id}
</div>

</form>
<!--content_storage{$id}--></div>
