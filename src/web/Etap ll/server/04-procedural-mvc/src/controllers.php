<?php
require_once 'business.php';


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
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    return REDIRECT_PREFIX . 'register';
}


// sprawdzić prawo na modyfikację folderów przez deweloperski porty
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
        $allowedMimeTypes = ['image/jpeg', 'image/png'];
        $errors = [];
        
        $public_values = isset($_POST['public']) ? $_POST['public'] : [];
        $authors = isset($_POST['authors']) ? $_POST['authors'] : [];
        $watermarks = isset($_POST['watermarks']) ? $_POST['watermarks'] : [];
        $file_titles = isset($_POST['file_titles']) ? $_POST['file_titles'] : [];

        foreach ($files['name'] as $key => $fileName) {
            $fileTmpName = $files['tmp_name'][$key];
            $fileSize = $files['size'][$key];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileMimeType = $finfo->file($fileTmpName);

            if (!in_array($fileMimeType, $allowedMimeTypes)) {
                $errors[] = "File $fileName has an invalid format. Only JPG and PNG are allowed.";
            }

            if ($fileSize > $maxFileSize) {
                $errors[] = "File $fileName is too large. Maximum size is 1MB.";
            } else if($fileSize === 0){
                $errors[] = "File $fileName is too small. Minimum size is > 0 bytes.";
            }

            $watermark = isset($watermarks[$key]) ? $watermarks[$key] : '';
            $file_title  = isset($file_titles[$key]) ? $file_titles[$key] : pathinfo($fileName, PATHINFO_FILENAME);

            if (strlen($watermark) < 1 || strlen($watermark) > 70) {
                $errors[] = "Watermark for file $fileName must be between 1 and 70 characters long.";
            }

            if (strlen($file_title) > 70) {
                $errors[] = "Title for file $fileName must not exceed 70 characters.";
            }

            if (empty($errors)) {
                $uniqueName = uniqid() . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
                $userFolder = $uploadDirectory . $user_id . '/';
                     
                $title = strlen($file_title) > 0 ? $file_title . '.' . pathinfo($fileName, PATHINFO_EXTENSION) : $uniqueName;
                $public = isset($public_values[$key]) ? (bool)$public_values[$key] : true;        
                $author = isset($authors[$key]) ? $authors[$key] : 'Unknown';
                $upload_path = $userFolder . $title;

                if(!is_dir($userFolder)){
                    mkdir($userFolder, 0777, true);
                }
                if (move_uploaded_file($fileTmpName, $upload_path)) {
                    $_SESSION['uploaded_images'][] = $upload_path;
                    $watermark_path = $userFolder . 'watermark_' . $title;
                    try {
                        addWatermark($upload_path, $watermark_path, $watermark);
                    } catch (Exception $e) {
                        $errors[] = "Error creating watermark for $fileName: " . $e->getMessage();
                    }
                    
                    $thumbnail_path = $userFolder . 'thumbnail_' . $title;
                    try {
                        createThumbnail($upload_path, $thumbnail_path, 200, 125);
                    } catch (Exception $e) {
                        $errors[] = "Error creating thumbnail for $fileName: " . $e->getMessage();
                    }
                    try {
                        save_image($user_id, $userFolder, $title, $watermark_path, $thumbnail_path, $public, $author);
                    } catch (Exception $e) {
                        $errors[] = "Error druging save an image for $fileName: " . $e->getMessage();
                    }

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
        return REDIRECT_PREFIX . 'public';
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

    // Tworzenie pustego obrazu miniatury
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


function gallery_combined(&$model, $page = 1, $itemsPerPage = 4) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $errors = [];
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "guest";
    if ($user_id == "guest" || !$user_id) {
        $errors[] = "User not logged in.";
    }

    // Pobranie prywatnych zdjęć z bazy danych
    $userImagesWithMetadata = [];
    try {
        $userImages = get_user_images($user_id);
        foreach ($userImages as $thumbnail) {
            if ($thumbnail) {
                $userImagesWithMetadata[] = [
                    'thumbnail_path' => $thumbnail['thumbnail_path'],
                    'watermark_path' => $thumbnail['watermark_path'],
                    'image_name' => $thumbnail['image_name'],
                    'author' => $thumbnail['author_name'],
                    'isPublic' => $thumbnail['public'],
                    'id' => $thumbnail['_id']
                ];
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error fetching private images: " . $e->getMessage();
    }

    // Pobranie publicznych zdjęć z bazy danych
    $publicImagesWithMetadata = [];
    try {
        $publicImages = get_all_public_images();

        // Przetwarzanie publicznych zdjęć
        foreach ($publicImages as $image) {
            $publicImagesWithMetadata[] = [
                'thumbnail_path' => isset($image['thumbnail_path']) ? $image['thumbnail_path'] : $image['original_image'],
                'watermark_path' => isset($image['watermark_path']) ? $image['watermark_path'] : $image['original_image'],
                'image_name' => isset($image['image_name']) ? $image['image_name'] : basename($image),
                'author' => isset($image['author_name']) ? $image['author_name'] : "Unknown",
                'isPublic' => isset($image['public']) ? $image['public'] : true,
                'id' => $image['_id']
            ];
        }
    } catch (Exception $e) {
        $errors[] = "Error during reading public images: " . $e->getMessage();
    }

    $allImages = array_merge($userImagesWithMetadata, $publicImagesWithMetadata);

    // Usuwanie duplikatów zdjęć
    $allImages = array_map("unserialize", array_unique(array_map("serialize", $allImages)));

    // Sortowanie, aby prywatne zdjęcia były pierwsze
    usort($allImages, function($a, $b) {
        return $a['isPublic'] - $b['isPublic'];
    });

    // Paginacja
    $totalItems = count($allImages);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($totalPages, $page));
    $offset = ($currentPage - 1) * $itemsPerPage;
    $paginatedThumbnails = array_slice($allImages, $offset, $itemsPerPage);

    $model = [
        'thumbnails' => $paginatedThumbnails,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages
    ];

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        return 'gallery_public_view';
    }

    return 'gallery_public_view';
}


function save_selected() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_images'])) {
        $selectedIds = $_POST['selected_images'];
        $images = [];
        $errors = [];

        // Pobieram informacje o nowych zdjęciach
        foreach ($selectedIds as $id) {
            try {
                $image = get_image_by_id(new MongoDB\BSON\ObjectId($id));
                if ($image) {
                    $images[] = (array) $image;
                } else {
                    throw new Exception("Nie znaleziono obrazu o ID: " . htmlspecialchars($id));
                }
            } catch (Exception $e) {
                $errors[] = "Błąd podczas pobierania informacji o obrazie $id: " . $e->getMessage();
            }
        }

        // Jeśli sesja już zawiera wybrane zdjęcia, łącze je z nowymi
        if (isset($_SESSION['selected_images']) && is_array($_SESSION['selected_images'])) {
            // Usuwanie duplikatów na podstawie `_id`
            $existingImages = $_SESSION['selected_images'];
            $images = array_merge($existingImages, $images);
            $images = array_unique($images, SORT_REGULAR); // Usuwanie duplikatów
        }

        // Zapisuje do sesji
        $_SESSION['selected_images'] = $images;

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
        }
        return 'selected_gallery_view';
    }
    else if(isset($_SESSION['selected_images'])){
        return 'selected_gallery_view';
    }

    return 'gallery_public_view';
}


