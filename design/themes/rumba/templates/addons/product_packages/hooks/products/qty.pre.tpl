{if $product.box_contains && $product.box_contains != 1 && $product.package_switcher == "YesNo::YES"|enum}
    <div class="ty-switcher-button">
        {include file="buttons/switch_button.tpl"
            id="switch_button_`$obj_id`"
            class="cm-switcher-button ty-left"
            data_on=__('product_packages.items')
            data_off=__('product_packages.packages')
            additional_attrs=['data-ca-step' => $product.qty_step, 'data-ca-items-in-package' => $product.items_in_package ]
        }
    </div>
{/if}
