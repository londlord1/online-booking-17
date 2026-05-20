<?php
session_start();
require_once __DIR__ . '/../src/Models/Database.php';
require_once __DIR__ . '/../src/Models/ClientRepository.php';
require_once __DIR__ . '/../src/Models/RealtorRepository.php';
require_once __DIR__ . '/../src/Models/PropertyRepository.php';
require_once __DIR__ . '/../src/Controllers/BaseController.php';
require_once __DIR__ . '/../src/Controllers/ClientController.php';
require_once __DIR__ . '/../src/Controllers/RealtorController.php';
require_once __DIR__ . '/../src/Controllers/PropertyController.php';

$entity = $_GET['entity'] ?? 'client';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$db = new Database();
$pdo = $db->getConnection();

switch ($entity) {
    case 'client':
        $controller = new ClientController($pdo);
        break;
    case 'realtor':
        $controller = new RealtorController($pdo);
        break;
    case 'property':
        $controller = new PropertyController($pdo);
        break;
    default:
        die("Неверный параметр entity");
}

$method = $action;
if (!method_exists($controller, $method)) {
    die("Метод не найден");
}
$controller->$method($id);
