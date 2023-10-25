<?php
$dir = dirname(__FILE__);
chdir($dir . '/../');
require_once 'env.php.test';
require_once 'common.inc.php';

foreach (glob("tests/*Entity.php") as $filename) {
    require_once $filename;
}

$graph = [];
$allTables = [];
$tableToClass = [];

// Build nodes
foreach (get_declared_classes() as $class) {
    if (is_subclass_of($class, 'SimpleMVC\BaseEntity')) {
        $tableName = $class::getTableName();
        $graph[$tableName] = [];
        $allTables[] = $tableName;
        $tableToClass[$tableName] = $class;
    }
}

// Build edges
foreach ($allTables as $table) {
    $cls = $tableToClass[$table];
    foreach ($cls::getFKs() as $fk) {
        $graph[$table][] = $fk->refTable;
    }
}

function topologicalSort($graph)
{
    $result = [];
    $visited = [];

    foreach ($graph as $node => $edges) {
        visit($node, $graph, $result, $visited);
    }

    return $result;
}

function visit($node, $graph, &$result, &$visited)
{
    if (!isset($visited[$node])) {
        $visited[$node] = true;
        foreach ($graph[$node] as $edge) {
            visit($edge, $graph, $result, $visited);
        }
        $result[] = $node;
    }
}

$order = topologicalSort($graph);
foreach ($order as $table) {
    $class = $tableToClass[$table];
    $class::createOrUpdateTable();
}
