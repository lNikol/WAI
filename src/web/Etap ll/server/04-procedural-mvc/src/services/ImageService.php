<?php

class ImageService {
    private $db;
    private $images;

    public function __construct($db) {
        $this->db = $db;
        $this->images = $this->db->images;
    }

    public function saveImage($user_id, $user_folder, $title, $watermark_path, $thumbnail_path, $public, $author) {
        $this->images->insertOne([
            'author_id' => $user_id,
            'author_name' => $author,
            'image_name' => $title,
            'author_folder' => $user_folder,
            'public' => $public,
            'original_image' => $user_folder . $title,
            'thumbnail_path' => $thumbnail_path,
            'watermark_path' => $watermark_path,
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
