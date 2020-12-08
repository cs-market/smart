{hook name="blocks:sidebox_dropdown"}{strip}
{assign var="foreach_name" value="item_`$iid`"}

{foreach from=$items item="item" name=$foreach_name}
{hook name="blocks:sidebox_dropdown_element"}

    <li class="ty-menu__item cm-menu-item-responsive {if $item.$childs}dropdown-vertical__dir{/if}{if $item.active || $item|fn_check_is_active_menu_item:$block.type} ty-menu__item-active{/if} menu-level-{$level}{if $item.class} {$item.class}{/if}">
        {if $item.$childs}
            <div class="ty-menu__item-toggle visible-phone cm-responsive-menu-toggle">
                <i class="ty-menu__icon-open ty-icon-down-open"></i>
                <i class="ty-menu__icon-hide ty-icon-up-open"></i>
            </div>
            <div class="ty-menu__item-arrow hidden-phone">
                <i class="ty-icon-right-open"></i>
                <i class="ty-icon-left-open"></i>
            </div>
        {/if}

        {assign var="item_url" value=$item|fn_form_dropdown_object_link:$block.type}
        <div class="ty-menu__submenu-item-header">
            <a{if $item_url} href="{$item_url}"{/if} {if $item.new_window}target="_blank"{/if} class="ty-menu__item-link">{$item.$name}</a>
        </div>

        {if $item.$childs}
            {hook name="blocks:sidebox_dropdown_childs"}
            <div class="ty-menu__submenu">
                <ul class="ty-menu__submenu-items cm-responsive-menu-submenu">
                    {include file="blocks/sidebox_dropdown.tpl" items=$item.$childs separated=true submenu=true iid=$item.$item_id level=$level+1}
                </ul>
            </div>
            {/hook}
        {elseif $item.product}
            <div class="ty-menu__submenu">
                <ul class="ty-menu__submenu-items cm-responsive-menu-submenu ty-menu-product">
                    <li class="ty-menu__item cm-menu-item-responsive {if $item.$childs}dropdown-vertical__dir{/if}{if $item.active || $item|fn_check_is_active_menu_item:$block.type} ty-menu__item-active{/if} menu-level-{$level}{if $item.class} {$item.class}{/if}">
                    <p class="menu-subheader ty-center">{__("azure_menu_subheader")}</p>
                    {include file="blocks/list_templates/grid_list.tpl"
                        show_name=true
                        show_old_price=true
                        show_price=true
                        show_rating=true
                        show_clean_price=true
                        show_list_discount=true
                        show_list_buttons=true
                        show_add_to_cart=true
                        but_role="act"
                        but_text=__("buy")
                        show_discount_label=true
                        no_pagination = true
                        no_sorting = true
                        products = [$item.product]
                        image_height = $addons.azure_theme.menu_product_image_height
                        image_width = $addons.azure_theme.menu_product_image_width
                        }
                    </li>
                </ul>
            </div>
        {/if}
    </li>
{/hook}

{/foreach}
{/strip}{/hook}