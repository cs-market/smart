{** block-description:carousel **}

{if $items}
    <div id="banner_slider_{$block.snapping_id}" class="banners owl-carousel {if $block.properties.item_quantity}ty-banner__scroller-grid{/if}">
        {foreach from=$items item="banner" key="key"}
            <div class="ty-banner__image-item">
                {if $banner.type == "G" && $banner.main_pair.image_id}
                    {if $banner.url != ""}<a class="banner__link" href="{$banner.url|fn_url}" {if $banner.target == "B"}target="_blank"{/if}>{/if}
                        {include file="common/image.tpl" images=$banner.main_pair class="ty-banner__image" }
                    {if $banner.url != ""}</a>{/if}
                {else}
                    <div class="ty-wysiwyg-content">
                        {$banner.description nofilter}
                    </div>
                {/if}
            </div>
        {/foreach}
    </div>
{/if}

<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        var slider = context.find('#banner_slider_{$block.snapping_id}');
        if (slider.length) {
            slider.owlCarousel({
                direction: '{$language_direction}',
                items: {$block.properties.item_quantity|default:3},
                singleItem : false,
                slideSpeed: {$block.properties.speed|default:400},
                autoPlay: '{$block.properties.delay * 1000|default:false}',
                stopOnHover: true,
                {if $block.properties.scroll_per_page == "Y"}
                    scrollPerPage: true,
                {/if}
                {if $block.properties.navigation == "N"}
                    pagination: false
                {/if}
                {if $block.properties.navigation == "D"}
                    pagination: true
                {/if}
                {if $block.properties.navigation == "P"}
                    pagination: true,
                    paginationNumbers: true
                {/if}
                {if $block.properties.navigation == "A"}
                    pagination: false,
                    navigation: true,
                    navigationText: ['{__("prev_page")}', '{__("next")}']
                {/if}
            });
        }
    });
}(Tygh, Tygh.$));
</script>
