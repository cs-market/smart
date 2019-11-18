<form action="{""|fn_url}" method="post" name="clone_category_form" class="form-horizontal form-edit ">
  <input type="hidden" name="clone_category[category_id]" value="{$id}" />

  <div class="add-new-object-group">
    <fieldset>
      <div class="control-group">
          <label for="clone_category__clone_subcat" class="control-label">{__("clone_category__clone_subcat")}:</label>
          <div class="controls">
              <input type="hidden" name="clone_category[clone_subcat]" value="N">
              <span class="checkbox">
                  <input type="checkbox" id="clone_category__clone_subcat" name="clone_category[clone_subcat]" value="Y" checked="checked">
              </span>
          </div>
      </div>

      <div class="control-group">
        <label for="clone_category_clone_products" class="control-label">{__("clone_category_clone_products")}:</label>
        <div class="controls" id="clone_category_clone_products">

          <label for="clone_category_clone_products__no_copy" class="radio">
          <input type="radio" name="clone_category[clone_products]" id="clone_category_clone_products__no_copy" value="no_copy" />
          {__("clone_category_clone_products__no_copy")}</label>

          <label for="clone_category_clone_products__copy" class="radio">
          <input type="radio" name="clone_category[clone_products]" id="clone_category_clone_products__copy" value="copy" checked="checked" />
          {__("clone_category_clone_products__copy")}</label>

          <label for="clone_category_clone_products__clone" class="radio">
          <input type="radio" name="clone_category[clone_products]" id="clone_category_clone_products__clone" value="clone" />
          {__("clone_category_clone_products__clone")}</label>

        </div>
      </div>

      <div class="control-group">
          <label for="clone_category_change_vendor" class="control-label">{__("clone_category_change_vendor")}:</label>
          <div class="controls">
            {include file="views/companies/components/company_field.tpl"
              name="clone_category[company_id]"
              id="elm_company_id"
              zero_company_id_name_lang_var="none"
              no_wrap=true
            }
          </div>
        </div>

    </fieldset>
  </div>

  <div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[categories.clone]" cancel_action="close"}
  </div>

</form>
