{if $category_data.category_id}

{capture name="clone_category"}
    {include file="addons/smart_distribution/views/categories/components/clone_category.tpl" object_name=$value_name}
{/capture}

<li class="divider"></li>
<li>{btn type="list" class="cm-process-items" text=__("add_category_to_products") onclick="Tygh.$('.cm-choose-products').click()"}</li>
<li>
  {include
    file="common/popupbox.tpl"
    id="clone_category"
    text=__("clone")
    content=$smarty.capture.clone_category
    act="edit"
    title=__("clone")
    link_text=__("clone")
  }
</li>
<li class="divider"></li>

{include file="pickers/products/picker.tpl"  extra_var="products.m_add_category&category_id=`$category_data.category_id`" data_id="select_products" no_container=true but_text="{__("advanced_products_search")}" show_but_text=false meta="hidden cm-choose-products"}
{/if}