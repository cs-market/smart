<div class="group">
    <div class="sidebar-field">
        <label class="control-label">{__("storages")}</label>
        <div class="controls">
            <select name="storage_id">
                <option value="">---</option>
                {foreach from=['status' => 'A']|fn_get_storages|reset item="storage"}
                    <option value="{$storage.storage_id}" {if $storage.storage_id == $user_storage.storage_id}selected="_selected"{/if}>{$storage.storage}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>
