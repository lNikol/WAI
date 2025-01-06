<?php
require_once '../models/Image.php';


class ImageController {
    private $imageService;
    private $imageProcessingService;

    public function __construct($db) {
        $this->imageService = new ImageService($db);
        $this->imageProcessingService = new ImageProcessingService();
    }

    public function upload(&$model) {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        if (!$user_id) {
            $user_id = isset($_SESSION['anon_user_id']) ? $_SESSION['anon_user_id'] : uniqid('anon_', true) . bin2hex(random_bytes(4));
            $_SESSION['anon_user_id'] = $user_id;
            $model['anon_user_id'] = $user_id;
        }
        $model['user_id'] = $user_id;
        $model['user_name'] = get_user_name($user_id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
            $files = $_FILES['images'];
            $uploadDirectory = '../public/images/';
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
                            $this->imageProcessingService->addWatermark($upload_path, $watermark_path, $watermark);
                        } catch (Exception $e) {
                            $errors[] = "Error creating watermark for $fileName: " . $e->getMessage();
                        }

                        $thumbnail_path = $userFolder . 'thumbnail_' . $title;
                        try {
                            $this->imageProcessingService->createThumbnail($upload_path, $thumbnail_path, 200, 125);
                        } catch (Exception $e) {
                            $errors[] = "Error creating thumbnail for $fileName: " . $e->getMessage();
                        }
                        try {
                            $image = new Image($user_id, $userFolder, $title, $watermark_path, $thumbnail_path, $public, $author);
                            $this->imageService->saveImage($image);
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
                $model['errors'] = $errors;
                return 'upload_view';
            }
            return REDIRECT_PREFIX . 'public';
        }

        return 'upload_view';
    }

    public function search_image(&$model) {
                if(isset($_SESSION['user_id'])){
            $model['user_id'] = $_SESSION['user_id'];
        }
        return 'search_image_view';
    }

    public function search_images_by_title($query) {
        try {
            $images = $this->imageService->getImagesByTitle($query);
            return [
                'images' => $images,
                'errors' => []
            ];
        } catch (Exception $e) {
            return [
                'images' => [],
                'errors' => ["Error during search: " . $e->getMessage()]
            ];
        }
    }

    public function save_selected(&$model) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_images'])) {
            $selectedIds = $_POST['selected_images'];
            $images = [];
            $errors = [];

            foreach ($selectedIds as $id) {
                try {
                    $image = $this->imageService->getImageById(new MongoDB\BSON\ObjectId($id));
                    if ($image) {
                        $images[] = $image;
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
            $model = $images;
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $model['errors'] = $errors;
            }
                    if(isset($_SESSION['user_id'])){
            $model['user_id'] = $_SESSION['user_id'];
        }
            return 'selected_gallery_view';
        } else if (isset($_SESSION['selected_images'])) {
                    if(isset($_SESSION['user_id'])){
            $model['user_id'] = $_SESSION['user_id'];
        }
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
}
