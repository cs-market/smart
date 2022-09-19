<div id="content_user_price" class="hidden">
    {include file="common/subheader.tpl" title=__("user_price") target="#user_price_products_hook"}
    <div id="user_price_products_hook" class="in collapse">
      <div id="user_price_search">
      <!--user_price_search--></div>
      <div id="user_price_pagination">
      <!--user_price_pagination--></div>
    </div>
</div>
<script type="text/javascript">
  (function(_, $) {
    $(document).ready(function() {
      const product_id = {$product_data.product_id};
      const company_id = {$product_data.company_id};
      Tygh.$.ceAjax('request',
        fn_url("products.get_user_price"),
        {
          method: 'get',
          result_ids: 'user_price_*',
          hidden: true,
          data: {
            product_id: product_id,
            name: 'product_data[user_price]',
            company_id: company_id
          }
        }
      );
    });
  }(Tygh, Tygh.$));
</script>
