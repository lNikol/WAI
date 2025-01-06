<?php
require '../../vendor/autoload.php';

require_once '../dispatcher.php';
require_once '../routing.php';
require_once '../controllers/AuthController.php';
require_once '../controllers/ImageController.php';
require_once '../controllers/CombinedGalleryController.php';
require_once '../controllers/PrivateGalleryController.php';
require_once '../services/AuthService.php';
require_once '../services/ImageService.php';
require_once '../services/CombinedGalleryService.php';
require_once '../services/PrivateGalleryService.php';
require_once '../services/ImageProcessingService.php';

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
$authController = new AuthController($db);
$imageController = new ImageController($db);
$combinedGalleryController = new CombinedGalleryController($db);
$privateGalleryController = new PrivateGalleryController($db);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$action_url = $_GET['action'];
dispatch($routing, $action_url, $authController, $imageController, $combinedGalleryController, $privateGalleryController);
