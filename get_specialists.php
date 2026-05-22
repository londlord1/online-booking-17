<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$specialistRepo = new SpecialistRepository();
$specialists = $specialistRepo->findAll();

// Оставляем только нужные поля
$result = [];
foreach ($specialists as $s) {
    $result[] = [
        'id' => $s['id'],
        'first_name' => $s['first_name'],
        'last_name' => $s['last_name'],
    ];
}

echo json_encode($result);