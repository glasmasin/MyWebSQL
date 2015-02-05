4<?php
/**
 * This file is a part of MyWebSQL package
 *
 * @file:      lib/schema_search.php
 * @author     Tom Horwood
 * @copyright  (c) 2015-2015 Tom Horwood
 * @web        http://mywebsql.net
 * @license    http://mywebsql.net/license
 */

class schemaSearch {
    var $db;

    var $text;
    var $objectTypes;

    var $results;
    var $_queries; // list of queries that fetched results with 1 or more matches

    function __construct(&$db) {
        $this->db = $db;
    }

    function setText($keyword) {
        $this->text = $keyword;
    }

    function setObjectTypes($objectTypes) {
        $this->objectTypes = $objectTypes;
    }


    function search() {

        $this->_queries = array();
        $this->results = array('rows' => array());

        $cleanText = $this->db->escape($this->text);

        foreach($this->objectTypes as $objectType) {
            switch ($objectType) {
                case 'functions':
                    $query = $this->getFunctionQuery($cleanText);
                    break;
                case 'views':
                    $query = $this->getViewQuery($cleanText);
                    break;
                case 'triggers':
                    $query = $this->getTriggerQuery($cleanText);
                    break;
                case 'sequences':
                    $query = $this->getSequenceQuery($cleanText);
                    break;

                default:
                    # code...
                    break;
            }

            $sql = "SELECT $query->fields FROM $query->tables WHERE $query->conditions ORDER BY $query->ordering";
            $this->_queries[] = $sql;
            if (!$this->db->query($sql)) {
                return false;
            }

            $total = $this->db->numRows();
            for ($i=0; $i < min(10, $total); $i++) {
                $this->results['rows'][] = $this->db->fetchRow();
            }
        }
        return true;
    }

    function getResults() {
        return $this->results;
    }

    function getQueries() {
        return $this->_queries;
    }

     function getViewQuery($cleanText) {

        $extra = $this->db->includeStandardObjects ? "" : "AND table_schema NOT LIKE 'pg@_%' ESCAPE '@' AND table_schema != 'information_schema'";

        $query = new StdClass();

        $query->fields = "'View' AS type, table_name AS name, pg_get_viewdef(oid) AS definition";
        $query->tables = "information_schema.tables";
        $query->conditions = "table_schema = current_schema() and table_type = 'VIEW' $extra";
        $query->ordering = "table_name";

        return $query;
    }
    function getFunctionQuery($cleanText) {
        $extra = $this->db->includeStandardObjects ? "" : "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";

        $query = new StdClass();

        $query->fields = "'Function' AS type, proname AS name, prosrc AS definition";
        $query->tables = "pg_proc p INNER JOIN pg_namespace n ON p.pronamespace = n.oid LEFT OUTER JOIN pg_roles u ON u.oid = p.proowner";
        $query->conditions = "prosrc ~* '" . $cleanText . "' $extra";
        $query->ordering = "p.proname, n.nspname";

        return $query;
    }
    function getTriggerQuery($cleanText) {
        $extra = $this->db->includeStandardObjects ? "" : "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";

        $query = new StdClass();

        $query->fields = "'Trigger' AS type, tgname AS name, pg_get_triggerdef(t.oid) AS definition";
        $query->tables = "pg_trigger t INNER JOIN pg_class c ON t.tgrelid = c.oid INNER JOIN pg_namespace n ON c.relnamespace = n.oid";
        $query->conditions = "t.tgisinternal = 'f' AND pg_get_triggerdef(t.oid) ~* '" . $cleanText . "' $extra";
        $query->ordering = "t.tgname";

        return $query;

    }
    function getSequenceQuery($cleanText) {
        $extra = $this->db->includeStandardObjects ? "" : "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";

        $query = new StdClass();

        $query->fields = "n.nspname, c.relname AS name, ds.description, n.nspname, d.refobjid as owntab, u.rolname AS usename";
        $query->tables = "pg_class c LEFT OUTER JOIN pg_roles u ON u.oid = c.relowner INNER JOIN pg_namespace n ON c.relnamespace = n.oid LEFT OUTER JOIN pg_depend d on c.relkind = 'S' and d.classid = c.tableoid and d.objid = c.oid and d.objsubid = 0 and d.refclassid = c.tableoid and d.deptype = 'i' LEFT OUTER JOIN pg_description ds ON c.oid = ds.objoid";
        $query->conditions = "c.relkind = 'S' $extra";
        $query->ordering = "c.relname";

        return $query;
    }
}
?>