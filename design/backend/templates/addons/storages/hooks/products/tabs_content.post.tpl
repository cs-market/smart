<div class="hidden" id="content_storages">
    {if $storages}
    <div class="control-group">
        {script src="js/tygh/filter_table.js"}
        <label class="control-label" for="elm_storages_search">{__('search')}</label>
        <div class="controls">
            <input type="text" id="storages_search" value="" size="30">
        </div>
    </div>
    <div class="table-responsive-wrapper">
        <table class="table table-middle table--relative table-responsive cm-filter-table" width="100%" data-ca-input-id="storages_search">
            <thead>
                <tr>
                    <th>{__("storages.storage")}</th>
                    <th width="15%">{__("quantity")}</th>
                    <th width="15%">{__("min_order_qty")}</th>
                    <th width="15%">{__("quantity_step")}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $storages as $storage_id => $storage}
                    <tr class="cm-row-item cm-row-status-{$storage.status|strtolower}">
                        <td data-th="{__("storages.storage")}">
                            <a href="{"storages.update?storage_id=`$storage.storage_id`"|fn_url}"
                               class="row-status"
                               target="_blank"
                            >{$storage.storage}</a>
                            {if "ULTIMATE"|fn_allowed_for}
                                {include file="views/companies/components/company_name.tpl" object=$storage}
                            {/if}
                        </td>
                        <td data-th="{__("quantity")}">
                            <input type="text" name="product_data[storages][{$storage.storage_id}][amount]" value="{$storages_amounts.$storage_id.amount}" class="input-small {$class}"/>
                        </td>
                        <td data-th="{__("min_order_qty")}">
                            <input type="text" name="product_data[storages][{$storage.storage_id}][min_qty]" value="{$storages_amounts.$storage_id.min_qty}" class="input-small {$class}"/>
                        </td>
                        <td data-th="{__("quantity_step")}">
                            <input type="text" name="product_data[storages][{$storage.storage_id}][qty_step]" value="{$storages_amounts.$storage_id.qty_step}" class="input-small {$class}"/>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    {else}
         <p class="no-items">{__("no_data")}</p>
    {/if}
</div>
