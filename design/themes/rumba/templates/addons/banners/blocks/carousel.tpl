{** block-description:carousel **}

{if $items}
    {$obj_prefix="`$block.block_id`000"}

    {if $block.properties.navigation == "O"}
        <div class="owl-theme ty-owl-controls">
            <div class="owl-controls clickable owl-controls-outside"  id="owl_outside_nav_{$block.block_id}">
                <div class="owl-buttons">
                    <div id="owl_prev_{$obj_prefix}" class="owl-prev"><i class="ty-icon-left-open-thin"></i></div>
                    <div id="owl_next_{$obj_prefix}" class="owl-next"><i class="ty-icon-right-open-thin"></i></div>
                </div>
            </div>
        </div>
    {/if}
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

<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        var slider = context.find('#banner_slider_{$block.snapping_id}');

        {if $block.properties.navigation == "O"}
        function outsideNav () {
            if(this.options.items >= this.itemsAmount){
                $("#owl_outside_nav_{$block.block_id}").hide();
            } else {
                $("#owl_outside_nav_{$block.block_id}").show();
            }
        }
        {/if}

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
                {if $block.properties.navigation == "O"}
                    pagination: false,
                    navigation: false,
                {/if}
            });
            {if $block.properties.navigation == "O"}
                $('#owl_prev_{$obj_prefix}').click(function(){
                    slider.trigger('owl.prev');
                });
                $('#owl_next_{$obj_prefix}').click(function(){
                    slider.trigger('owl.next');
                });
            {/if}
        }
    });
}(Tygh, Tygh.$));
</script>

{/if}
