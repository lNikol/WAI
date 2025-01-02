<?php
require_once 'business.php';
require_once 'controller_utils.php';


function products(&$model)
{
    $products = get_products();
    $model['products'] = $products;

    return 'products_view';
}

function product(&$model)
{
    if (!empty($_GET['id'])) {
        $id = $_GET['id'];

        if ($product = get_product($id)) {
            $model['product'] = $product;
            return 'product_view';
        }
    }

    http_response_code(404);
    exit;
}

function edit(&$model)
{
    $product = [
        'name' => null,
        'price' => null,
        'description' => null,
        '_id' => null
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['name']) &&
            !empty($_POST['price']) /* && ...*/
        ) {
            $id = isset($_POST['id']) ? $_POST['id'] : null;

            $product = [
                'name' => $_POST['name'],
                'price' => (int)$_POST['price'],
                'description' => $_POST['description']
            ];

            if (save_product($id, $product)) {
                return 'redirect:products';
            }
        }
    } elseif (!empty($_GET['id'])) {
        $product = get_product($_GET['id']);
    }

    $model['product'] = $product;

    return 'edit_view';
}

function delete(&$model)
{
    if (!empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            delete_product($id);
            return 'redirect:products';

        } else {
            if ($product = get_product($id)) {
                $model['product'] = $product;
                return 'delete_view';
            }
        }
    }

    http_response_code(404);
    exit;
}

function cart(&$model)
{
    $model['cart'] = get_cart();
    return 'partial/cart_view';
}

function add_to_cart()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $product = get_product($id);

        $cart = &get_cart();
        $amount = isset($cart[$id]) ? $cart[$id]['amount'] + 1 : 1;

        $cart[$id] = ['name' => $product['name'], 'amount' => $amount];

        return 'redirect:' . $_SERVER['HTTP_REFERER'];
    }
}

function clear_cart()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['cart'] = [];
        return 'redirect:' . $_SERVER['HTTP_REFERER'];
    }
}


function register(&$model) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $model['error'] = 'Passwords do not match';
            return 'register_view';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $model['error'] = 'Invalid email format';
            return 'register_view'; 
        }

        $result = save_user($name, $email, $password);
        if ($result === 'success') {
            return REDIRECT_PREFIX . 'login';
        } else {
            $model['error'] = $result;
            return 'register_view';
        }
    }

    return 'register_view';
}



function login(&$model) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $user = authenticate_user($email, $password);
        if ($user) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = (string) $user['_id'];
            $_SESSION['user_name'] = $user['name'];
            return REDIRECT_PREFIX . 'upload';
        } else {
            $model['error'] = 'Invalid credentials';
        }
    }

    return 'login_view';
}


function logout() {
    session_destroy();
    return REDIRECT_PREFIX . '/';
}


function upload(&$model) {
    if (!isset($_SESSION['user_id'])) {
        return REDIRECT_PREFIX . 'login';
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
        $files = $_FILES['images'];
        $uploadDirectory = '../../public/images/';  
        $maxFileSize = 2 * 1024 * 1024;  
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
    
        $errors = [];  
    
        foreach ($files['name'] as $key => $fileName) {
            $fileTmpName = $files['tmp_name'][$key];
            $fileSize = $files['size'][$key];
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                $errors[] = "File $fileName has an invalid format. Only JPG, JPEG, PNG are allowed.";
            }
            if ($fileSize > $maxFileSize) {
                $errors[] = "File $fileName is too large. Maximum size is 2MB.";
            }
            if (empty($errors)) {
                $uniqueName = uniqid() . '.' . $fileExtension;
                $uploadPath = $uploadDirectory . $uniqueName;
    
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    $_SESSION['uploaded_images'][] = $uploadPath;
                } else {
                    $errors[] = "There was an error uploading file $fileName. Please try again.";
                }
            }
        }
    
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return 'upload_view';
            exit;
        }
        return REDIRECT_PREFIX . 'success';
    }
    
    // Jeżeli formularz nie został wysłany, wyświetlam formularz
    return 'upload_view';
}
