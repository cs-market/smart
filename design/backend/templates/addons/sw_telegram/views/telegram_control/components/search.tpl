<div class="sidebar-row" id="sw_telegram">
    <h6>{__("search")}</h6>

<form action="{""|fn_url}" name="data_search_form" method="get" id="access_detail" class="form-horizontal {$form_meta}" enctype="multipart/form-data">

<input type="hidden" name="result_ids" value="sw_telegram">
<input type="hidden" name="return_url" value="{$config.current_url}">


{capture name="simple_search"}
    
<div class="sidebar-field">
	<label>E-mail</label>
	<input type="text" name="email" size="20" value="{$search.email}" class="search-input-text" />
</div>

<div class="sidebar-field">
	<label>{__("phone")}</label>
	<input type="text" name="phone" size="20" value="{$search.phone}" class="search-input-text" />
</div>

<div class="sidebar-field">
	<label>{__("sw_telegram.chat_id")}</label>
	<input type="text" name="chat_id" size="20" value="{$search.chat_id}" class="search-input-text" />
</div>

<div class="sidebar-field">
	<label>{__("sw_telegram.confirm_noty_short")}</label>
	<select name="noty_tg" id="noty_tg">
        <option value="">{__("all")}</option>
        <option {if $search.noty_tg == 'Y'}selected="selected"{/if} value="Y">{__("yes")}</option>
        <option {if $search.noty_tg == 'N'}selected="selected"{/if} value="N">{__("no")}</option>
	</select>
</div>

<div class="sidebar-field">
	<label>{__("user_type")}</label>
	<select name="user_type" id="user_type">
        <option value="">{__("all")}</option>
        <option {if $search.user_type == 'C'}selected="selected"{/if} value="C">{__("user")}</option>
        <option {if $search.user_type == 'V'}selected="selected"{/if} value="V">{__("vendor")}</option>
	</select>
</div>

{/capture}

{include file="common/advanced_search.tpl" no_adv_link=true simple_search=$smarty.capture.simple_search dispatch="telegram_control.manage" view_type="sw_category_tags"}

</form>


<!--sw_telegram--></div><hr>