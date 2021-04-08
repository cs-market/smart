{if !$elm_id}
    {math equation="rand()" assign="elm_id"}
{/if}
<div class='cm-row-item control-group' id="container_{$elm_id}">
    {$value = $value|default:$property.value}
    <label class="control-label">{$property.label} {if $property.tooltip}{include file="common/tooltip.tpl" tooltip=$property.tooltip}{/if}: </label>
    <div class="controls clearfix">
        {if $property.prefix}
            <span style="line-height: 30px;"> {$property.prefix} </span>
        {/if}
        
        {if $property.type == 'checkbox'}
            <input type="hidden" name="sticker_data[properties][{$property.name}]" value="N">
            <input class="{$property.class}" type="checkbox" name="sticker_data[properties][{$property.name}]" value="Y" {if $value == 'Y'}checked="checked"{/if}>
        {elseif $property.type == 'select'}
            <select name="sticker_data[properties][{$property.name}]" class="{$property.class}">
                {foreach from=$property.variants item='name' key='variant'}
                    <option value="{$variant}" {if $value == $variant} selected="selected" {/if}>{$variant}</option>
                {/foreach}
            </select>
        {elseif $property.type == 'colorpicker'}
            {include file="common/colorpicker.tpl"
                cp_name="sticker_data[properties][`$property.name`]"
                cp_value="{$value|default:'#ffffff'}"
                cp_id={$elm_id}
                show_picker=true
            }
        {elseif $property.type == 'slider'}
            <input type="hidden" id="value_{$elm_id}" name="sticker_data[properties][{$property.name}]" value="{$value}">
            <div id="slider_{$elm_id}" class="{$property.class}"><div id="custom-handle" class="ui-slider-handle" style="height: 24px;width: 40px;text-align: center; top: 50%;margin-top: -12px;margin-left: -20px;">{$value}</div></div>
            <script type="text/javascript">
                var handle = $( "#custom-handle" );
                $("#slider_{$elm_id}").slider({
                    min: 0,
                    max: 1,
                    step: 0.05,
                    {if $value}
                        value: {$value},
                    {/if}
                    slide: function (event, ui) {
                        $('#value_{$elm_id}').val(ui.value);
                        handle.text( ui.value );
                    }
                });
            </script>
        {else}
            <input type="text" name="sticker_data[properties][{$property.name}]" value="{$value}" class="{$property.class}">
        {/if}
        {if $property.suffix}
            <span style="line-height: 30px;"> {$property.suffix} </span>
        {/if}
        <div class="pull-right">
            <a class="icon-trash cm-tooltip cm-delete-row" name="remove" style="line-height: 30px;"></a>
        </div>
    </div>
<!--container_{$elm_id}--></div>
