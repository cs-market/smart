{** block-description:dropdown_vertical **}

<div class="ty-menu ty-menu-vertical">
    <ul id="vmenu_{$block.block_id}" class="ty-menu__items{if $block.properties.right_to_left_orientation =="Y"} rtl{/if}">
        {include file="blocks/sidebox_dropdown.tpl" items=$items separated=true submenu=false name="category" item_id="category_id" childs="subcategories"}
    </ul>
</div>
