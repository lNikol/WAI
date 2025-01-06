<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publiczna Galeria Zdjęć</title>
    <style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.thumbnails {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.thumbnail-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 200px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    background-color: #f9f9f9;
}

.thumbnail-item img {
    max-width: 100%;
    height: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
}

.image-info {
    text-align: center;
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

.pagination a.active {
    background-color: #333;
    color: #fff;
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

<?php if (isset($model['errors']) && !empty($model['errors'])): ?>
    <div class="error-messages">
        <?php foreach ($model['errors'] as $error): ?>
            <p style='color: red;'><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="container">
    <h1>Publiczna Galeria Zdjęć</h1>
    <form method="post" action="save_selected">
        <div class="gallery">
            <?php if (!empty($thumbnails)): ?>
                <div class="thumbnails">
                    <?php foreach ($thumbnails as $thumbnail): ?>
                        <div class="thumbnail-item">
                            <label for="image-<?= htmlspecialchars($thumbnail['id']) ?>">
                                <a href="<?= htmlspecialchars($thumbnail['watermark']) ?>" target="_blank">
                                    <img src="<?= htmlspecialchars($thumbnail['thumbnail']) ?>" alt="<?= htmlspecialchars($thumbnail['image_name']) ?>">
                                </a>
                                <div class="image-info">
                                    <p><strong>ID:
                                    <input type="checkbox" id="image-<?= htmlspecialchars($thumbnail['id']) ?>" 
                                        name="selected_images[]" 
                                        value="<?= htmlspecialchars($thumbnail['id']) ?>" 
                                        <?= isset($model['selected_images']) && in_array($thumbnail['id'], array_column($model['selected_images'], '_id')) ? 'checked' : '' ?>>
                                    </strong><?= htmlspecialchars($thumbnail['id']) ?></p>
                                        
                                    <p><strong>Autor:</strong> <?= htmlspecialchars($thumbnail['author']) ?></p>
                                    <p><strong>Status:</strong> <?= $thumbnail['public'] ? 'Publiczne' : 'Prywatne' ?></p>
                                    <p><strong>Title:</strong> <?= htmlspecialchars($thumbnail['image_name']) ?></p>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Brak publicznych zdjęć.</p>
            <?php endif; ?>
        </div>
        <button type="submit">Zapamiętaj wybrane</button>
    </form>

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
