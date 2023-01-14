{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="storages_form" id="storages_form" class="form-horizontal form-edit cm-processed-form">
<input type="hidden" name="fake" value="1" />
{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}
{if $storages}
<div class="items-container type-methods {if $draggable}cm-sortable{/if}" {if $draggable}data-ca-sortable-table="types" data-ca-sortable-id-name="type_id"{/if} id="types_list">
    <div class="table-wrapper">
        <table class="table table-middle table--relative table-objects table-striped type-methods__list cm-filter-table" data-ca-input-id="elm_storage" data-ca-clear-id="elm_storage_clear">
            <tbody>
                {foreach $storages as $storage}
                    {include file="common/object_group.tpl"
                        id=$storage.storage_id
                        text=$storage.storage
                        href="storages.update?storage_id=`$storage.storage_id`"
                        object_id_name="storage_id"
                        table="storages"
                        href_delete="storages.delete?storage_id=`$storage.storage_id`"
                        href_desc=$storage.code
                        delete_target_id="types_list"
                        skip_delete=$skip_delete
                        header_text="{__("storages.editing_storage")}: `$storage.storage`"
                        additional_class="cm-sortable-row cm-sortable-id-`$storage.storage_id`"
                        no_table=true
                        draggable=$draggable
                        can_change_status=true
                        display=$display
                        status=$storage.status
                        tool_items=$smarty.capture.tool_items
                        extra_data=$smarty.capture.extra_data
                        company_object=$storage
                    }
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        {include file="addons/storages/views/storages/update.tpl"
            storage=[]
            hide_for_vendor=false
        }
    {/capture}
    {include file="common/popupbox.tpl"
        id="add_new_payments"
        text=__("storages.new_storage")
        content=$smarty.capture.add_new_picker
        title=__("storages.new_storage")
        act="general"
        icon="icon-plus"
    }
{/capture}

</form>
{/capture}

{capture name="sidebar"}
    {include file="addons/storages/views/storages/components/storages_search_form.tpl" dispatch="storages.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("storages.storages") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
