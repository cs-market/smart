{math equation="rand()" assign="rnd"}
{assign var="data_id" value="`$data_id`_`$rnd`"}
{assign var="view_mode" value=$view_mode|default:"mixed"}
{assign var="show_but_text" value=$show_but_text|default:"true"}

{script src="js/tygh/picker.js"}

{if $item_ids && !$item_ids|is_array}
    {assign var="item_ids" value=","|explode:$item_ids}
{/if}

{assign var="display" value=$display|default:"checkbox"}

{if $view_mode == "user_price_button"}
    {if $user_info}
        {$user_name = "`$user_info.firstname` `$user_info.lastname`(`$user_info.email`)"}
        {$item_ids = $user_info.user_id}
    {else}
      {$user_name = ''}
    {/if}

    {$_but_text=__("choose_user")}
    <div class="mixed-controls">
    <div class="form-inline">
    <span id="{$data_id}" class="cm-js-item cm-display-radio">

    <div class="input-append">
    <input class="cm-picker-value-description {$extra_class}" type="text" value="{$user_name}" {if $display_input_id}id="{$display_input_id}"{/if} size="30" name="user_name" readonly="readonly" {$extra}>

    {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="profiles.picker?display=`$display`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&root=`$default_name`&except_id=`$except_id`&data_id=`$data_id``$extra_url`"|fn_url but_role="text" but_icon="icon-plus" but_target_id="content_`$data_id`" but_meta="`$but_meta` cm-dialog-opener add-on btn"}

    <input id="{if $input_id}{$input_id}{else}u{$data_id}_ids{/if}" type="hidden" class="cm-picker-value" name="{$input_name}" value="{if $item_ids|is_array}{","|implode:$item_ids}{else}{$item_ids}{/if}" {$extra} />

    </div>
    </span>
    </div>
    </div>
{/if}

{if $view_mode != "list"}
    <div class="hidden" id="content_{$data_id}" title="{$_but_text}">
    </div>
{/if}
