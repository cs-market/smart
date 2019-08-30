{$is_sortable = $id > 0}
<tr id="box_elm_location_row_{$id}"
    class="ty-rus-customer-geolocation-location {if $is_sortable}cm-row-item cm-sortable-row{/if}"
>
    <td width="1%" class="no-padding-td mobile-hide">
        <span class="handler cm-sortable-handle {if !$is_sortable}hidden{/if}"></span>
    </td>
    <td data-th="{__("country")}">
        {include file="addons/rus_customer_geolocation/views/rus_customer_geolocation/components/countries.tpl"
                 selected=$country_id
                 name="locations[`$id`][country]"
        }
    </td>
    <td data-th="{__("state")}">
        {include file="addons/rus_customer_geolocation/views/rus_customer_geolocation/components/states.tpl"
                 selected=$state_id
                 name="locations[`$id`][state]"
                 states=$states.$country_id
        }
    </td>
    <td data-th="{__("city")}">
        <input type="text"
               name="locations[{$id}][city]"
               class="ty-rus-customer-geolocation-location__city"
               id="elm_city_{$id}"
               value="{$city}"
        />
    </td>
    <td class="right">
        <div class="hidden-tools">
            {include file="buttons/multiple_buttons.tpl"
                     item_id="elm_location_row_{$id}"
                     tag_level=1
                     simple=($id > 0)
                     hide_clone=true
                     on_add="\$.ceRusCustomerGeolocationLocationsList('rebuild');"
            }
        </div>
    </td>
</tr>