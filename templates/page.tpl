{config_load file="BootstrapSmarty.conf"}
<!DOCTYPE html>
<html>
    <head>
        {block name="pre-bootstrap-meta"}{/block}
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        {block name="post-bootstrap-meta"}{/block}
    
        <title>{$title|default: 'Untitled'}</title>
        
        {block name="pre-bootstrap-stylesheets"}{/block}
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" />
        {foreach $uiStylesheets as $name => $stylesheet}
            <link rel="stylesheet" href="{$stylesheet}" {if !empty($name) && !is_int($name)}name="{$name}"{/if} type="text/css" />
        {/foreach}
        {block name="post-bootstrap-stylesheets"}{/block}
        
        {block name="head-scripts"}{/block}
    </head>
    <body>
        {block name="page-content"}
        <div class="page-content">
        
            {block name="header"}
                {include file="header.tpl"}
            {/block}
            
            {block name="messages"}
                {include file="messages.tpl"}
            {/block}
            
            {block name="content"}
                <div class="container">
                    {$content|default:"No content."}
                </div>
            {/block}
            
            {block name="footer"}
                {include file="footer.tpl"}
            {/block}
    
            {block name="pre-bootstrap-scripts"}{/block}
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
            <script src="{$BOOTSTRAPSMARTY_URL}/js/ie10-viewport-bug-workaround.js"></script>
            <script src="{$BOOTSTRAPSMARTY_URL}/js/button-spinner.js"></script>
            {foreach $uiScripts as $name => $script}
                <script src="{$script}"{if !empty($name)} name="{$name}"{/if}></script>
            {/foreach}
            <script>
                {foreach $uiScriptSnippets as $name => $snippet}
                    {if !empty($name)}// {$name}{/if}
                    {$snippet}
                    
                {/foreach}
            </script>
            {block name="post-bootstrap-scripts"}{/block}
        </div>
        {/block}
    </body>
</html>