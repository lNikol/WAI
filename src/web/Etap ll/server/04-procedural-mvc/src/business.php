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

function get_products()
{
    $db = get_db();
    return $db->products->find()->toArray();
}

function get_products_by_category($cat)
{
    $db = get_db();
    $products = $db->products->find(['cat' => $cat]);
    return $products;
}

function get_product($id)
{
    $db = get_db();
    return $db->products->findOne(['_id' => new ObjectID($id)]);
}

function save_product($id, $product)
{
    $db = get_db();

    if ($id == null) {
        $db->products->insertOne($product);
    } else {
        $db->products->replaceOne(['_id' => new ObjectID($id)], $product);
    }

    return true;
}

function delete_product($id)
{
    $db = get_db();
    $db->products->deleteOne(['_id' => new ObjectID($id)]);
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


function get_image_info($thumbnail_path){
    $db = get_db();
    $images = $db->images;
    return $images->findOne(['thumbnail_path' => $thumbnail_path]);
}

function get_all_public_images(){
    $db = get_db();
    $images = $db->images;
    return (iterator_to_array($images->find(['public' => true])));
}