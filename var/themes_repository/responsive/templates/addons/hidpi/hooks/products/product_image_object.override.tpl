<img class="ty-pict {$valign} {$class} {if $lazy_load}lazyOwl{/if} {if $generate_image}ty-spinner{/if}"  {if $obj_id && !$no_ids}id="det_img_{$obj_id}"{/if} {if $generate_image}data-ca-image-path="{$image_data.image_path}"{/if} {if $lazy_load}data-{/if}src="{if $generate_image}{$images_dir}/icons/spacer.gif{else}{$image_data.image_path}{/if}" alt="{$image_data.alt}" title="{$image_data.alt}" {if $image_onclick}onclick="{$image_onclick}"{/if} {if $lazy_load && ($image_width || $image_height)}data-width="{$image_width|default:$image_height}" data-height="{$image_height|default:$image_width}"{/if} />