<div class="ticket">
    {if $ticket}
        <div class="ty-hd-messages">
            {include file="common/pagination.tpl"}
            {foreach from=$ticket.messages item=post}
            
                {if $addons.helpdesk.appearance == 'cloud'}
                    <div class="ty-discussion-post__content {if $post.user_id == $auth.user_id}ty-discussion-post__right{else}ty-discussion-post__left{/if}">
                        <span class="ty-discussion-post__author">{$post.user}</span>
                        <div class="ty-discussion-post" id="post_{$post.ticket_id}_{$post.message_id}">
                            <div class="ty-discussion-post__message">
                                {$post.message nofilter}
                                {if $post.files}
                                    {foreach from=$post.files item="file" name='files'}
                                    <a href="{"tickets.get_file?file_id=`$file.file_id`"|fn_url}">{$file.filename}</a>{if !$smarty.foreach.files.last}<hr/>{/if}
                                    {/foreach}
                                {/if}
                            </div>
                            <span class="ty-discussion-post__date">{$post.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</span>
                            <span class="ty-caret"><span class="ty-caret-outer"></span><span class="ty-caret-inner"></span></span>
                        </div>
                    </div>
                {else}
                <div class="panel {if $post.status == 'N'}panel-success{else}panel-info{/if}">
                    <div class="panel-heading clearfix">
                        <div class='ty-float-left'>{__("posted_by")}:&nbsp;{$post.user}</div>
                        <div class='ty-float-right'>{"j/m/Y G:i"|date:$message.timestamp}</div>
                    </div>
                    <div class="panel-body">{$post.message nofilter}
                        {if $post.files}
                        <div class="panel panel-warning">
                            <div class="panel-heading">
                                <h3 class="panel-title">{__("files")}</h3>
                            </div>
                            <div class="panel-body">
                                {foreach from=$post.files item="file"}
                                <a href="{"tickets.get_file?file_id=`$file.file_id`"|fn_url}">{$file.filename}</a><hr/>
                                {/foreach}
                            </div>
                        </div>
                        {/if}
                    </div>
                </div>
                {/if}
            {/foreach}
            {include file="common/pagination.tpl"}
        </div>
    {/if}
    <div id="scroll_anchor"></div>
    <form action="{""|fn_url}" method="POST" enctype="multipart/form-data" name="create_new_message" class='form-horizontal form-edit cm-disable-empty-files cm-check-changes collapse clearfix' id="create_new_message">
        <input type="hidden" name="ticket_data[ticket_id]" value="{$ticket.ticket_id}" />
        <input type ="hidden" name="redirect_url" value="{$config.current_url}" />

        {include file="addons/helpdesk/views/tickets/components/new_message.tpl"}
        {include file="buttons/button.tpl" but_text=__("post_message") but_name="dispatch[tickets.add_message]" but_meta="ty-btn ty-btn__primary ty-float-right"}
    </form>
    <script>
        offset = $(window).height() - 220;
        $.scrollToElm("scroll_anchor", undefined, { 'offset': offset });
    </script>
</div>
{capture name="title"}{$ticket.subject}{/capture}
