<table class="table table-tree">
    <thead>
    <tr>
        <th width="1%">{include file="common/check_items.tpl"}</th>
        <th width="99%">
            &nbsp;{__("product_variations.variations")}
        </th>
    </tr>
    </thead>
    {foreach from=$combinations item="combination" key="variation_code"}
        {include file="addons/product_variations/views/product_variations/components/variations_list_row.tpl"
            level=0
            combination=$combination
            variation_code=$variation_code
        }
    {/foreach}
</table>
