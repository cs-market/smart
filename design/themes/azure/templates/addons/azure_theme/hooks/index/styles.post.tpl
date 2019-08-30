{style src="addons/azure_theme/styles.less"}

{capture name="styles"}
.azure-main-menu .ty-dropdown-box__content {
	.ty-menu__submenu-items {
		min-width: {$addons.azure_theme.menu_width}px;
		& ,.ty-menu__items {
			min-height: {$addons.azure_theme.menu_height}px;
		}
	}
}
{/capture}
{style content=$smarty.capture.styles type="less"}