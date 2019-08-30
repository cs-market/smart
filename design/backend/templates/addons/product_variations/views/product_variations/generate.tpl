{if $combinations}
    <form action="{"product_variations.generate"|fn_url}" name="generate_variations_form" method="post">
        <input type="hidden" name="product_id" value="{$product_data.product_id}" />

        <div class="items-container">
            {$first_combination = $combinations|reset}
            {$levels_count = $first_combination.selected_options|count}

            {if $levels_count > 1}
                {include file="addons/product_variations/views/product_variations/components/variations_grouped_list.tpl"
                    combinations=$combinations
                }
            {else}
                {include file="addons/product_variations/views/product_variations/components/variations_list.tpl"
                    combinations=$combinations
                }
            {/if}
        </div>

        <div class="buttons-container">
            <a class="cm-dialog-closer cm-cancel tool-link btn">{__("cancel")}</a>
            {include file="buttons/button.tpl" but_text=__("product_variations.add_variations") but_role="submit-link" but_name="dispatch[product_variations.generate]" but_meta="btn-primary"}
        </div>
    </form>
{else}
    <p class="no-items">{__("product_variations.inventory_notice")}</p>
    <div class="buttons-container">
        <a class="cm-dialog-closer cm-cancel tool-link btn">{__("close")}</a>
    </div>
{/if}
