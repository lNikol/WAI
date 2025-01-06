<?php

class PrivateGalleryController {
    private $privateGalleryService;

    public function __construct($db) {
        $this->privateGalleryService = new PrivateGalleryService($db);
    }

    public function gallery_private(&$model, $page = 1, $itemsPerPage = 2) {
        return $this->privateGalleryService->gallery_private($model, $page, $itemsPerPage);
    }
}
