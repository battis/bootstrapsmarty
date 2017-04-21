{assign var="formAction" value=$formAction|default: $smarty.server.PHP_SELF}
{assign var="formMethod" value=$formMethod|default: 'post'}
{assign var="formFileUpload" value=$formFileUpload|default: false}
{assign var="formHidden" value=$formHidden|default: false}
{assign var="formLabelWidth" value=$formLabelWidth|default:2}

<div class="container">
    <form action="{$formAction}" method="{$formMethod}" {if $formFileUpload}enctype="multipart/form-data"{/if} class="form-horizontal" role="form">

        {if !empty($formHidden)}
            {foreach $formHidden as $formHiddenName => $formHiddenValue}
                <input type="hidden" type="hidden" name="{$formHiddenName}" value="{$formHiddenValue}" />
            {/foreach}
        {/if}

        {block name="form-content"}
        
            <!-- example form content -->
            <div class="form-group">
                <label for="input1" class="control-label col-sm-{$formLabelWidth}">Input 1</label>
                <div class="col-sm-{12 - $formLabelWidth}">
                    <input name="input1" id="input1" type="text" class="form-control" placeholder="Write something" autofocus="autofocus" />
                </div>
            </div>
            <div class="form-group">
                <label for="input2" class="control-label col-sm-{$formLabelWidth}">Input 2</label>
                <div class="col-sm-{12 - $formLabelWidth}">
                    <input name="input2" id="input2" type="text" class="form-control" placeholder="Write something else" />
                </div>
            </div>

        {/block}

        {block name="form-buttons"}
        
            <div class="form-group">
                <div class="col-sm-offset-{$formLabelWidth} col-sm-{12 - $formLabelWidth}">
                    <button type="submit" class="btn btn-primary has-spinner">{$formButton|default: "Submit"} <span class="spinner"><i class="fa fa-refresh fa-spin"></i></span></button>
                </div>    
            </div>
            
        {/block}

    </form>
</div>