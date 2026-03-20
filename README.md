# Pomagayu - TikTok-Style Content Management Backend

A Laravel-based backend for a short-video/media feed application. Includes an admin panel for content management and a public REST API for frontend/mobile consumption.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+ (or SQLite for development)

## Installation

```bash
# Clone the project
git clone <repo-url> pomagayu
cd pomagayu

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
# For MySQL:
#   DB_CONNECTION=mysql
#   DB_DATABASE=pomagayu
#   DB_USERNAME=root
#   DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed the database (creates admin user + sample posts)
php artisan db:seed

# Create storage symlink for media uploads
php artisan storage:link

# Start the development server
php artisan serve
```

## Admin Panel

**URL:** `http://localhost:8000/admin/login`

**Default credentials:**
- Email: `admin@example.com`
- Password: `Pmg@Adm!n2026#Sec`

### Admin Routes

| Route | Description |
|---|---|
| `GET /admin/login` | Login page |
| `POST /admin/login` | Login action |
| `POST /admin/logout` | Logout |
| `GET /admin/dashboard` | Dashboard with stats |
| `GET /admin/posts` | Posts listing with search/filters |
| `GET /admin/posts/create` | Create post form |
| `POST /admin/posts` | Store new post |
| `GET /admin/posts/{id}/edit` | Edit post form |
| `PUT /admin/posts/{id}` | Update post |
| `DELETE /admin/posts/{id}` | Delete post |
| `POST /admin/posts/{id}/publish` | Publish post |
| `POST /admin/posts/{id}/unpublish` | Unpublish post |

## Public API

### Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/feed` | Paginated feed of published posts |
| `GET` | `/api/feed?type=video` | Filter feed by type (`video` or `image`) |
| `GET` | `/api/feed?per_page=20` | Custom page size |
| `GET` | `/api/posts/featured` | Featured published posts |
| `GET` | `/api/posts/{id}` | Single post details |
| `POST` | `/api/posts/{id}/view` | Increment view count |

### Example Response

```json
GET /api/feed

{
  "data": [
    {
      "id": 1,
      "type": "video",
      "title": "Amazing sunset timelapse",
      "description": "Captured at the beach last weekend.",
      "media_url": "http://localhost:8000/storage/videos/abc123.mp4",
      "thumbnail_url": null,
      "views_count": 1523,
      "is_featured": true,
      "published_at": "2024-01-15T10:30:00+00:00",
      "created_at": "2024-01-14T08:00:00+00:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
}
```

## Project Structure

```
app/
  Http/
    Controllers/
      Admin/          # Admin panel controllers
      Api/            # Public API controllers
    Middleware/
      EnsureAdmin.php # Admin-only access guard
    Requests/
      Admin/          # Form request validation
    Resources/        # API resource transformers
  Models/
    Post.php          # Main content entity
    User.php          # Admin user model
  Services/
    PostService.php        # Post business logic
    MediaUploadService.php # File upload handling
database/
  factories/          # Model factories for testing
  migrations/         # Database schema
  seeders/            # Data seeders
resources/views/admin/ # Blade templates for admin panel
routes/
  web.php             # Admin panel routes
  api.php             # Public API routes
storage/app/public/
  images/             # Uploaded images
  videos/             # Uploaded videos
  thumbnails/         # Thumbnails
```

## Upload Configuration

- **Images:** jpg, jpeg, png, webp (max 10MB)
- **Videos:** mp4, mov, webm (max 100MB)
- **Thumbnails:** jpg, jpeg, png, webp (max 5MB)

Files are stored in `storage/app/public/` and served via the storage symlink.

For larger uploads in production, adjust `upload_max_filesize` and `post_max_size` in your `php.ini`.

## Future Extensions

The architecture is designed to support:
- App user accounts and authentication
- Likes, comments, and bookmarks
- Hashtags and categories
- Content moderation workflows
- Analytics and engagement tracking
- Mobile push notifications
