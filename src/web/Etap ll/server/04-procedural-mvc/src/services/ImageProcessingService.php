<?php

class ImageProcessingService {
    public function addWatermark($sourcePath, $destinationPath, $watermarkText) {
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

    public function createThumbnail($sourcePath, $destinationPath, $thumbnailWidth, $thumbnailHeight) {
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
}