function remove_selected() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_images'])) {
        $removeIds = $_POST['remove_images']; // Lista ID zdjęć do usunięcia
        $errors = [];
        if (!isset($_SESSION['selected_images']) || empty($_SESSION['selected_images'])) {
            $errors[] = "Brak wybranych zdjęć w sesji.";
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                return 'selected_gallery_view';
            }
            return 'selected_gallery_view';
        }

        // Filtruję sesję, pozostawiając tylko te zdjęcia, których ID nie są w $removeIds
        $_SESSION['selected_images'] = array_filter($_SESSION['selected_images'], function ($image) use ($removeIds) {
            return !in_array($image['_id'], $removeIds);
        });
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return 'selected_gallery_view';
        }
        return 'selected_gallery_view';
    }
    return 'selected_gallery_view';
}


function search_image(){
    return 'search_image_view';
}


function search_images_by_title($title) {
    $response = ['images' => [], 'errors' => []];
    try {
        if (!is_string($title)) {
            $response['errors'][] = 'Nieprawidłowy format tytułu';
            return $response;
        }
        $cursor = iterator_to_array(get_images_by_title($title));
        foreach ($cursor as $image) {
            $response['images'][] = [
                'thumbnail_path' => $image['thumbnail_path'] ?? 'brak-miniatury.jpg',
                'image_name' => $image['image_name'] ?? 'Nieznany tytuł',
            ];
        }
    } catch (Exception $e) {
        $response['errors'][] = 'Błąd podczas pobierania obrazów: ' . $e->getMessage();
    }

    return $response;
}


if (isset($_GET['query'])) {
    try {
        $query = $_GET['query'];
        $results = search_images_by_title($query);
        echo json_encode($results);
    } catch (Exception $e) {
        echo json_encode(['errors' => [$e->getMessage()]]);
    }
    exit;
}
