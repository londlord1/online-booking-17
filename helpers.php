<?php
function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('CSRF token mismatch');
        }
    }
}

function paginate($total, $current, $perPage = PER_PAGE) {
    $pages = ceil($total / $perPage);
    $html = '<nav><ul class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        if ($i == $current) {
            $html .= "<li><strong>$i</strong></li>";
        } else {
            $entity = h($_GET['entity'] ?? 'client');
            $search = h($_GET['search'] ?? '');
            $sort   = h($_GET['sort'] ?? '');
            $order  = h($_GET['order'] ?? '');
            $html .= "<li><a href='?entity=$entity&action=list&page=$i&search=$search&sort=$sort&order=$order'>$i</a></li>";
        }
    }
    $html .= '</ul></nav>';
    return $html;
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function old($key, $default = '') {
    return isset($_SESSION['old'][$key]) ? h($_SESSION['old'][$key]) : h($default);
}

function error_class($errors, $field) {
    return isset($errors[$field]) ? 'error' : '';
}

function error_text($errors, $field) {
    return isset($errors[$field]) ? '<span class="error-msg">' . h($errors[$field]) . '</span>' : '';
}
function generateBookingCode($id) {
    return strtoupper(substr(md5($id . 'salt'), 0, 8));
}