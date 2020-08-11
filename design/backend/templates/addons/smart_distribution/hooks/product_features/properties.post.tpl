{if !"ULTIMATE"|fn_allowed_for}
    {include file="views/companies/components/company_field.tpl"
        name="feature_data[company_id]"
        id="elm_feature_data_`$id`"
        selected=$feature.company_id
    }
{/if}
