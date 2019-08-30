{capture name="mainbox"}
    {$container_id = $smarty.request.container_id|default:"elfinder"}

    {script src="js/lib/elfinder/js/elfinder.min.js"}

    {if $smarty.const.CART_LANGUAGE != 'en'}
    {script src="js/lib/elfinder/js/i18n/elfinder.`$smarty.const.CART_LANGUAGE`.js"}
    {/if}

    <script type="text/javascript">
    (function(_, $) {

        $.loadCss(['js/lib/elfinder/css/elfinder.min.css']);
        $.loadCss(['js/lib/elfinder/css/theme.css']);

        $(document).ready(function() {

            var w = $.getWindowSizes();
            $('#{$container_id}').elfinder({
                url : fn_url('elf_connector.manage?start_path={$smarty.request.path}&security_hash=' + _.security_hash),
                rememberLastDir: true,
                useBrowserHistory: true,
                resizable: false,
                lang: _.cart_language,
                height: w.view_height - 170,
                uiOptions: {
                    toolbar : [
                        ['back', 'forward'],
                        ['mkdir', 'mkfile', 'upload'],
                        ['download'],
                        ['info'],
                        ['quicklook'],
                        ['copy', 'cut', 'paste'],
                        ['rm', 'rename'],
                        ['edit'],
                        ['extract', 'archive'],
                        ['search'],
                        ['view']
                    ]
                },
                contextmenu: {
                    files: [
                        'getfile',
                        '|',
                        'open', 'quicklook',
                        '|',
                        'download',
                        '|',
                        'copy', 'cut', 'paste', 'duplicate',
                        '|',
                        'rm',
                        '|',
                        'edit', 'rename',
                        '|',
                        'archive', 'extract',
                        '|',
                        'info'
                    ]
                },
                requestType: 'post'
            });
        });
    }(Tygh, Tygh.$))
    </script>

    <div id={$container_id}></div>

{/capture}

{if $smarty.request.in_popup}
    {$smarty.capture.mainbox nofilter}
{else}
    {include file="common/mainbox.tpl" content=$smarty.capture.mainbox title=__("file_editor") buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar sidebar_position="left"}
{/if}
