{if $data.type == "input"}
    <input type="text" name="status_data[params][{$name}]" id="status_param_{$id}_{$name}" value="{$status_data.params.$name}"/>
{/if}
{if $data.type == "textarea"}
    <textarea
        id="status_param_{$id}_{$name}"                                    
        name="status_data[params][{$name}]"
        cols="55"
        rows="8"
        class="input-slarge"
    >{$status_data.params.$name}</textarea>
{/if}
