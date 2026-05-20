<?php
class ClientRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function findAll($search = '', $sort = 'id', $order = 'ASC', $limit = PER_PAGE, $offset = 0) {
        $allowedSorts = ['id','first_name','last_name','phone','email','birth_date'];
        if (!in_array($sort, $allowedSorts)) $sort = 'id';
        $sql = "SELECT * FROM clients WHERE first_name LIKE :s OR last_name LIKE :s OR phone LIKE :s";
        $sql .= " ORDER BY `$sort` $order LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $s = "%$search%";
        $stmt->bindValue(':s', $s);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($search = '') {
        $sql = "SELECT COUNT(*) FROM clients WHERE first_name LIKE :s OR last_name LIKE :s OR phone LIKE :s";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['s' => "%$search%"]);
        return $stmt->fetchColumn();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO clients (first_name, last_name, phone, email, birth_date) VALUES (:fn, :ln, :ph, :em, :bd)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'fn' => $data['first_name'],
            'ln' => $data['last_name'],
            'ph' => $data['phone'],
            'em' => $data['email'],
            'bd' => $data['birth_date']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE clients SET first_name=:fn, last_name=:ln, phone=:ph, email=:em, birth_date=:bd WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'fn' => $data['first_name'],
            'ln' => $data['last_name'],
            'ph' => $data['phone'],
            'em' => $data['email'],
            'bd' => $data['birth_date'],
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM clients WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function hasRelatedRecords($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE client_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn() > 0;
    }
}