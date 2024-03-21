{** block-description:grid **}
{if $items}
    {$columns = $block.properties.number_of_columns|default:3}
    {if $items|count < $columns}{$columns = $items|count}{/if}
    <div id="banner_grid_{$block.snapping_id}" class="banners ty-grid-banners" style="--grid-columns: {$columns}">
        {foreach from=$items item="banner" key="key"}
            <div class="ty-banner__image-item">
                {if $banner.type == "G" && $banner.main_pair.image_id}
                    {if $banner.url != ""}<a class="banner__link" href="{$banner.url|fn_url}" {if $banner.target == "B"}target="_blank"{/if}>{/if}
                        {include file="common/image.tpl" images=$banner.main_pair class="ty-banner__image" }
                    {if $block.properties.section_name == "YesNo::YES"|enum}<div>{$banner.banner}</div> {/if}
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
