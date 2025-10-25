<?php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader);

// --- Helper functions ---
function get_tickets() {
    $file = __DIR__ . '/../tickets.json';
    if (!file_exists($file)) {
        $default = [];
        file_put_contents($file, json_encode($default));
        return $default;
    }
    return json_decode(file_get_contents($file), true);
}
function save_tickets($tickets) {
    $file = __DIR__ . '/../tickets.json';
    file_put_contents($file, json_encode($tickets));
}
function is_logged_in() {
    return isset($_SESSION['user']);
}
function get_user() {
    return $_SESSION['user'] ?? null;
}

// --- Routing and logic ---
$page = $_GET['page'] ?? 'landing';
$toast = null;
$session = is_logged_in();

if ($page === 'logout') {
    session_destroy();
    header('Location: ?page=landing');
    exit;
}

if ($page === 'login') {
    $error = null;
    $email = $_POST['email'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        if ($email === 'demo@ticketflow.com' && $password === 'demo123') {
            $_SESSION['user'] = [ 'email' => $email ];
            header('Location: ?page=dashboard');
            exit;
        } else {
            $error = 'Invalid credentials. Try demo@ticketflow.com / demo123';
        }
    }
    echo $twig->render('auth_login.twig', compact('error', 'email', 'session', 'page'));
    exit;
}

if ($page === 'signup') {
    $error = null;
    $email = $_POST['email'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if (!$email || !$password || !$confirm) {
            $error = 'Please fill in all fields.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $_SESSION['user'] = [ 'email' => $email ];
            header('Location: ?page=dashboard');
            exit;
        }
    }
    echo $twig->render('auth_signup.twig', compact('error', 'email', 'session', 'page'));
    exit;
}

if ($page === 'dashboard') {
    if (!is_logged_in()) {
        header('Location: ?page=login');
        exit;
    }
    $tickets = get_tickets();
    $total = count($tickets);
    $open = count(array_filter($tickets, fn($t) => $t['status'] === 'open'));
    $closed = count(array_filter($tickets, fn($t) => $t['status'] === 'closed' || $t['status'] === 'resolved'));
    echo $twig->render('dashboard.twig', compact('total', 'open', 'closed', 'session', 'page', 'toast'));
    exit;
}

if ($page === 'tickets') {
    if (!is_logged_in()) {
        header('Location: ?page=login');
        exit;
    }
    $tickets = get_tickets();
    $show_form = false;
    $ticket = null;
    $error = null;
    $form_action = 'create';
    // Handle actions
    $action = $_GET['action'] ?? null;
    if ($action === 'new') {
        $show_form = true;
    } elseif ($action === 'edit' && isset($_GET['id'])) {
        $ticket = null;
        foreach ($tickets as $t) {
            if ($t['id'] == $_GET['id']) $ticket = $t;
        }
        $show_form = true;
        $form_action = 'update&id=' . $_GET['id'];
    } elseif ($action === 'delete' && isset($_GET['id'])) {
        $tickets = array_filter($tickets, fn($t) => $t['id'] != $_GET['id']);
        save_tickets(array_values($tickets));
        $toast = [ 'message' => 'Ticket deleted.', 'type' => 'success' ];
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_GET['action']) && $_GET['action'] === 'create') {
            $title = trim($_POST['title'] ?? '');
            $status = $_POST['status'] ?? 'open';
            $description = trim($_POST['description'] ?? '');
            if (!$title) {
                $error = 'Title is required.';
                $show_form = true;
            } elseif (!in_array($status, ['open','in_progress','closed','resolved'])) {
                $error = 'Status must be valid.';
                $show_form = true;
            } else {
                $id = time() . rand(100,999);
                $tickets[] = [ 'id' => $id, 'title' => $title, 'status' => $status, 'description' => $description ];
                save_tickets($tickets);
                $toast = [ 'message' => 'Ticket created!', 'type' => 'success' ];
            }
        } elseif (isset($_GET['action']) && strpos($_GET['action'], 'update') === 0) {
            $id = explode('=', $_GET['action'])[1] ?? null;
            foreach ($tickets as &$t) {
                if ($t['id'] == $id) {
                    $t['title'] = trim($_POST['title'] ?? '');
                    $t['status'] = $_POST['status'] ?? 'open';
                    $t['description'] = trim($_POST['description'] ?? '');
                }
            }
            save_tickets($tickets);
            $toast = [ 'message' => 'Ticket updated.', 'type' => 'success' ];
        }
    }
    echo $twig->render('tickets.twig', compact('tickets', 'show_form', 'ticket', 'error', 'form_action', 'session', 'page', 'toast'));
    exit;
}

// Default: landing
echo $twig->render('landing.twig', compact('session', 'page'));
