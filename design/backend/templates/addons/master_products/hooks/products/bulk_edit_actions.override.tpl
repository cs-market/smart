{if $show_bulk_edit_actions|default:false}
    <li>
        {btn
            type="list"
            text=__("clone_selected")
            dispatch="dispatch[products.m_clone]"
            form="manage_products_form"
        }
    </li>

    <li>
        {btn
            type="list"
            text=__("export_selected")
            dispatch="dispatch[products.export_range.master]"
            form="manage_products_form"
        }
    </li>

    <li>
        {btn
            type="delete_selected"
            dispatch="dispatch[products.m_delete]"
            form="manage_products_form"
        }
    </li>
{/if}