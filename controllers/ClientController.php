<?php
class ClientController {
    private $repo;

    public function __construct() {
        $this->repo = new ClientRepository();
    }

    public function handle($action) {
        check_csrf();
        switch ($action) {
            case 'list':    $this->list(); break;
            case 'create':  $this->create(); break;
            case 'store':   $this->store(); break;
            case 'edit':    $this->edit(); break;
            case 'update':  $this->update(); break;
            case 'delete':  $this->confirmDelete(); break;
            case 'destroy': $this->destroy(); break;
        }
    }

    private function list() {
        $search = $_GET['search'] ?? '';
        $sort   = in_array($_GET['sort'] ?? '', ['id','first_name','last_name','phone','email','birth_date']) ? $_GET['sort'] : 'id';
        $order  = ($_GET['order'] ?? '') === 'desc' ? 'DESC' : 'ASC';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * PER_PAGE;

        $total = $this->repo->countAll($search);
        $clients = $this->repo->findAll($search, $sort, $order, PER_PAGE, $offset);

        $pagination = paginate($total, $page);
        $flash = getFlash();
        $entity = 'client';
        require __DIR__ . '/../views/clients/list.php';
    }

    private function create() {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        $entity = 'client';
        require __DIR__ . '/../views/clients/form.php';
    }

    private function store() {
        $data = $_POST;
        $errors = $this->validate($data);
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ?entity=client&action=create');
            exit;
        }
        $this->repo->create($data);
        flash('Клиент успешно добавлен', 'success');
        header('Location: ?entity=client&action=list');
    }

    private function edit() {
        $id = $this->getId();
        $client = $this->repo->findById($id);
        if (!$client) die('Клиент не найден');
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        $_SESSION['old'] = $client;
        $entity = 'client';
        require __DIR__ . '/../views/clients/form.php';
    }

    private function update() {
        $id = $this->getId();
        $data = $_POST;
        $errors = $this->validate($data, $id);
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data + ['id' => $id];
            header("Location: ?entity=client&action=edit&id=$id");
            exit;
        }
        $this->repo->update($id, $data);
        flash('Данные клиента обновлены', 'success');
        header('Location: ?entity=client&action=list');
    }

    private function confirmDelete() {
        $id = $this->getId();
        $client = $this->repo->findById($id);
        if (!$client) die('Клиент не найден');
        $entity = 'client';
        require __DIR__ . '/../views/clients/delete.php';
    }

    private function destroy() {
        $id = $this->getId();
        if ($this->repo->hasRelatedRecords($id)) {
            flash('Нельзя удалить клиента: есть связанные записи (например, записи на приём). Сначала удалите или переназначьте их.', 'error');
        } else {
            $this->repo->delete($id);
            flash('Клиент удалён', 'success');
        }
        header('Location: ?entity=client&action=list');
    }

    private function getId() {
        $id = $_GET['id'] ?? 0;
        if (!ctype_digit((string)$id) || $id <= 0) die('Некорректный ID');
        return (int)$id;
    }

    private function validate($data, $id = null) {
        $errors = [];
        if (trim($data['first_name'] ?? '') === '') $errors['first_name'] = 'Имя обязательно';
        if (trim($data['last_name'] ?? '') === '') $errors['last_name'] = 'Фамилия обязательна';
        if (!preg_match('/^\+?\d[\d\-\(\) ]{6,}$/', $data['phone'] ?? '')) $errors['phone'] = 'Некорректный телефон';
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Некорректный email';
        $bd = $data['birth_date'] ?? '';
        if ($bd) {
            $date = DateTime::createFromFormat('Y-m-d', $bd);
            if (!$date || $date->format('Y-m-d') !== $bd) {
                $errors['birth_date'] = 'Неверный формат даты';
            } else {
                if ($date > new DateTime()) $errors['birth_date'] = 'Дата не может быть в будущем';
                $age = $date->diff(new DateTime())->y;
                if ($age < 18) $errors['birth_date'] = 'Клиент должен быть старше 18 лет';
            }
        }
        return $errors;
    }
}