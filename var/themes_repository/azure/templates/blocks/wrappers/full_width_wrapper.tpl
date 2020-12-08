{if $content|trim}
    <div class="ty-full-width-container clearfix{if isset($hide_wrapper)} cm-hidden-wrapper{/if}{if $hide_wrapper} hidden{/if}{if $block.user_class} {$block.user_class}{/if}" id="full-width-container_{$block.snapping_id}">
        {$content nofilter}
    </div>
{/if}

<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        var banner = context.find('#full-width-container_{$block.snapping_id}');

        if (banner.length) {
	     res = banner.closest('[class ^= span]', context).addClass('ty-grid-full-width');

        }
    });
}(Tygh, Tygh.$));
</script>