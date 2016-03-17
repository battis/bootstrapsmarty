{extends file="form.tpl"}

{block name="form-content"}

<div class="form-group">
	<label for id="date" class="control-label col-sm-{$formLabelWidth}">Pick a color</label>
	<div class="input-group color col-sm-3">
	    <input type="text" value="" class="form-control" placeholder="Pick a color" />
	    <span class="input-group-addon"><i></i></span>
	</div>
</div>

{/block}