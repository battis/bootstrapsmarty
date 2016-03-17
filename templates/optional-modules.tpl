{if strpos($BOOTSTRAPSMARTY_URL, '/vendor/') !== false}
	{assign var="assetPath" value="/../.."}
{else}
	{assign var="assetPath" value="/vendor"}
{/if}

{if !empty($uiStylesheets[$MODULE_DATEPICKER])}
	<script src="{$BOOTSTRAPSMARTY_URL}{$assetPath}/bower-asset/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	<script>
		$('.input-group.date').datepicker({
			orientation: 'top auto',
		    autoclose: true,
		    todayHighlight: true
		});
	</script>
{/if}

{if !empty($uiStylesheets[$MODULE_COLORPICKER])}
	<script src="{$BOOTSTRAPSMARTY_URL}{$assetPath}/bower-asset/xaguilars-bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
	<script>
		$('.input-group.color').colorpicker();
	</script>
{/if}