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
    return REDIRECT_PREFIX . 'register';
}


function upload(&$model) {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    if (!$user_id) {
        $user_id = isset($_SESSION['anon_user_id']) ? $_SESSION['anon_user_id'] : uniqid('anon_', true) . bin2hex(random_bytes(4));
        $_SESSION['anon_user_id'] = $user_id;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
        $files = $_FILES['images'];
        $uploadDirectory = '../../public/images/';  
        $maxFileSize = 1 * 1024 * 1024;  
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $errors = [];
        
        $public_values = isset($_POST['public']) ? $_POST['public'] : [];
        $authors = isset($_POST['authors']) ? $_POST['authors'] : [];

        foreach ($files['name'] as $key => $fileName) {
            $fileTmpName = $files['tmp_name'][$key];
            $fileSize = $files['size'][$key];
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                $errors[] = "File $fileName has an invalid format. Only JPG, JPEG, PNG are allowed.";
            }
            if ($fileSize > $maxFileSize) {
                $errors[] = "File $fileName is too large. Maximum size is 1MB.";
            }
            if (empty($errors)) {



                $uniqueName = uniqid() . '.' . $fileExtension;
                $user_folder = $uploadDirectory . $user_id . '/';
                
                $watermark = isset($_POST['watermarks'][$key]) ? $_POST['watermarks'][$key] : '';
                $public = isset($public_values[$key]) ? (bool)$public_values[$key] : true;        
                $title = isset($_POST['file_titles'][$key]) ? ($_POST['file_titles'][$key] . '.' . $fileExtension): $uniqueName;
                $author = isset($authors[$key]) ? $authors[$key] : 'Unknown';
                $upload_path = $user_folder . $title;

                if(!is_dir($user_folder)){
                    mkdir($user_folder, 0777, true);
                }
                if (move_uploaded_file($fileTmpName, $upload_path)) {
                    $_SESSION['uploaded_images'][] = $upload_path;
                    $watermark_path = $user_folder . 'watermark_' . $title;
                    try {
                        addWatermark($upload_path, $watermark_path, $watermark);
                    } catch (Exception $e) {
                        $errors[] = "Error creating watermark for $fileName: " . $e->getMessage();
                    }
                    
                    $thumbnail_path = $user_folder . 'thumbnail_' . $title;
                    try {
                        createThumbnail($upload_path, $thumbnail_path, 200, 125);
                    } catch (Exception $e) {
                        $errors[] = "Error creating thumbnail for $fileName: " . $e->getMessage();
                    }

                    save_image($user_id, $user_folder, $title, $watermark_path, $thumbnail_path, $public, $author);

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


function addWatermark($sourcePath, $destinationPath, $watermarkText) {
    $imageType = exif_imagetype($sourcePath);
    if ($imageType == IMAGETYPE_JPEG) {
        $image = imagecreatefromjpeg($sourcePath);
    } elseif ($imageType == IMAGETYPE_PNG) {
        $image = imagecreatefrompng($sourcePath);
    } else {
        throw new Exception("Unsupported image type.");
    }

    $imageWidth = imagesx($image);
    $imageHeight = imagesy($image);

    // Ustawienia tekstu
    $fontSize = 20;
    $fontFile = '../../public/fonts/centurygothic_bold.ttf'; 
    $textColor = imagecolorallocatealpha($image, 0, 0, 0, 50);

    // Wyśrodkowanie tekstu
    $textBoundingBox = imagettfbbox($fontSize, 0, $fontFile, $watermarkText);
    $textWidth = $textBoundingBox[2] - $textBoundingBox[0];
    $textHeight = $textBoundingBox[1] - $textBoundingBox[7];
    $x = ($imageWidth - $textWidth) / 2;
    $y = ($imageHeight - $textHeight) / 2;

    // Dodanie znaku wodnego
    imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $watermarkText);

    // Zapisanie obrazu
    if ($imageType == IMAGETYPE_JPEG) {
        imagejpeg($image, $destinationPath);
    } elseif ($imageType == IMAGETYPE_PNG) {
        imagepng($image, $destinationPath);
    }

    // Zwalnianie zasobów
    imagedestroy($image);
}



function createThumbnail($sourcePath, $destinationPath, $thumbnailWidth, $thumbnailHeight) {
    $imageType = exif_imagetype($sourcePath);
    if ($imageType == IMAGETYPE_JPEG) {
        $image = imagecreatefromjpeg($sourcePath);
    } elseif ($imageType == IMAGETYPE_PNG) {
        $image = imagecreatefrompng($sourcePath);
    } else {
        throw new Exception("Unsupported image type.");
    }

    $imageWidth = imagesx($image);
    $imageHeight = imagesy($image);

    // Stwórz pusty obraz miniatury
    $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

    // Skaluje obraz do rozmiaru miniatury
    imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $imageWidth, $imageHeight);

    // Zapisanie miniatury
    if ($imageType == IMAGETYPE_JPEG) {
        imagejpeg($thumbnail, $destinationPath);
    } elseif ($imageType == IMAGETYPE_PNG) {
        imagepng($thumbnail, $destinationPath);
    }

    // Zwalnianie zasobów
    imagedestroy($image);
    imagedestroy($thumbnail);
}
