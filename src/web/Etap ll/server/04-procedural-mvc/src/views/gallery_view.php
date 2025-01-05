<?php
if (isset($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $error) {
        echo "<p style='color: red;'>$error</p>";
    }
    unset($_SESSION['errors']);
}
?>

<div class="gallery">
    <?php if (!empty($model['thumbnails'])): ?>
        <?php foreach ($model['thumbnails'] as $thumbnailData): ?>
            <div class="gallery-item">
                <a href="<?= $model['userFolder'] . str_replace('thumbnail_', 'watermark_', $thumbnailData['thumbnail']); ?>" target="_blank">
                    <img src="<?= $model['userFolder'] . $thumbnailData['thumbnail']; ?>" alt="Image thumbnail">
                </a>
                <p>Author: <?= htmlspecialchars($thumbnailData['author']); ?></p>
                <p>Status: <?= $thumbnailData['isPublic'] ? 'Public' : 'Private'; ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No images found.</p>
    <?php endif; ?>
</div>

<div class="pagination">
    <?php if ($model['currentPage'] > 1): ?>
        <a href="?page=<?= $model['currentPage'] - 1; ?>">&laquo; Previous</a>
    <?php endif; ?>

    <?php if ($model['currentPage'] < $model['totalPages']): ?>
        <a href="?page=<?= $model['currentPage'] + 1; ?>">Next &raquo;</a>
    <?php endif; ?>
</div>

<style>
.gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.gallery img {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
    cursor: pointer;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.pagination a {
    text-decoration: none;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #333;
    background-color: #f9f9f9;
}
</style>