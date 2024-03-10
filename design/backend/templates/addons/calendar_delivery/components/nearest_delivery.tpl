<div class="control-group">
    <label for="elm_{$id}" class="control-label">{__("calendar_delivery.nearest_delivery")}:</label>
    <div class="controls">
        <label class="radio inline" for="{$id}_today"><input type="radio" name="{$name}" id="{$id}_today" {if $params.nearest_delivery == '0'}checked="checked"{/if} value="0" {if $disabled}disabled="_disabled"{/if} onclick="Tygh.$('#{$id}_other_wrapper').switchAvailability(true, false);">{__('today')}</label>
        <label class="radio inline" for="{$id}_tomorrow"><input type="radio" name="{$name}" id="{$id}_tomorrow" {if $params.nearest_delivery == '1'}checked="checked"{/if} value="1" {if $disabled}disabled="_disabled"{/if} onclick="Tygh.$('#{$id}_other_wrapper').switchAvailability(true, false);">{__('tomorrow')}</label>
        <label class="radio inline" for="{$id}_aftertomorrow" onclick="Tygh.$('#{$id}_other_wrapper').switchAvailability(true, false);"><input type="radio" name="{$name}" id="{$id}_aftertomorrow" {if $params.nearest_delivery == '2'}checked="checked"{/if} value="2" {if $disabled}disabled="_disabled"{/if}>{__('day_after_tomorrow')}</label>
        <label class="radio inline" for="{$id}_other"><input type="radio" name="{$name}" id="{$id}_other" {if $params.nearest_delivery > '2'}checked="checked"{/if} {if $disabled}disabled="_disabled"{/if} onclick="Tygh.$('#{$id}_other_wrapper').switchAvailability(false, false);">{__('calendar_delivery.other')}</label>
        <span id="{$id}_other_wrapper"><input class="input-micro" id="{$id}_other_input" type="text" name="{$name}" {if $params.nearest_delivery > '2'}value="{$params.nearest_delivery}"{else}disabled="_disabled"{/if}></span>
    </div>
</div>
