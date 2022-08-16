{if $order_info.allow_order_review}
    {$obj_id = $order_info.order_id}
    {capture name="order_rating_popup"}
        <div class="ty-discussion-post-popup{if $meta} {$meta}{/if}" id="new_post_dialog_{$obj_prefix}{$obj_id}" title="{$new_post_title}">
            <form action="{""|fn_url}" method="post" class="{if !$post_redirect_url}cm-ajax cm-form-dialog-closer{/if} posts-form" name="add_post_form" id="add_post_form_{$obj_prefix}{$obj_id}">
                <input type="hidden" name="result_ids" value="posts_list*,new_post*,average_rating*">
                <input type ="hidden" name="post_data[object_id]" value="{$obj_id}" />
                <input type ="hidden" name="post_data[company_id]" value="{$order_info.company_id}" />
                <input type ="hidden" name="post_data[object_type]" value="{"Addons\\Discussion\\DiscussionObjectTypes::ORDER"|enum}" />
                <input type ="hidden" name="redirect_url" value="{$post_redirect_url|default:$config.current_url}" />
                <input type="hidden" name="selected_section" value="" />

                <div id="new_post_{$obj_prefix}{$obj_id}">
                    <div class="ty-control-group">
                        {$rate_id = "rating_`$obj_prefix``$obj_id`"}
                        <label for="{$rate_id}" class="ty-control-group__title cm-required cm-multiple-radios">{__("your_rating")}</label>
                        {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[rating_value]"}
                    </div>

                    <div class="ty-control-group">
                        <label for="dsc_message_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_message")}</label>
                        <textarea id="dsc_message_{$obj_prefix}{$obj_id}" name="post_data[message]" class="ty-input-textarea ty-input-text-large" rows="5" cols="72">{$discussion.post_data.message}</textarea>
                    </div>

                    {include file="common/image_verification.tpl" option="discussion"}
                <!--new_post_{$obj_prefix}{$obj_id}--></div>

                <div class="buttons-container">
                    {include file="buttons/button.tpl" but_text=__("submit") but_meta="ty-btn__secondary cm-dialog-closer cm-ajax" but_role="submit" but_name="dispatch[order_reviews.add]"}
                </div>
            </form>
        <!--new_post_dialog_{$obj_prefix}{$obj_id}--></div>
    {/capture}

    {include file="common/popupbox.tpl"
        link_text=__("")
        title=__("order_reviews.leave_review")
        id="order_reviews_switcher"
        content=$smarty.capture.order_rating_popup
        wysiwyg=false
        dialog_additional_attrs=["data-ca-dialog-class" => "ty-order-reviews__dialog"]
    }
    <script>
        $(document).ready(function(){
            $('#opener_order_reviews_switcher').click();
        });
    </script>
{/if}
