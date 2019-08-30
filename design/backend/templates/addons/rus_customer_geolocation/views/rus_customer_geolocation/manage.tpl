{capture name="mainbox"}
    <form action="{""|fn_url}"
          method="post"
          name="rus_customer_geolocation_update_locations"
    >
        {include file="views/profiles/components/profiles_scripts.tpl"}

        <div class="table-responsive cm-sortable">
            <table class="table table-middle">
                <thead>
                <tr class="cm-first-sibling">
                    <th class="mobile-hide">&nbsp;</th>
                    <th>{__("country")}</th>
                    <th>{__("state")}</th>
                    <th>{__("city")}</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                {$id = 1}
                {foreach $locations|default:[] as $country_id => $country}
                    {foreach $country.states as $state_id => $state}
                        {foreach $state.cities as $city}
                            {include file="addons/rus_customer_geolocation/views/rus_customer_geolocation/components/location.tpl"}
                            {$id = $id + 1}
                        {/foreach}
                    {/foreach}
                {/foreach}
                {include file="addons/rus_customer_geolocation/views/rus_customer_geolocation/components/location.tpl"
                         country_id=""
                         state_id=""
                         city=""
                         id=0
                }
            </table>
        </div>
    </form>
{/capture}

{capture name="buttons"}
    {include file="buttons/save.tpl"
             but_name="dispatch[rus_customer_geolocation.update]"
             but_role="action"
             but_target_form="rus_customer_geolocation_update_locations"
             but_meta="cm-submit btn-primary"
    }
{/capture}

{include file="common/mainbox.tpl"
         title=__("rus_customer_geolocation.locations")
         content=$smarty.capture.mainbox
         buttons=$smarty.capture.buttons
}