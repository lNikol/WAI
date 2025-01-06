<?php

$routing = [
    '/' => 'register',
    '/register' => 'register',
    '/login' => 'login',
    '/logout' => 'logout',
    '/upload' => 'upload',
    '/public' => 'gallery_combined',
    '/save_selected' => 'save_selected',
    '/remove_selected' => 'remove_selected',
    '/search_image' => 'search_image',
    '/search_images_by_title' => 'search_images_by_title',
    '/gallery' => 'gallery_private'
];

function register($userController, $imageController, &$model) {
    return $userController->register($model);
}

function login($userController, $imageController, &$model) {
    return $userController->login($model);
}

function logout($userController, $imageController, &$model) {
    return $userController->logout();
}

function upload($userController, $imageController, &$model) {
    return $imageController->upload($model);
}

function gallery_private($userController, $imageController, &$model) {
    return $imageController->gallery_private($model);
}

function gallery_combined($userController, $imageController, &$model) {
    return $imageController->gallery_combined($model);
}

function save_selected($userController, $imageController, &$model) {
    return $imageController->save_selected();
}

function remove_selected($userController, $imageController, &$model) {
    return $imageController->remove_selected();
}

function search_image($userController, $imageController, &$model) {
    return $imageController->search_image();
}

function search_images_by_title($userController, $imageController, &$model) {
    $query = $_GET['query'];
    $results = $imageController->search_images_by_title($query);
    echo json_encode($results);
    exit;
}
