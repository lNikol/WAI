<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prywatna Galeria Zdjęć</title>   
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

nav {
    background-color: #333;
    font-family: Arial, sans-serif;
    margin-bottom: 5px;
}

.menu {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
}

.menu li {
    position: relative;
    margin: 0;
}

.menu li a {
    color: white;
    text-decoration: none;
    padding: 14px 20px;
    display: block;
}

.menu li a:hover {
    background-color: #575757;
}
</style>
</head>
<body>
<?php
if (isset($model['errors'])) {
    foreach ($model['errors'] as $error) {
        echo "<p style='color: red;'>$error</p>";
    }
    unset($model['errors']);
}
?>

<nav>
    <ul class="menu">
        <li><a href="public">Public Gallery</a></li>
        <li><a href="gallery">Private Gallery</a></li>
        <li><a href="search_image">Search Image</a></li>
        <li><a href="upload">Upload Image</a></li>
        <li><a href="save_selected">Selected Images</a></li>
        
        <?php if (isset($model['user_id'])): ?>
            <li><a href="logout">Logout</a></li>
        <?php else: ?>
            <li><a href="register">Register</a></li>
            <li><a href="login">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div class="gallery">
    <?php if (!empty($thumbnails)): ?>
        <?php foreach ($thumbnails as $thumbnailData): ?>
            <div class="gallery-item">
                <a href="<?= htmlspecialchars($thumbnailData['watermark']); ?>" target="_blank">
                    <img src="<?= htmlspecialchars($thumbnailData['thumbnail']); ?>" alt="Image thumbnail">
                </a>
                <p>Title: <?= htmlspecialchars($thumbnailData['image_name']); ?></p>
                <p>Author: <?= htmlspecialchars($thumbnailData['author']); ?></p>
                <p>Status: <?= $thumbnailData['public'] ? 'Public' : 'Private'; ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No images found.</p>
    <?php endif; ?>
</div>

<div class="pagination">
    <?php if ($currentPage > 1): ?>
        <a href="?page=<?= $currentPage - 1; ?>">&laquo; Previous</a>
    <?php endif; ?>

    <?php if ($currentPage < $totalPages): ?>
        <a href="?page=<?= $currentPage + 1; ?>">Next &raquo;</a>
    <?php endif; ?>
</div>
</body>
</html>
