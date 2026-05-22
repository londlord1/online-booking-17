<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$specialistId = isset($_GET['specialist_id']) ? (int)$_GET['specialist_id'] : 0;
$date = $_GET['date'] ?? '';
$objectId = isset($_GET['object_id']) ? (int)$_GET['object_id'] : 0;

if ($specialistId <= 0 || !$date || $objectId <= 0) {
    echo json_encode(['error' => 'Неверные параметры']);
    exit;
}

// Получаем длительность из объекта (услуги)
$objectRepo = new ObjectRepository();
$object = $objectRepo->findById($objectId);
if (!$object) {
    echo json_encode(['error' => 'Объект не найден']);
    exit;
}
$duration = isset($object['duration']) ? (int)$object['duration'] : 60;

$appointmentRepo = new AppointmentRepository();
$slots = $appointmentRepo->getAvailableSlots($specialistId, $date, $duration);

echo json_encode(['slots' => $slots]);