<?php
 /**
 *
 */
 class NodeGrouping
 {
    public $rule = null;
    private $subGroups = array();

    /**
     * Initialise the grouping with a rule to group by
     * @param [string] $rule Regex to group by
     */
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

/**
*
*/
class ObjTree
{

    private $objectTypes = array(
        array('name' => 'table', 'class' => 'table', 'groupby' => '/^tbl(.+?)_/'),
        array('name' => 'view', 'class' => 'view', 'groupby' => '/^v_(.+?)(_|$)/'),
        array('name' => 'procedure', 'class' => 'proc', 'groupby' => '/^p_(.+?)_/'),
        array('name' => 'function', 'class' => 'func', 'groupby' => '/^(f_.+?|_sys|[a-z]{3,})[^a-z]/'),
        array('name' => 'trigger', 'class' => 'trig', 'groupby' => '/(.+?)_/'),
        array('name' => 'event', 'class' => 'evt', 'groupby' => false),
        );
    private $groupby_rule = "/^.{6}/";
    private $tree = array();
    private $data = array();
    public $messages = array();

    public function __construct($data, $options = null)
    {
        $this->data = $data;
        if ($options and isset($options['objectTypes'])) {
            $this->objectTypes = $options['objectTypes'];
        }
        $this->objectTypes = array_filter($this->objectTypes, array($this, 'typeInUse'));
        $this->tree = array_map(array($this,'processSchema'), $data['schemas']);
    }

    public function getData() {
        return json_encode($this->tree);
    }

    protected function processSchema($schema) {
        $schema_id = 's_'.Html::id($schema);
        $branches = array();
        foreach ($this->objectTypes as $type) {
            $branches[] = $this->processObjectType($type, $schema);
        }

        return array(
            'id' => $schema_id,
            'a_attr' => array('class' => 'schmf'),
            'state' => array('opened' => 1),
            'text' => $schema,
            'children' => $branches,
        );
    }
    protected function typeInUse($objectType) {
        return isset($this->data["{$objectType['name']}s"]);
    }
    protected function processObjectType($objectType, $schema) {

        $schema_id = 's_'.Html::id($schema);
        $objPural = $objectType['name'] . 's';
        $objects = isset($this->data[$objPural][$schema]) ? $this->data[$objPural][$schema] : array();
        $nodes = array();
        $last_node_id = '';
        $node_increment = 0;
        $this->messages[] = count($this->data[$objPural][$schema]);

        foreach($objects as $object) {

            $id = $objectType['name'][0] . '_' . $schema_id . '_' .Html::id(preg_replace('/ \(.+/', "", $object));
            $id = $this->differentiateFunctionIds($objectType['name'], $id, $last_node_id, $node_increment);
            $object = htmlspecialchars($object);
            $nodes[] = array(
                "id" => $id,
                "text" => $object,
                'icon' => "jstree-file",
                "a_attr" => array(
                    'class' => 'o' . $objectType['class'],
                    'data-parent' => $schema,
                    "href" => 'javascript:objDefault(\'' . $objectType['name'] . '\', \''.$id.'\', \''.$schema_id.'\')')
            );

        }
        $nodeCount =count($nodes);
        $groupby = $objectType['groupby'] ? $objectType['groupby'] : $this->groupby_rule;
        $grouping = new NodeGrouping($groupby);
        $nodes = $grouping->getGroupOutput($nodes);
        return array(
            'text' => __(ucfirst($objPural)) . ' [' . $nodeCount . ']',
            'a_attr' => array('class' => $objectType['class'] . "f"),
            'children' => $nodes,
        );
    }


    function differentiateFunctionIds($objectType, $id, &$last_node_id, &$node_increment)
    {
        if ($objectType !== 'function') {
            return $id;
        }
        if ($id === $last_node_id) {
            $node_increment += 1;
        } else {
            $node_increment = 0;
        }
        $last_node_id = $id;
        return $id . $node_increment;

    }
}

$objTree = new ObjTree($data);

print $objTree->getData();

