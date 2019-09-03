{assign var="zero_company_id_name_lang_var" value="none"}
{include file="views/companies/components/company_field.tpl"
    name="promotion_data[company_id]"
    id="promotion_data_company_id"
    selected=$promotion_data.company_id
    tooltip=$companies_tooltip
}