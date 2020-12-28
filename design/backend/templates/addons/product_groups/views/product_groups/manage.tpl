{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="stickers_form" id="stickers_form" class="form-horizontal form-edit cm-processed-form">
<input type="hidden" name="fake" value="1" />
{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}
{if $product_groups}
<div class="items-container type-methods {if $draggable}cm-sortable{/if}"
     {if $draggable}data-ca-sortable-table="types" data-ca-sortable-id-name="type_id"{/if}
     id="types_list">
<div class="table-wrapper">
    <table class="table table-middle table--relative table-objects table-striped type-methods__list">
        <tbody>
            {foreach $product_groups as $group}
                {include file="common/object_group.tpl"
                    id=$group.group_id
                    text=$group.group
                    href="product_groups.update?group_id=`$group.group_id`"
                    object_id_name="group_id"
                    table="product_groups"
                    href_delete="product_groups.delete?group_id=`$group.group_id`"
                    delete_target_id="types_list"
                    skip_delete=$skip_delete
                    header_text="{__("editing_group")}: `$group.group`"
                    additional_class="cm-sortable-row cm-sortable-id-`$group.group_id`"
                    no_table=true
                    draggable=$draggable
                    can_change_status=true
                    display=$display
                    status=$group.status
                    tool_items=$smarty.capture.tool_items
                    extra_data=$smarty.capture.extra_data
                    company_object=$group
                }
            {/foreach}
        </tbody>
    </table>
</div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        {include file="addons/product_groups/views/product_groups/update.tpl"
            product_group=[]
            hide_for_vendor=false
        }
    {/capture}
    {include file="common/popupbox.tpl"
        id="add_new_payments"
        text=__("product_groups.new_product_group")
        content=$smarty.capture.add_new_picker
        title=__("product_groups.add_product_group")
        act="general"
        icon="icon-plus"
    }
{/capture}

</form>
{/capture}

{include file="common/mainbox.tpl" title=__("product_groups") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons content_id="manage_users" select_languages=true}
