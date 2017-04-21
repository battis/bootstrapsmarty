{block name="ui-messages"}
    {if !empty($uiMessages)}
        <div id="messages" class="container">
            {foreach $uiMessages as $message}
                <div class="alert alert-dismissable {$message->class|default:"alert-info"}">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <p><strong>{$message->title}</strong></p>
                    <div>{$message->content}</div>
                </div>
            {/foreach}
        </div>
    {/if}
{/block}
