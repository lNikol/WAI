# 📷 PHP Image Gallery

A full-stack MVC web application for uploading, managing, and browsing images — with private and public galleries, watermarking, and real-time search.

## Features

- **User authentication** — registration, login/logout with password hashing (`password_hash`) and session management
- **Image upload pipeline** — multi-file upload with server-side validation (MIME type, file size), automatic thumbnail generation (200×125px), and watermark overlay via PHP's GD Library
- **Gallery views** — private gallery (user's own images), combined public gallery (all public images merged with user's), and a saved/selected images collection
- **Real-time search** — AJAX-powered title search using MongoDB regex queries, returning results without page reload
- **Pagination** — server-side pagination across all gallery views
- **Anonymous uploads** — guests can upload images without registering (assigned a temporary session-based ID)

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8 (OOP, MVC pattern) |
| Database | MongoDB (via official PHP driver) |
| Image Processing | GD Library |
| Frontend | HTML, CSS, JavaScript, jQuery (AJAX) |
| Architecture | Front Controller + Dispatcher + Routing |

## Project Structure

```
├── controllers/
│   ├── AuthController.php
│   ├── ImageController.php
│   └── PrivateGalleryController.php
├── services/
│   ├── AuthService.php
│   ├── ImageService.php
│   ├── ImageProcessingService.php
│   ├── PrivateGalleryService.php
│   └── CombinedGalleryService.php
├── models/
│   ├── Image.php
│   └── User.php
├── views/
│   ├── gallery_view.php
│   ├── gallery_public_view.php
│   ├── selected_gallery_view.php
│   ├── search_image_view.php
│   ├── upload_view.php
│   ├── login_view.php
│   └── register_view.php
└── public/
    ├── front_controller.php
    ├── dispatcher.php
    └── routing.php
```

## How It Works

1. All requests pass through `front_controller.php`, which resolves the route, invokes the appropriate controller method, and renders the view with the returned model.
2. Uploaded images are stored on disk under a per-user folder. The pipeline generates two derivatives: a thumbnail and a watermarked version.
3. Image metadata (paths, author, public/private flag) is stored in MongoDB and queried for gallery rendering and search.
4. Selected images are persisted in the session, allowing users to build a personal collection across pages.

## Setup

### Requirements

- PHP 8+ with GD extension enabled
- MongoDB (tested on 4.x+)
- Composer (for MongoDB PHP driver)

### Installation

```bash
git clone https://github.com/your-username/php-image-gallery.git
cd php-image-gallery
composer install
```

Configure the MongoDB connection in `public/front_controller.php`:

```php
$mongo = new MongoDB\Client(
    "mongodb://<host>:<port>/wai",
    ['username' => '<user>', 'password' => '<password>']
);
```

Ensure the `public/images/` directory is writable and the font file for watermarking is present at `public/fonts/centurygothic_bold.ttf`.

Then point your web server's document root to the `public/` directory.

## Security Notes

- Passwords are hashed with `password_hash()` / `password_verify()`
- File uploads are validated by MIME type (not just extension) using `finfo`
- AJAX search endpoint is restricted to `XMLHttpRequest` calls only (checks `X-Requested-With` header)
- Output is escaped with `htmlspecialchars()` throughout views
- MongoDB credentials should be moved to environment variables before deploying

## License

MIT
