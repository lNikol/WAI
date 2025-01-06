<?php

class PrivateGalleryService {
    private $db;
    private $imageService;

    public function __construct($db) {
        $this->db = $db;
        $this->imageService = new ImageService($db);
    }

    public function gallery_private(&$model, $page = 1, $itemsPerPage = 2) {
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


        $totalItems = count($userImagesWithMetadata);
        $totalPages = ceil($totalItems / $itemsPerPage);

        $currentPage = max(1, min($totalPages, $page));
        $offset = ($currentPage - 1) * $itemsPerPage;
        $paginatedThumbnails = array_slice($userImagesWithMetadata, $offset, $itemsPerPage);
        
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
}
