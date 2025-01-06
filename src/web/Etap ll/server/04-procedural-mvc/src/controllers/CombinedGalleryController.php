<?php

class CombinedGalleryController {
    private $combinedGalleryService;

    public function __construct($db) {
        $this->combinedGalleryService = new CombinedGalleryService($db);
    }

    public function gallery_combined(&$model, $page = 1, $itemsPerPage = 6) {
        return $this->combinedGalleryService->gallery_combined($model, $page, $itemsPerPage);
    }
}
