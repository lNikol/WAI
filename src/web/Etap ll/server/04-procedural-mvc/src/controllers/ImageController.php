<?php

class ImageController {
    private $imageService;

    public function __construct($db) {
        $this->imageService = new ImageService($db);
    }

    public function upload(&$model) { 
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
                } else if ($fileSize === 0) { 
                    $errors[] = "File $fileName is too small. Minimum size is > 0 bytes."; 
                } 
                
                $watermark = isset($watermarks[$key]) ? $watermarks[$key] : ''; 
                $file_title = isset($file_titles[$key]) ? $file_titles[$key] : pathinfo($fileName, PATHINFO_FILENAME); 
                
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
                    
                    if (!is_dir($userFolder)) { 
                        mkdir($userFolder, 0777, true); 
                    } 
                    
                    if (move_uploaded_file($fileTmpName, $upload_path)) { 
                        $_SESSION['uploaded_images'][] = $upload_path; 
                        $watermark_path = $userFolder . 'watermark_' . $title; 
                        
                        try { 
                            $this->addWatermark($upload_path, $watermark_path, $watermark); 
                        } catch (Exception $e) { 
                            $errors[] = "Error creating watermark for $fileName: " . $e->getMessage(); 
                        } 
                        
                        $thumbnail_path = $userFolder . 'thumbnail_' . $title; 
                        
                        try { 
                            $this->createThumbnail($upload_path, $thumbnail_path, 200, 125); 
                        } catch (Exception $e) { 
                            $errors[] = "Error creating thumbnail for $fileName: " . $e->getMessage(); 
                        } 
                        
                        try { 
                            $this->imageService->saveImage($user_id, $userFolder, $title, $watermark_path, $thumbnail_path, $public, $author); 
                        } catch (Exception $e) { 
                            $errors[] = "Error during save an image for $fileName: " . $e->getMessage(); 
                        } 
                    } else { 
                        $errors[] = "There was an error uploading file $fileName. Please try again."; 
                    } 
                } 
            } 
            
            if (!empty($errors)) { 
                $_SESSION['errors'] = $errors; 
                return 'upload_view'; 
            } 
            
            return REDIRECT_PREFIX . 'public'; 
        } 
        
        return 'upload_view'; 
    } 
    
    private function addWatermark($sourcePath, $destinationPath, $watermarkText) { 
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
        $fontSize = 20; 
        $fontFile = '../../public/fonts/centurygothic_bold.ttf'; 
        $textColor = imagecolorallocatealpha($image, 0, 0, 0, 50); 
        
        $textBoundingBox = imagettfbbox($fontSize, 0, $fontFile, $watermarkText); 
        $textWidth = $textBoundingBox[2] - $textBoundingBox[0]; 
        $textHeight = $textBoundingBox[1] - $textBoundingBox[7]; 
        $x = ($imageWidth - $textWidth) / 2; 
        $y = ($imageHeight - $textHeight) / 2; 
        
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $watermarkText); 
        
        if ($imageType == IMAGETYPE_JPEG) { 
            imagejpeg($image, $destinationPath); 
        } elseif ($imageType == IMAGETYPE_PNG) { 
            imagepng($image, $destinationPath); 
        } 
        
        imagedestroy($image); 
    } 
    
    private function createThumbnail($sourcePath, $destinationPath, $thumbnailWidth, $thumbnailHeight) { 
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
        $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight); 
        
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $imageWidth, $imageHeight); 
        
        if ($imageType == IMAGETYPE_JPEG) { 
            imagejpeg($thumbnail, $destinationPath); 
        } elseif ($imageType == IMAGETYPE_PNG) { 
            imagepng($thumbnail, $destinationPath); 
        } 
        
        imagedestroy($image); 
        imagedestroy($thumbnail); 
    }
    
    public function gallery_private(&$model, $page = 1, $itemsPerPage = 2) {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $errors = [];
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "guest";
    
        if ($user_id == "guest" || !$user_id) {
            $errors[] = "User not logged in.";
            $_SESSION['errors'] = $errors;
            return REDIRECT_PREFIX . 'public';
        }
    
        try {
            $userImages = $this->imageService->getUserImages($user_id);
        } catch (Exception $e) {
            $errors[] = "Error fetching private images: " . $e->getMessage();
            $_SESSION['errors'] = $errors;
            return 'gallery_view';
        }
    
        if (empty($userImages)) {
            $errors[] = "No images found for the user.";
            $model = [
                'thumbnails' => [],
                'currentPage' => $page,
                'totalPages' => 0
            ];
            $_SESSION['errors'] = $errors;
            return 'gallery_view';
        }
    
        $thumbnailsWithMetadata = array_map(function ($image) {
            return [
                'thumbnail' => $image['thumbnail_path'],
                'watermark' => $image['watermark_path'],
                'image_name' => $image['image_name'],
                'author' => $image['author_name'],
                'isPublic' => $image['public'],
                'id' => $image['_id']
            ];
        }, $userImages);
    
        $totalItems = count($thumbnailsWithMetadata);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $currentPage = max(1, min($totalPages, $page));
        $offset = ($currentPage - 1) * $itemsPerPage;
        $paginatedThumbnails = array_slice($thumbnailsWithMetadata, $offset, $itemsPerPage);
    
        $model = [
            'thumbnails' => $paginatedThumbnails,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages
        ];
    
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return 'gallery_view';
        }
    
        return 'gallery_view';
    }    

    public function gallery_combined(&$model, $page = 1, $itemsPerPage = 4) {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $errors = [];
        
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "guest";
        if ($user_id == "guest" || !$user_id) {
            $errors[] = "User not logged in.";
        }

        $userImagesWithMetadata = [];
        try {
            $userImages = $this->imageService->getUserImages($user_id);
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

        $publicImagesWithMetadata = [];
        try {
            $publicImages = $this->imageService->getAllPublicImages();

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

        $allImages = array_map("unserialize", array_unique(array_map("serialize", $allImages)));

        usort($allImages, function($a, $b) {
            return $a['isPublic'] - $b['isPublic'];
        });

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

    public function save_selected() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_images'])) {
            $selectedIds = $_POST['selected_images'];
            $images = [];
            $errors = [];

            foreach ($selectedIds as $id) {
                try {
                    $image = $this->imageService->getImageById(new MongoDB\BSON\ObjectId($id));
                    if ($image) {
                        $images[] = (array) $image;
                    } else {
                        throw new Exception("Nie znaleziono obrazu o ID: " . htmlspecialchars($id));
                    }
                } catch (Exception $e) {
                    $errors[] = "Błąd podczas pobierania informacji o obrazie $id: " . $e->getMessage();
                }
            }

            if (isset($_SESSION['selected_images']) && is_array($_SESSION['selected_images'])) {
                $existingImages = $_SESSION['selected_images'];
                $images = array_merge($existingImages, $images);
                $images = array_unique($images, SORT_REGULAR);
            }

            $_SESSION['selected_images'] = $images;

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
            }
            return 'selected_gallery_view';
        } else if (isset($_SESSION['selected_images'])) {
            return 'selected_gallery_view';
        }

        return 'gallery_public_view';
    }

    public function remove_selected() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_images'])) {
            $removeIds = $_POST['remove_images'];
            $errors = [];
            if (!isset($_SESSION['selected_images']) || empty($_SESSION['selected_images'])) {
                $errors[] = "Brak wybranych zdjęć w sesji.";
                if (!empty($errors)) {
                    $_SESSION['errors'] = $errors;
                    return 'selected_gallery_view';
                }
                return 'selected_gallery_view';
            }

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

    public function search_image() {
        return 'search_image_view';
    }

    public function search_images_by_title($title) {
        $response = ['images' => [], 'errors' => []];
        try {
            if (!is_string($title)) {
                $response['errors'][] = 'Nieprawidłowy format tytułu';
                return $response;
            }
            $cursor = $this->imageService->getImagesByTitle($title);
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
}
