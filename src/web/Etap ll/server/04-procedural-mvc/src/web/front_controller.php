<?php
require '../../vendor/autoload.php';

require_once '../dispatcher.php';
require_once '../routing.php';
require_once '../models/User.php';
require_once '../models/Image.php';
require_once '../services/UserService.php';
require_once '../services/ImageService.php';
require_once '../controllers/UserController.php';
require_once '../controllers/ImageController.php';

function get_db() {
    $mongo = new MongoDB\Client(
        "mongodb://192.168.56.10:27017/wai",
        [
            'username' => 'wai_web',
            'password' => 'w@i_w3b',
        ]);

    return $mongo->wai;
}

$db = get_db();
$userController = new UserController($db);
$imageController = new ImageController($db);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$action_url = $_GET['action'];
dispatch($routing, $action_url, $userController, $imageController);
