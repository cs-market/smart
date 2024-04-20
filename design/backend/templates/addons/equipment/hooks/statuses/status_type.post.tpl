{if $data.type == "input"}
    <input type="text" name="status_data[params][{$name}]" id="status_param_{$id}_{$name}" value="{$status_data.params.$name}"/>
{/if}
