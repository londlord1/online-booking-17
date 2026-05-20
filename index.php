<?php
require_once __DIR__ . '/config.php';

$allowed = ['client', 'object', 'specialist'];
$entity = $_GET['entity'] ?? 'client';
if (!in_array($entity, $allowed)) {
    die('Invalid entity');
}

$controllerClass = ucfirst($entity) . 'Controller';
$controller = new $controllerClass();

$action = $_GET['action'] ?? 'list';
$allowedActions = ['list', 'create', 'store', 'edit', 'update', 'delete', 'destroy'];
if (!in_array($action, $allowedActions)) {
    $action = 'list';
}

$controller->handle($action);