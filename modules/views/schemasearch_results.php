<link href='cache.php?css=theme,default,grid,alerts,editor' rel="stylesheet" />

<div id="popup_wrapper">
	<div id="popup_contents">
		<div class="ui-state-highlight padded">{{MESSAGE}}<br/>(click on row for full definition)</div>
		<table width="95%" border="0" cellspacing="1" cellpadding="2" id="table_grid"><tbody>
			<tr id='fhead'>
				<th style="width:20%"><?php echo __('Type'); ?></th>
				<th style="width:20%"><?php echo __('Object name'); ?></th>
				<th style="width:60%"><?php echo __('Definition'); ?></th>
			</tr>
			<?php foreach($data['results']['rows'] as $row) { ?>
				<tr>
					<td><?php echo $row['type']; ?></td>
					<td><?php echo $row['name']; ?></td>
					<td><pre><?php echo htmlspecialchars($row['definition']); ?></pre></td>
				</tr>
			<?php } ?>
		</tbody></table>

		<section id="queries">
				<h1><?php echo __('Queries'); ?></h1>
			<?php foreach($data['queries'] as $query) { ?>
				<p><?php echo $query; ?></p>
			<?php } ?>

		</section>
		<div class="ui-dialog ui-widget ui-widget-content ui-corner-all" style='position:absolute; top:20px; left:20px; width:95%; height:90%; display:none; flex-direction:column; background-color: white' id="fullDefinition">
			<div class='close ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix' style='flex: 0 0 20px; padding:3px; text-align:right'>
				<span class="ui-dialog-title" id="ui-dialog-title-function-definition"></span>
				<a href="#" class="ui-dialog-titlebar-close ui-corner-all" role="button"><span class="ui-icon ui-icon-closethick">close</span></a>
			</div>
			<textarea style='flex: 1 1 auto' id="fullDefinition"></textarea>
		</div>
	</div>
</div>

<script type="text/javascript" language='javascript' src="cache.php?script=common,jquery,ui,position,query"></script>
<script type="text/javascript" language="javascript">
window.title = "<?php echo __('Schema Search Results'); ?>";
$(function() {
	var m;
	var search = "<?php echo htmlspecialchars($data['filter']);  ?>";
	$('#table_grid tr td:nth-child(3) pre').each(function() {
		output = '';
		re = new RegExp("(.{0,20}" + search + ".{0,20})", "gi");
		m = $(this).html().match(re);
	 	for (var i = 0; i < m.length; i++) {
	 		if (i > 4) {
	 			output += "...</br>(" + (m.length - 5) + " more) ...";
	 			break;
	 		}
	 		output += "..." + m[i].replace(search, "<strong style='font-weight:700'>" + search + "</strong>") + "...</br>";
	 	}
	 	$(this).data('definition', $(this).text());
	 	$(this).html(output);
	});

	$('#table_grid tr').click(function() {
	 	definition = $(this).children().last().find('pre').data().definition;
	 	name = $(this).find('td:nth-child(2)').html();
		$('#fullDefinition textarea').text("--- " + name + "\n" + definition);
		$("#ui-dialog-title-function-definition").html(name);
		$('#fullDefinition').css('display', 'flex');
	});
	$("#fullDefinition .close").click(function() {
		$(this).parent().hide();
	});
	$('#table_grid a').click(function() {
		sql = $(this).siblings('div').eq(0).text();
		parent.setSqlCode(parent.sql_delimiter + sql, 1);
	});
});
</script>