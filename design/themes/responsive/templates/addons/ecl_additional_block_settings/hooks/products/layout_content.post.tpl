{*if $smarty.const.MOBILE_VIEW == 'Y'}
<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    $(document).ready(function(){
        $.scrollToElm($('.ty-product-block'));
		$.scrollToElm($('.product-main-info'));
    });
}(Tygh, Tygh.$));
//]]>
</script>
{/if*}
