{** template-description:tmpl_grid **}

{if !$show_add_to_cart|defined}{$show_add_to_cart = true}{/if}

{include file="blocks/list_templates/grid_list.tpl"
show_name=true
show_old_price=$show_old_price|default:true
show_price=$show_price|default:true
show_rating=true
show_clean_price=true
show_list_discount=true
but_role="action"
show_features=true
show_product_labels=true
show_discount_label=true
show_shipping_label=true
show_list_buttons=$show_list_buttons|default:true
show_sku=true
hide_qty_label=true
}
