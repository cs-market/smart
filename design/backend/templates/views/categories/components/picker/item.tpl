<div class="object-picker__categories-main">
    {if $type === "result" || $type === "selection"}
        <div class="object-picker__categories-main-content">
            {$title_pre} 
            {literal}${data.name}{/literal} 
            {$title_post}
        </div>
    {elseif $type === "load"}
        ...
    {elseif $type === "new_item"}
        <div class="object-picker__results-label object-picker__results-label--categories">
            {if $icon|default:true}
                <div class="object-picker__results-label-icon-wrapper object-picker__results-label-icon-wrapper--categories">
                    <i class="object-picker__results-label-icon object-picker__results-label-icon--categories {$icon|default:"icon-plus-sign"}"></i>
                </div>
            {/if}
            {if $title_pre}
                <div class="object-picker__results-label-prefix object-picker__results-label-prefix--categories">
                    {$title_pre}
                </div>
            {/if}
            <div class="object-picker__results-label-body object-picker__results-label-body--categories">
                <span class="select2-selection__choice__handler"></span>
                {literal}${data.text}{/literal}
            </div>
        </div>

        {if $help}
            <div class="object-picker__results-help object-picker__results-help--categories">
                {__("enter_category_name_and_path")}
            </div>
        {/if}
    {/if}
</div>