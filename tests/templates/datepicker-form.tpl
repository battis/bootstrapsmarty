{extends file="form.tpl"}

{block name="form-content"}

<div class="form-group">
	<label for id="date" class="control-label col-sm-{$formLabelWidth}">Pick a date</label>
	<div class="input-group date col-sm-3">
	  <input type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
	</div>
</div>

{/block}