<?php

$routing = [
    '/' => 'register',
    '/register' => 'register',
    '/login' => 'login',
    '/logout' => 'logout',
    '/upload' => 'upload',
    '/public' => 'gallery_combined',
    '/gallery' => 'gallery_private',
    '/save_selected' => 'save_selected',
    '/remove_selected' => 'remove_selected',
    '/search_image' => 'search_image',
    '/search_images_by_title' => 'search_images_by_title'
];

function register($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $authController->register($model);
}

function login($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $authController->login($model);
}

function logout($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $authController->logout();
}

function upload($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $imageController->upload($model);
}

function gallery_private($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $privateGalleryController->gallery_private($model);
}

function gallery_combined($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $combinedGalleryController->gallery_combined($model);
}

function save_selected($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $imageController->save_selected($model);
}

function remove_selected($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $imageController->remove_selected();
}

function search_image($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    return $imageController->search_image($model);
}

function search_images_by_title($authController, $imageController, $combinedGalleryController, $privateGalleryController, &$model) {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') 
        { 
            http_response_code(403);
            die('Forbidden'); 
        } 
        
    $query = $_GET['query'];
    $results = $imageController->search_images_by_title($query);
    echo json_encode($results);
    exit;
}
