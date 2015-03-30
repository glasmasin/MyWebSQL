<?php
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
                    $sql = $this->getViewQuery($cleanText);
                    break;
                case 'triggers':
                    $sql = $this->getTriggerQuery($cleanText);
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

        $extra = $this->db->includeStandardObjects ? "" : "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";

        $sql = "SELECT 'View' AS type, c.relname AS name, 'CREATE OR REPLACE VIEW ' || c.relname || ' AS\n' || pg_get_viewdef(c.oid) AS definition
                FROM pg_class c
                    INNER JOIN pg_namespace n ON c.relnamespace = n.oid
                WHERE c.relkind  = 'v' AND pg_get_viewdef(c.oid) ~* '" . $searchTerm . "' $extra
                ORDER BY c.relname";

        return $sql;
    }
    function getFunctionQuery($searchTerm) {
        $extra = $this->db->includeStandardObjects ? "" : "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";

        $sql = "SELECT 'Function' AS type, proname AS name, pg_get_functiondef(p.oid) AS definition
                FROM pg_proc p
                    INNER JOIN pg_namespace n ON p.pronamespace = n.oid
                    LEFT OUTER JOIN pg_roles u ON u.oid = p.proowner
                WHERE prosrc ~* '" . $searchTerm . "' $extra
                ORDER BY p.proname, n.nspname";

        return $sql;
    }
    function getTriggerQuery($searchTerm) {
        $extra = $this->db->includeStandardObjects ? "" : "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";

        $sql = "SELECT 'Trigger' AS type, tgname AS name, pg_get_triggerdef(t.oid, true) AS definition
                FROM pg_trigger t
                    INNER JOIN pg_class c ON t.tgrelid = c.oid
                    INNER JOIN pg_namespace n ON c.relnamespace = n.oid
                WHERE t.tgisinternal = 'f' AND pg_get_triggerdef(t.oid) ~* '" . $searchTerm . "' $extra
                ORDER BY t.tgname";

        return $sql;

    }
}
