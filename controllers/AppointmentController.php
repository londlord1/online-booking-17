<?php
class AppointmentController {
    private $repo;
    private $clientRepo;
    private $objectRepo;
    private $specialistRepo;

    public function __construct() {
        $this->repo = new AppointmentRepository();
        $this->clientRepo = new ClientRepository();
        $this->objectRepo = new ObjectRepository();
        $this->specialistRepo = new SpecialistRepository();
    }

    public function handle($action) {
        check_csrf();
        switch ($action) {
            case 'list':       $this->list(); break;
            case 'create':     $this->create(); break;
            case 'store':      $this->store(); break;
            case 'view':       $this->view(); break;
            case 'edit':       $this->edit(); break;
            case 'update':     $this->update(); break;
            case 'cancel':     $this->cancel(); break;
            case 'changeStatus': $this->changeStatusAjax(); break;
            case 'reschedule': $this->reschedule(); break;
            case 'reports':    $this->reports(); break;
            case 'exportReport': $this->exportReport(); break;
            default: $this->list();
        }
    }

    private function list() {
        $filters = [
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'status' => $_GET['status'] ?? '',
            'specialist_id' => $_GET['specialist_id'] ?? '',
            'client_search' => $_GET['client_search'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $total = $this->repo->countFiltered($filters);
        $appointments = $this->repo->getFiltered($filters, $perPage, $offset);
        $specialists = $this->specialistRepo->findAll();

        $pagination = paginate($total, $page, $perPage);
        $flash = getFlash();
        $entity = 'appointment';
        require __DIR__ . '/../views/appointments/list.php';
    }

    private function create() {
        $objects = $this->objectRepo->findAll();
        $clients = $this->clientRepo->findAll();
        $specialists = $this->specialistRepo->findAll();
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        $entity = 'appointment';
        require __DIR__ . '/../views/appointments/create.php';
    }

    private function store() {
        $data = $_POST;
        $errors = $this->validate($data);
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ?entity=appointment&action=create');
            exit;
        }
        try {
            $appointmentId = $this->repo->create($data);
            $code = generateBookingCode($appointmentId);
            flash("Запись успешно создана! Код бронирования: $code", 'success');
            header('Location: ?entity=appointment&action=view&id=' . $appointmentId);
        } catch (\Exception $e) {
            flash($e->getMessage(), 'error');
            header('Location: ?entity=appointment&action=create');
        }
    }

    private function view() {
        $id = $this->getId();
        $appointment = $this->repo->findById($id);
        if (!$appointment) die('Запись не найдена');
        $entity = 'appointment';
        require __DIR__ . '/../views/appointments/view.php';
    }

    private function edit() {}
    private function update() {}

    private function cancel() {
        $id = $this->getId();
        try {
            $this->repo->changeStatus($id, 'отменена');
            flash('Запись отменена', 'success');
        } catch (\Exception $e) {
            flash($e->getMessage(), 'error');
        }
        header('Location: ?entity=appointment&action=list');
    }

    private function changeStatusAjax() {
        $id = (int)($_POST['id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        if ($id <= 0) {
            http_response_code(400); echo 'Неверный ID'; exit;
        }
        try {
            $this->repo->changeStatus($id, $newStatus);
            echo 'OK';
        } catch (\Exception $e) {
            http_response_code(400); echo $e->getMessage();
        }
        exit;
    }

    private function reschedule() {
        $id = $this->getId();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newDatetime = $_POST['new_datetime'] ?? '';
            if (empty($newDatetime)) {
                flash('Дата и время обязательны', 'error');
                header("Location: ?entity=appointment&action=view&id=$id");
                exit;
            }
            try {
                $this->repo->reschedule($id, $newDatetime);
                flash('Запись перенесена', 'success');
                header("Location: ?entity=appointment&action=view&id=$id");
            } catch (\Exception $e) {
                flash($e->getMessage(), 'error');
                header("Location: ?entity=appointment&action=view&id=$id");
            }
            exit;
        }
        $appointment = $this->repo->findById($id);
        if (!$appointment) die('Запись не найдена');
        $date = $_GET['date'] ?? date('Y-m-d');
        $object = $this->objectRepo->findById($appointment['object_id']);
        $duration = $object['duration'] ?? 60;
        $slots = $this->repo->getAvailableSlots($appointment['specialist_id'], $date, $duration);
        $entity = 'appointment';
        require __DIR__ . '/../views/appointments/reschedule.php';
    }

    private function reports() {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-t');
        $dailyReport = $this->repo->reportByDay($month, $year);
        $specialistReport = $this->repo->reportBySpecialist($dateFrom, $dateTo);
        $cancelledReport = $this->repo->cancelledReport($dateFrom, $dateTo);
        $entity = 'appointment';
        require __DIR__ . '/../views/appointments/reports.php';
    }

    private function exportReport() {
        $type = $_GET['type'] ?? 'daily';
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-t');

        if ($type === 'daily') {
            $data = $this->repo->reportByDay($month, $year);
            $filename = "daily_report_{$year}_{$month}.csv";
        } elseif ($type === 'specialist') {
            $data = $this->repo->reportBySpecialist($dateFrom, $dateTo);
            $filename = "specialist_report_{$dateFrom}_{$dateTo}.csv";
        } else {
            $data = $this->repo->cancelledReport($dateFrom, $dateTo);
            $filename = "cancelled_report_{$dateFrom}_{$dateTo}.csv";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=$filename");
        $output = fopen('php://output', 'w');
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    private function getId() {
        $id = $_GET['id'] ?? 0;
        if (!ctype_digit((string)$id) || $id <= 0) die('Некорректный ID');
        return (int)$id;
    }

    private function validate($data) {
        $errors = [];
        if (empty($data['client_id'])) $errors['client_id'] = 'Выберите клиента';
        if (empty($data['object_id'])) $errors['object_id'] = 'Выберите объект';
        if (empty($data['specialist_id'])) $errors['specialist_id'] = 'Выберите специалиста';
        if (empty($data['date_time'])) {
            $errors['date_time'] = 'Дата и время обязательны';
        } else {
            $dt = $data['date_time'];
            if (strtotime($dt) < time()) $errors['date_time'] = 'Нельзя записаться на прошедшее время';
            $time = date('H:i', strtotime($dt));
            if ($time < WORK_START || $time > WORK_END) $errors['date_time'] = 'Время вне рабочих часов';
            if ($time >= BREAK_START && $time < BREAK_END) $errors['date_time'] = 'Время попадает в обеденный перерыв';
            $clientId = $data['client_id'] ?? null;
            if (!empty($clientId)) {
                $date = date('Y-m-d', strtotime($dt));
                if ($this->repo->clientHasBookingOnDate($clientId, $date)) {
                    $errors['client_id'] = 'Клиент уже записан на этот день';
                }
            }
        }
        return $errors;
    }
}