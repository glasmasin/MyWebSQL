<?php
 /**
 *
 */
 class NodeGrouping
 {
 	public $rule = null;
 	private $subGroups = array();

 	function __construct($rule)
 	{
 		$this->rule = $rule;
 	}
 	function getGroupOutput($nodes) {
 		$twigs = array();
 		foreach ($nodes as $node) {
 			$group = $this->getSubGroup($node);
 			$this->putInGroup($group, $node);
 		}
 		foreach ($this->subGroups as $subGroup => $children) {
 			if (count($children) === 1 or $subGroup === "noGroup") {
 				foreach ($children as $child) {
 					$twigs[] = $child;
 				}
 			} else {
 				$twigs[] = array("text" => $subGroup . ' [' . count($children) . ']', "children" => $children);
 			}
 		}
 		return $twigs;
 	}

 	function putInGroup($groupName, $node) {
 		if(!array_key_exists($groupName, $this->subGroups)) {
 			$this->subGroups[$groupName] = array();
 		}
 		$this->subGroups[$groupName][] = $node;
 	}

 	function getSubGroup($node) {
 		$matches = null;
 		if (preg_match($this->rule, $node['text'], $matches)) {
 			return $matches[1];
 		}
 		return "noGroup";
 	}
 }

$objTypes = array(
	(object) array('name' => 'table', 'class' => 'tablef', 'groupby' => '/^tbl(.+?)_/'),
	(object) array('name' => 'view', 'class' => 'viewf', 'groupby' => '/^v_(.+?)(_|$)/'),
	(object) array('name' => 'procedure', 'class' => 'procf', 'groupby' => '/^p_(.+?)_/'),
	(object) array('name' => 'function', 'class' => 'funcf', 'groupby' => '/^(f_.+?|_sys|[a-z]{3,})[^a-z]/'),
	(object) array('name' => 'trigger', 'class' => 'trigf', 'groupby' => '/(.+?)_/'),
	(object) array('name' => 'event', 'class' => 'evtf', 'groupby' => false),
	);
$groupby_rule = "/^.{6}/";

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
			$nodes = array();
			$last_node_id = '';
			$node_increment = 0;
			foreach($objects as $key=>$object) {
				$id = $objType->name[0] . '_' . $schema_id . '_' .Html::id(preg_replace('/ \(.+/', "", $object));
				if ($id === $last_node_id) {
					$node_increment += 1;
				} else {
					$node_increment = 0;
				}
				$last_node_id = $id;
				$id .= $node_increment;
				$object = htmlspecialchars($object);
				$nodes[] = array(
					"id" => $id,
					"text" => $object,
					'icon' => "jstree-file",
					"a_attr" => array("href" => 'javascript:objDefault(\'' . $objType->name . '\', \''.$id.'\', \''.$schema_id.'\')')
				);

			}
			$nodeCount =count($nodes);
			$groupby = $objType->groupby ? $objType->groupby : $groupby_rule;
			$grouping = new NodeGrouping($groupby);
			$nodes = $grouping->getGroupOutput($nodes);
			//print "</ul></li>\n";
			$branches[] =  array(
				'text' => __(ucfirst($objPural)) . ' [' . $nodeCount . ']',
				'children' => $nodes,
			);

		}
	}
	$trunks[] =  array(
		'id' => $schema_id,
		'a_attr' => array('class' => 'schmf'),
		'state' => array('opened' => 1),
		'text' => $schema,
		'children' => $branches,
	);

	// print '</ul>';
	// print '</li>';
}
print '</div><script>var jsTreeData = ' . json_encode($trunks) . ';</script><div>';
//print '</ul>';
?>