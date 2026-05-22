<?php
class AppointmentRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function getBusySlots($specialistId, $date) {
        $stmt = $this->pdo->prepare(
            "SELECT TIME(date_time) as slot_start
             FROM appointments
             WHERE specialist_id = :sid
               AND DATE(date_time) = :date
               AND status NOT IN ('отменена', 'завершена')
             ORDER BY slot_start"
        );
        $stmt->execute(['sid' => $specialistId, 'date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function getAvailableSlots($specialistId, $date, $durationMinutes) {
        $workStart = strtotime(WORK_START);
        $workEnd = strtotime(WORK_END);
        $breakStart = strtotime(BREAK_START);
        $breakEnd = strtotime(BREAK_END);
        $interval = SLOT_INTERVAL * 60;

        $busy = $this->getBusySlots($specialistId, $date);
        $busyStarts = [];
        foreach ($busy as $time) {
            $busyStarts[] = strtotime($time);
        }

        $slots = [];
        for ($t = $workStart; $t + $durationMinutes * 60 <= $workEnd; $t += $interval) {
            if ($t >= $breakStart && $t < $breakEnd) continue;
            if ($t + $durationMinutes * 60 > $breakStart && $t < $breakEnd) continue;

            $slotEnd = $t + $durationMinutes * 60;
            $isFree = true;
            foreach ($busyStarts as $busyStart) {
                $busyEnd = $busyStart + 60;
                if ($t < $busyEnd && $slotEnd > $busyStart) {
                    $isFree = false;
                    break;
                }
            }
            if ($isFree) {
                $slots[] = date('H:i', $t);
            }
        }
        return $slots;
    }

    public function create($data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM appointments
                 WHERE specialist_id = :sid
                   AND date_time = :dt
                   AND status NOT IN ('отменена', 'завершена')"
            );
            $stmt->execute(['sid' => $data['specialist_id'], 'dt' => $data['date_time']]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception('К сожалению, выбранное время только что занято.');
            }
            $sql = "INSERT INTO appointments (client_id, object_id, specialist_id, date_time, status)
                    VALUES (:cid, :oid, :sid, :dt, 'ожидает')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'cid' => $data['client_id'],
                'oid' => $data['object_id'],
                'sid' => $data['specialist_id'],
                'dt' => $data['date_time']
            ]);
            $appointmentId = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $appointmentId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, c.first_name AS client_fn, c.last_name AS client_ln, c.phone AS client_phone,
                    o.address AS object_address, o.type AS object_type, o.price AS object_price,
                    s.first_name AS spec_fn, s.last_name AS spec_ln
             FROM appointments a
             JOIN clients c ON a.client_id = c.id
             JOIN objects o ON a.object_id = o.id
             JOIN specialists s ON a.specialist_id = s.id
             WHERE a.id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFiltered($filters, $limit = 20, $offset = 0) {
        $where = [];
        $params = [];
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(a.date_time) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(a.date_time) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['status'])) {
            $where[] = "a.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['specialist_id'])) {
            $where[] = "a.specialist_id = :sid";
            $params['sid'] = $filters['specialist_id'];
        }
        if (!empty($filters['client_search'])) {
            $where[] = "(c.last_name LIKE :cs OR c.phone LIKE :cs2)";
            $params['cs'] = '%' . $filters['client_search'] . '%';
            $params['cs2'] = '%' . $filters['client_search'] . '%';
        }
        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT a.*, c.first_name AS client_fn, c.last_name AS client_ln, c.phone AS client_phone,
                       o.address AS object_address, o.type AS object_type,
                       s.first_name AS spec_fn, s.last_name AS spec_ln
                FROM appointments a
                JOIN clients c ON a.client_id = c.id
                JOIN objects o ON a.object_id = o.id
                JOIN specialists s ON a.specialist_id = s.id
                $whereSQL
                ORDER BY a.date_time DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFiltered($filters) {
        $where = [];
        $params = [];
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(a.date_time) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(a.date_time) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['status'])) {
            $where[] = "a.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['specialist_id'])) {
            $where[] = "a.specialist_id = :sid";
            $params['sid'] = $filters['specialist_id'];
        }
        if (!empty($filters['client_search'])) {
            $where[] = "(c.last_name LIKE :cs OR c.phone LIKE :cs2)";
            $params['cs'] = '%' . $filters['client_search'] . '%';
            $params['cs2'] = '%' . $filters['client_search'] . '%';
        }
        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*)
                FROM appointments a
                JOIN clients c ON a.client_id = c.id
                $whereSQL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function changeStatus($appointmentId, $newStatus) {
        $appointment = $this->findById($appointmentId);
        if (!$appointment) throw new \Exception('Запись не найдена');
        $current = $appointment['status'];
        $allowed = [
            'ожидает' => ['подтверждена', 'отменена'],
            'подтверждена' => ['завершена', 'отменена'],
            'завершена' => [],
            'отменена' => [],
        ];
        if (!in_array($newStatus, $allowed[$current] ?? [])) {
            throw new \Exception('Недопустимый переход статуса');
        }
        $stmt = $this->pdo->prepare("UPDATE appointments SET status = :st WHERE id = :id");
        $stmt->execute(['st' => $newStatus, 'id' => $appointmentId]);
        $this->logChange($appointmentId, $current, $newStatus, $appointment['date_time'], null);
    }

    public function reschedule($appointmentId, $newDatetime) {
        $appointment = $this->findById($appointmentId);
        if (!$appointment) throw new \Exception('Запись не найдена');
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM appointments
                 WHERE specialist_id = :sid
                   AND date_time = :dt
                   AND id != :id
                   AND status NOT IN ('отменена', 'завершена')"
            );
            $stmt->execute(['sid' => $appointment['specialist_id'], 'dt' => $newDatetime, 'id' => $appointmentId]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception('Новое время занято');
            }
            $stmt = $this->pdo->prepare("UPDATE appointments SET date_time = :dt WHERE id = :id");
            $stmt->execute(['dt' => $newDatetime, 'id' => $appointmentId]);
            $this->logChange($appointmentId, $appointment['status'], $appointment['status'], $appointment['date_time'], $newDatetime);
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function logChange($appointmentId, $oldStatus, $newStatus, $oldDatetime, $newDatetime) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO appointment_log (appointment_id, old_datetime, new_datetime, old_status, new_status)
             VALUES (:aid, :old_dt, :new_dt, :old_st, :new_st)"
        );
        $stmt->execute([
            'aid' => $appointmentId,
            'old_dt' => $oldDatetime,
            'new_dt' => $newDatetime,
            'old_st' => $oldStatus,
            'new_st' => $newStatus,
        ]);
    }

    public function clientHasBookingOnDate($clientId, $date) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE client_id = ? AND DATE(date_time) = ? AND status != 'отменена'");
        $stmt->execute([$clientId, $date]);
        return $stmt->fetchColumn() > 0;
    }

    public function reportByDay($month, $year) {
        $stmt = $this->pdo->prepare(
            "SELECT DATE(a.date_time) as day,
                    COUNT(*) as total_appointments,
                    SUM(o.price) as total_revenue
             FROM appointments a
             JOIN objects o ON a.object_id = o.id
             WHERE YEAR(a.date_time) = :year
               AND MONTH(a.date_time) = :month
               AND a.status IN ('завершена', 'подтверждена')
             GROUP BY day
             ORDER BY day"
        );
        $stmt->execute(['year' => $year, 'month' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reportBySpecialist($dateFrom, $dateTo) {
        $stmt = $this->pdo->prepare(
            "SELECT s.id, s.first_name, s.last_name,
                    COUNT(a.id) as appointments_count,
                    SUM(o.price) as total_revenue
             FROM appointments a
             JOIN specialists s ON a.specialist_id = s.id
             JOIN objects o ON a.object_id = o.id
             WHERE DATE(a.date_time) BETWEEN :df AND :dt
               AND a.status IN ('завершена', 'подтверждена')
             GROUP BY s.id
             ORDER BY total_revenue DESC"
        );
        $stmt->execute(['df' => $dateFrom, 'dt' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelledReport($dateFrom, $dateTo) {
        $stmt = $this->pdo->prepare(
            "SELECT DATE(a.date_time) as day,
                    COUNT(*) as cancelled_count
             FROM appointments a
             WHERE a.status = 'отменена'
               AND DATE(a.date_time) BETWEEN :df AND :dt
             GROUP BY day
             ORDER BY day"
        );
        $stmt->execute(['df' => $dateFrom, 'dt' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}