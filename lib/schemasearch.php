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
                    $sql = $this->getFunctionQuery($cleanText);
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

     function getViewQuery($searchTerm) {

        $extra = $this->db->includeStandardObjects ? "" : "AND table_schema NOT LIKE 'pg@_%' ESCAPE '@' AND table_schema != 'information_schema'";

        $sql = "SELECT 'View' AS type, table_name AS name, pg_get_viewdef(oid) AS definition
                FROM information_schema.tables
                WHERE table_schema = current_schema() and table_type = 'VIEW' AND pg_get_viewdef(oid) ~= '" . $searchTerm . "' $extra
                ORDER BY table_name";

        return $sql;
    }
    function getFunctionQuery($searchTerm) {
        $extra = $this->db->includeStandardObjects ? "" : "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";

        $sql = "SELECT 'Function' AS type, proname AS name, prosrc AS definition
                FROM pg_proc p
                    INNER JOIN pg_namespace n ON p.pronamespace = n.oid
                    LEFT OUTER JOIN pg_roles u ON u.oid = p.proowner
                WHERE prosrc ~* '" . $searchTerm . "' $extra
                ORDER BY p.proname, n.nspname";

        return $sql;
    }
    function getTriggerQuery($searchTerm) {
        $extra = $this->db->includeStandardObjects ? "" : "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";

        $sql = "SELECT 'Trigger' AS type, tgname AS name, pg_get_triggerdef(t.oid) AS definition
                FROM pg_trigger t
                    INNER JOIN pg_class c ON t.tgrelid = c.oid
                    INNER JOIN pg_namespace n ON c.relnamespace = n.oid
                WHERE t.tgisinternal = 'f' AND pg_get_triggerdef(t.oid) ~* '" . $searchTerm . "' $extra
                ORDER BY t.tgname";

        return $sql;

    }
}
