<?php
use MongoDB\BSON\ObjectID;

function get_db()
{
    $mongo = new MongoDB\Client(
        "mongodb://192.168.56.10:27017/wai",
        [
            'username' => 'wai_web',
            'password' => 'w@i_w3b',
        ]);

    $db = $mongo->wai;

    return $db;
}

function save_user($name, $email, $password) {
    $db = get_db(); 
    $users = $db->users;

    $existing_user = $users->findOne(['email' => $email]);
    if ($existing_user) {
        return 'Email already exists';
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $users->insertOne([
        'name' => $name,
        'email' => $email,
        'password' => $hashed_password,
    ]);

    return 'success';
}


function authenticate_user($email, $password) {
    $db = get_db();
    $users = $db->users;

    $user = $users->findOne(['email' => $email]);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function save_image($user_id, $user_folder, $title, $watermark_path, $thumbnail_path, $public, $author) {
    $db = get_db();
    $images = $db->images;
    
    $original_image_path = $user_folder . $title;
    
    $images->insertOne([
        'author_id' => $user_id,
        'author_name' => $author,
        'image_name' => $title,
        'author_folder' => $user_folder,
        'public' => $public,
        'original_image' => $original_image_path,
        'thumbnail_path' => $thumbnail_path, 
        'watermark_path' => $watermark_path,
    ]);
    return false;
}


function get_user_images($user_id) {
    $db = get_db();
    $images = $db->images;
    return $images->find(['author_id' => $user_id])->toArray();
}


function get_image_by_id($id){
    $db = get_db();
    $images = $db->images;
    return $images->findOne(['_id' => $id]);
}


function get_all_public_images(){
    $db = get_db();
    $images = $db->images;
    return (iterator_to_array($images->find(['public' => true])));
}

function get_images_by_title($title){
    $db = get_db();
    $images = $db->images;

    if (!$db || !$images) {
        throw new Exception('Baza danych lub kolekcja obrazów nie jest dostępna.');
    }
    
    return $images->find([
        'image_name' => new MongoDB\BSON\Regex($title, 'i') // 'i' oznacza ignorowanie wielkości liter
    ]);
}