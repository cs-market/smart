{** block-description:original **}
{foreach from=$items item="banner" key="key"}
	<div class="ty-banner__image-wrapper">
		{if $banner.url != ""}<a href="{$banner.url|fn_url}" {if $banner.target == "B"}target="_blank"{/if}>{/if}
		{if $banner.type == "G" && $banner.main_pair.image_id}
			{include file="common/image.tpl" images=$banner.main_pair image_auto_size=true}
		{/if}
		{if $banner.url != ""}</a>{/if}
		{if $banner.description}
			<div class="ty-wysiwyg-content banner-description">
				{$banner.description nofilter}
			</div>
		{/if}  
	</div>
{/foreach}