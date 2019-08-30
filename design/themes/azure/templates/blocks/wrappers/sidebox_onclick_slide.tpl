{assign var="slide_id" value=$block.snapping_id}

{if $content|trim}
    <div class="ty-slide {if $block.user_class} {$block.user_class}{/if}{if $content_alignment == "RIGHT"} ty-float-right{elseif $content_alignment == "LEFT"} ty-float-left{/if}">
        <div id="sw_slide_{$slide_id}" class="ty-slide__title cm-combination">
            {hook name="wrapper:onclick_dropdown_title"}
            {if $smarty.capture.title|trim}
                {$smarty.capture.title nofilter}
            {else}
                <a>{$title nofilter}</a>
            {/if}
            {/hook}
        </div>
        <div class="ty-slide__container cm-popup-box" id='slide_{$slide_id}'>
            <i id="off_slide_{$slide_id}" class="ty-icon-close cm-combination"></i>
            <div class="ty-slide__content">
                {$content|default:"&nbsp;" nofilter}
            </div>
        </div>
    </div>
{/if}
