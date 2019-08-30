{** block-description:tmpl_grid **}

{if $block.properties.hide_add_to_cart_button == "Y"}
    {assign var="_show_add_to_cart" value=false}
{else}
    {assign var="_show_add_to_cart" value=true}
{/if}
{if $block.properties.show_list_buttons == "Y"}
    {assign var="_show_list_buttons" value=true}
{else}
    {assign var="_show_list_buttons" value=false}
{/if}


{include file="blocks/list_templates/grid_list.tpl"
products=$items
columns=$block.properties.number_of_columns
form_prefix="block_manager"
no_sorting="Y"
no_pagination="Y"
no_ids="Y"
obj_prefix="`$block.block_id`000"
item_number=$block.properties.item_number
show_name=true
show_old_price=true
show_price=true
show_rating=true
show_clean_price=true
show_list_discount=true
show_add_to_cart=$_show_add_to_cart
show_list_buttons=$_show_list_buttons
but_role="action"
show_discount_label=true}