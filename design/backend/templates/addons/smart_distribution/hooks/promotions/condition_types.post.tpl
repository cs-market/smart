
{if $schema.conditions[$condition_data.condition].type == "file"}

    {include file="common/fileuploader.tpl" var_name="promotion_data[users_csv]" prefix="promo_"}
{/if}
