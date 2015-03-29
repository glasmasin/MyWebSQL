<?php
	$objTypes = array(
		(object) array('name' => 'table', 'class' => 'tablef'),
		(object) array('name' => 'view', 'class' => 'viewf'),
		(object) array('name' => 'procedure', 'class' => 'procf'),
		(object) array('name' => 'function', 'class' => 'funcf'),
		(object) array('name' => 'trigger', 'class' => 'trigf'),
		(object) array('name' => 'event', 'class' => 'evtf'),
		);
	//print '<ul id="tablelist" class="filetree">';
	$trunks = array();
	foreach($data['schemas'] as $schema) {

		$schema_id = 's_'.Html::id($schema);
		//print '<li id="'.$schema_id.'"><span class="schmf">'.htmlspecialchars($schema).'</span>';
		//print '<ul class="filetree">';
		$branches = array();
		foreach($objTypes as $objType) {
			$objPural = $objType->name . 's';
			if (isset($data[$objPural])) {
				$objects = isset($data[$objPural]) ? $data[$objPural][$schema] : array();
				//print '<span class="' . $objType->class . '" data-parent="'.htmlspecialchars($schema).'">'.__(ucfirst($objPural)).'</span>';
				if(count($objects) > 0) {
					//print '<span class="count">'.count($objects).'</span>';
				}
				//print '<ul>';
				$nodes = array();
				foreach($objects as $key=>$object) {
					$id = $objType->name[0] . '_' . $schema_id . '_' .Html::id($object);
					$object = htmlspecialchars($object);
					//print '<li id="'.$id.'"><a href=\'javascript:objDefault("' . $objType->name . '", "'.$id.'", "'.$schema_id.'")\'>'.$object.'</a></li>';
					$nodes[] = '{"id": "'.$id.'", "text": "'.$object.'", ' .
							'"a_attr": {"href": "javascript:objDefault(\'' . $objType->name . '\', \''.$id.'\', \''.$schema_id.'\')"}}';
				}
				//print "</ul></li>\n";
				$branches[] = '{"text": "'.__(ucfirst($objPural)).'", "children": [' . implode(',', $nodes) . ']}';

			}
		}
		$trunks[] = '{"id": "'.$schema_id.'" , "a_attr": {"class": "schmf"}, "text": "'.$schema.'", "children": [' . implode(',', $branches) . ']}';

		// print '</ul>';
		// print '</li>';
	}

	print '</div><script>var jsTreeData = [' . implode(',', $trunks) . '];</script><div>';
	//print '</ul>';
?>