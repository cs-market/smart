{** template-description:compact_list **}
{* Added show_product_amount *}
{include file="blocks/list_templates/compact_list.tpl"
show_name=true
show_sku=true
show_price=true
show_old_price=true
show_clean_price=true
show_add_to_cart=$show_add_to_cart|default:true
but_role="action"
hide_form=true
hide_qty_label=true
show_product_labels=false
show_discount_label=false
show_shipping_label=false
show_product_amount=true
}