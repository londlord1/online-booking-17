<?php
class ObjectController {
    private $repo;

    public function __construct() {
        $this->repo = new ObjectRepository();
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
        $sort   = in_array($_GET['sort'] ?? '', ['id','address','type','area','price']) ? $_GET['sort'] : 'id';
        $order  = ($_GET['order'] ?? '') === 'desc' ? 'DESC' : 'ASC';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * PER_PAGE;

        $total = $this->repo->countAll($search);
        $objects = $this->repo->findAll($search, $sort, $order, PER_PAGE, $offset);

        $pagination = paginate($total, $page);
        $flash = getFlash();
        $entity = 'object';
        require __DIR__ . '/../views/objects/list.php';
    }

    private function create() {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        $entity = 'object';
        require __DIR__ . '/../views/objects/form.php';
    }

    private function store() {
        $data = $_POST;
        $errors = $this->validate($data);
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ?entity=object&action=create');
            exit;
        }
        $this->repo->create($data);
        flash('Объект добавлен', 'success');
        header('Location: ?entity=object&action=list');
    }

    private function edit() {
        $id = $this->getId();
        $object = $this->repo->findById($id);
        if (!$object) die('Объект не найден');
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        $_SESSION['old'] = $object;
        $entity = 'object';
        require __DIR__ . '/../views/objects/form.php';
    }

    private function update() {
        $id = $this->getId();
        $data = $_POST;
        $errors = $this->validate($data, $id);
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data + ['id' => $id];
            header("Location: ?entity=object&action=edit&id=$id");
            exit;
        }
        $this->repo->update($id, $data);
        flash('Объект обновлён', 'success');
        header('Location: ?entity=object&action=list');
    }

    private function confirmDelete() {
        $id = $this->getId();
        $object = $this->repo->findById($id);
        if (!$object) die('Объект не найден');
        $entity = 'object';
        require __DIR__ . '/../views/objects/delete.php';
    }

    private function destroy() {
        $id = $this->getId();
        if ($this->repo->hasRelatedRecords($id)) {
            flash('Нельзя удалить объект: есть связанные записи (показы, встречи). Сначала удалите или переназначьте их.', 'error');
        } else {
            $this->repo->delete($id);
            flash('Объект удалён', 'success');
        }
        header('Location: ?entity=object&action=list');
    }

    private function getId() {
        $id = $_GET['id'] ?? 0;
        if (!ctype_digit((string)$id) || $id <= 0) die('Некорректный ID');
        return (int)$id;
    }

    private function validate($data, $id = null) {
        $errors = [];
        if (trim($data['address'] ?? '') === '') $errors['address'] = 'Адрес обязателен';
        if (trim($data['type'] ?? '') === '') $errors['type'] = 'Тип объекта обязателен';
        if (!is_numeric($data['price'] ?? '') || $data['price'] <= 0) $errors['price'] = 'Цена должна быть положительным числом';
        if ($data['area'] !== '' && !is_numeric($data['area'])) $errors['area'] = 'Площадь должна быть числом';
        return $errors;
    }
}