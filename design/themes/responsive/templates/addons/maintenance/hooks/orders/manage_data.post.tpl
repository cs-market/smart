{if $orders|array_column:'tracking_link'|array_filter}
    <td>{if $o.tracking_link}<a href="{$o.tracking_link}" target="_blank"><span class="ty-icon ty-icon-aurora-shipping"></span></a>{/if}</td>
{/if}
