<?php

class ImageService {
    private $db;
    private $images;

    public function __construct($db) {
        $this->db = $db;
        $this->images = $this->db->images;
    }
    public function saveImage(Image $image) {
        $this->images->insertOne([
            'author_id' => $image->getUserId(),
            'author_name' => $image->getAuthor(),
            'image_name' => $image->getTitle(),
            'author_folder' => $image->getUserFolder(),
            'public' => $image->isPublic(),
            'original_image' => $image->getUserFolder() . $image->getTitle(),
            'thumbnail_path' => $image->getThumbnailPath(),
            'watermark_path' => $image->getWatermarkPath(),
        ]);

        return false;
    }
    public function getUserImages($user_id) {
        return $this->images->find(['author_id' => $user_id])->toArray();
    }

    public function getImageById($id) {
        return $this->images->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
    }

    public function getAllPublicImages() {
        return $this->images->find(['public' => true])->toArray();
    }

    public function getImagesByTitle($title) {
        return $this->images->find([
            'image_name' => new MongoDB\BSON\Regex($title, 'i')
        ])->toArray();
    }
}
