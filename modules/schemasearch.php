<?php
/**
 * This file is a part of MyWebSQL package
 *
 * @file:      modules/search.php
 * @author     Samnan ur Rehman
 * @copyright  (c) 2008-2014 Samnan ur Rehman
 * @web        http://mywebsql.net
 * @license    http://mywebsql.net/license
 */

function processRequest(&$db) {
    if (isset($_POST['keyword']) && is_array(v($_POST['object_type'])) ) {
        searchDatabase($db);
    } else {
        echo view('schemasearch', $replace);
    }
}

function searchDatabase(&$db) {
    $operator = v($_POST['object_type']);

    include(BASE_PATH . "/lib/schemasearch.php");
    $searchTool = new schemaSearch($db);
    $searchTool->setObjectTypes($_POST['object_type']);
    $searchTool->setText(v($_POST['keyword']));

    $data = array('filter' => v($_POST['keyword']), 'results' => array(), 'queries' => array());
    if ($searchTool->search()) {
        $data['results'] = $searchTool->getResults();
        $data['queries'] = $searchTool->getQueries();
    }

    $message = str_replace('{{KEYWORD}}', "&quot;" . htmlspecialchars($_POST['keyword']) .
        "&quot;", __('Search results for {{KEYWORD}} in the database schema'));
    $replace = array('MESSAGE' => $message);
    echo view('schema_search_results', $replace, $data);
}
