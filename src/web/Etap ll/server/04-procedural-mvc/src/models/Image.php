<?php

class Image {
    private $user_id;
    private $user_folder;
    private $title;
    private $watermark_path;
    private $thumbnail_path;
    private $public;
    private $author;

    public function __construct($user_id, $user_folder, $title, $watermark_path, $thumbnail_path, $public, $author) {
        $this->user_id = $user_id;
        $this->user_folder = $user_folder;
        $this->title = $title;
        $this->watermark_path = $watermark_path;
        $this->thumbnail_path = $thumbnail_path;
        $this->public = $public;
        $this->author = $author;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getUserFolder() {
        return $this->user_folder;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getWatermarkPath() {
        return $this->watermark_path;
    }

    public function getThumbnailPath() {
        return $this->thumbnail_path;
    }

    public function isPublic() {
        return $this->public;
    }

    public function getAuthor() {
        return $this->author;
    }
}
