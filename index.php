<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/config.php';

$allowed = ['client', 'object', 'specialist' , 'appointment'];
$entity = $_GET['entity'] ?? 'client';
if (!in_array($entity, $allowed)) {
    die('Invalid entity');
}

$controllerClass = ucfirst($entity) . 'Controller';
$controller = new $controllerClass();

$action = $_GET['action'] ?? 'list';
$allowedActions = ['list', 'create', 'store', 'edit', 'update', 'delete', 'destroy' , 'view', 'cancel', 'changeStatus', 'reschedule', 'reports', 'exportReport'];
if (!in_array($action, $allowedActions)) {
    $action = 'list';
}

$controller->handle($action);