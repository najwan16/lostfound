<?php
require_once 'controllers/AuthController.php';
require_once 'controllers/ProfileController.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$authController = new AuthController();
$profileController = new ProfileController($authController->getSessionManager());

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST received: " . print_r($_POST, true));
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            $result = $authController->login($email, $password);
            error_log("Login result: " . print_r($result, true));
            $success = $result['success'];
            $message = $result['message'];
            if ($success) {
                $authController->setRememberMeCookie($email, $authController->getSessionManager()->get('userId'), $remember);
                header('Location: index.php?action=profile');
                return;
            }
        }
        include 'views/login.php';
        break;
    case 'profile':
        if (!$authController->getSessionManager()->get('userId')) {
            header('Location: index.php?action=login');
            return;
        }
        $result = $profileController->showProfile();
        if ($result['success']) {
            $profil = $result['profil'];
            include 'views/profile.php';
        } else {
            $message = $result['message'];
            $success = false;
            include 'views/profile.php';
        }
        break;
    case 'update_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama'] ?? '';
            $nomorKontak = $_POST['nomorKontak'] ?? '';
            $result = $profileController->updateProfile($nama, $nomorKontak);
            $success = $result['success'];
            $message = $result['message'];
            $profil = $profileController->showProfile()['profil'];
            include 'views/profile.php';
        } else {
            header('Location: index.php?action=profile');
            return;
        }
        break;
    default:
        header('Location: index.php?action=login');
        return;
}
?>