<?php
class ObjectRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function findAll($search = '', $sort = 'id', $order = 'ASC', $limit = PER_PAGE, $offset = 0) {
        $allowedSorts = ['id','address','type','area','price'];
        if (!in_array($sort, $allowedSorts)) $sort = 'id';
        $sql = "SELECT * FROM objects WHERE address LIKE :s OR type LIKE :s OR description LIKE :s";
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
        $sql = "SELECT COUNT(*) FROM objects WHERE address LIKE :s OR type LIKE :s OR description LIKE :s";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['s' => "%$search%"]);
        return $stmt->fetchColumn();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM objects WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO objects (address, type, area, price, description) VALUES (:addr, :type, :area, :price, :desc)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'addr' => $data['address'],
            'type' => $data['type'],
            'area' => $data['area'] ?: null,
            'price' => $data['price'],
            'desc' => $data['description']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE objects SET address=:addr, type=:type, area=:area, price=:price, description=:desc WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'addr' => $data['address'],
            'type' => $data['type'],
            'area' => $data['area'] ?: null,
            'price' => $data['price'],
            'desc' => $data['description'],
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM objects WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function hasRelatedRecords($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE object_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn() > 0;
    }
}