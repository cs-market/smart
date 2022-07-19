{style src="addons/rumba/icomoon/style.less"}
{style src="addons/rumba/styles.less"}

{capture name="styles"}
body {
    --grid-columns: {$settings.Appearance.columns_in_products_list}
}
{/capture}
{style content=$smarty.capture.styles type="less"}
