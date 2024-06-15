{if !$auth.user_id && $product.company_id == 45}
    {capture name="buttons_product"}
        {capture name="anonymous_buy"}
            <p>
                {__('anonymous_catalog.auth_required')}
            </p>
            <div class="buttons-container">
                {include file="buttons/button.tpl" but_href="pages.view&page_id=169" but_meta="ty-btn__primary" but_text=__('register')}
                {include file="buttons/button.tpl" but_href="auth.baltika_login_form" but_meta="ty-btn__primary ty-float-right" but_text=__('authorization')}
            </div>
        {/capture}

        {include file="common/popupbox.tpl"
            link_text=__('buy')
            link_meta="ty-btn ty-btn__primary ty-btn__full-width ty-btn__big"
            title=__("buy")
            id="anonymous_buy"
            content=$smarty.capture.anonymous_buy
        }
    {/capture}
{/if}
