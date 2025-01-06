<?php

class CombinedGalleryService {
    private $db;
    private $imageService;

    public function __construct($db) {
        $this->db = $db;
        $this->imageService = new ImageService($db);
    }

    public function gallery_combined(&$model, $page = 1, $itemsPerPage = 6) {
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
                        'thumbnail' => $thumbnail['thumbnail_path'],
                        'watermark' => $thumbnail['watermark_path'],
                        'image_name' => $thumbnail['image_name'],
                        'author' => $thumbnail['author_name'],
                        'public' => $thumbnail['public'],
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
                if ($image) {
                    $publicImagesWithMetadata[] = [
                        'thumbnail' => $image['thumbnail_path'],
                        'watermark' => $image['watermark_path'],
                        'image_name' => $image['image_name'],
                        'author' => $image['author_name'],
                        'public' => $image['public'],
                        'id' => $image['_id']
                    ];
                }
            }
        } catch (Exception $e) {
            $errors[] = "Error during reading public images: " . $e->getMessage();
        }

        $allImages = array_merge($userImagesWithMetadata, $publicImagesWithMetadata);

        $allImages = array_map("unserialize", array_unique(array_map("serialize", $allImages)));

        usort($allImages, function($a, $b) {
            return $a['public'] - $b['public'];
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
}
