
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publiczna Galeria Zdjęć</title>
</head>
<body>
    <div class="container">
        <h1>Publiczna Galeria Zdjęć</h1>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="gallery">
            <?php if (!empty($thumbnails)): ?>
                <div class="thumbnails">
                    <?php foreach ($thumbnails as $thumbnail): ?>
                        <div class="thumbnail-item">
                            <a href="<?= htmlspecialchars($thumbnail['watermark_path']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($thumbnail['thumbnail_path']) ?>" alt="<?= htmlspecialchars($thumbnail['image_name']) ?>">
                            </a>
                            <div class="image-info">
                                <p><strong>Autor:</strong> <?= htmlspecialchars($thumbnail['author']) ?></p>
                                <p><strong>Status:</strong> <?= $thumbnail['isPublic'] ? 'Publiczne' : 'Prywatne' ?></p>
                                <p><strong>Tytuł:</strong> <?= htmlspecialchars($thumbnail['image_name']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Brak publicznych zdjęć.</p>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<style>
.gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.thumbnails{
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