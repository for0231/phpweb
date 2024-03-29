{include file='header.tpl' section='blogmanager'}

<script type="text/javascript" src="/js/blogPreview.js"></script>

<form method="post"
      action="{geturl controller='blogmanager' action='setstatus'}"
      id="status-form">

<div class="preview-status">
    <input type="hidden" name="id" value="{$post->getId()}" />
    {if $post->isLive()}
        <div class="status live">
            This post is live on your blog. To unpublish
            it click the <strong>Unpublish post</strong> button below.
            <div>
                <input type="submit" value="Unpublish post"
                       name="unpublish" id="status-unpublish" />
                <input type="submit" value="Edit post"
                       name="edit" id="status-edit" />
                <input type="submit" value="Delete post"
                       name="delete" id="status-delete" />
            </div>
        </div>
    {else}
        <div class="status draft">
            This post is not yet live on your blog. To publish
            it on your blog, click the button below.
            <div>
                <input type="submit" value="Publish post"
                       name="publish" id="status-publish" />
                <input type="submit" value="Edit post"
                       name="edit" id="status-edit" />
                <input type="submit" value="Delete post"
                       name="delete" id="status-delete" />
            </div>
        </div>
    {/if}
</div>

</form>

<fieldset id="preview-tags">
    <legend>Tags</legend>
    <ul>
        {foreach from=$post->getTags() item=tag}
            <li>
                <form method="post" action="{geturl action='tags'}">
                    <div>
                        {$tag|escape}
                        <input type="hidden" name="id" value="{$post->getId()}" />
                        <input type="hidden" name="tag" value="{$tag|escape}" />
                        <input type="submit" value="Delete" name="delete" />
                    </div>
                </form>
            </li>
        {foreachelse}
            <li>No tags found</li>
        {/foreach}
    </ul>

    <form method="post" action="{geturl action='tags'}">
        <div>
            <input type="hidden" name="id" value="{$post->getId()}" />
            <input type="text" name="tag" />
            <input type="submit" value="Add Tag" name="add" />
        </div>
    </form>
</fieldset>

<div class="preview-date">
    {$post->ts_created|date_format:'%x %X'}
</div>

<div class="preview-content">
    {$post->profile->content}
</div>

{include file='footer.tpl'
         leftcolumn='blogmanager/lib/left-column.tpl'}
