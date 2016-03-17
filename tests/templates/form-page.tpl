{extends file="subpage.tpl"}

{block name="subcontent"}

	<div class="container">
		<table class="table table-striped sortable">
			<thead>
				<tr>
					<th>Column A</th>
					<th>Column B</th>
					<th>Column C</th>
					<th data-dateformat="MM-DD-YYYY">Column D</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>C</td>
					<td>2</td>
					<td>Alice</td>
					<td>1/1/1951</td>
				</tr>
				<tr>
					<td>A</td>
					<td>3</td>
					<td>Bob</td>
					<td>6/1/1951</td>
				</tr>
				<tr>
					<td>B</td>
					<td>1</td>
					<td>Carol</td>
					<td>6/1/1949</td>
				</tr>
			</tbody>
		</table>
	</div>

	{include file="datepicker-form.tpl"}
	{include file="colorpicker-form.tpl"}
{/block}