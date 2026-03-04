# Hero Slides API – Fix "route api/hero-slides could not be found"

This 404 means the server running your Laravel API does not have the `hero-slides` routes registered. Do the following **on the server** where the API runs (e.g. where `https://api.esatb.org` is hosted).

## 1. Deploy latest code

Ensure the server has the code that includes:

- `routes/api.php` – lines with `HeroSlideController` and `Route::get('/hero-slides', ...)` etc.
- `app/Http/Controllers/Api/HeroSlideController.php`
- `app/Models/HeroSlide.php`
- `app/Http/Resources/Api/HeroSlideResource.php`
- `app/Http/Requests/Api/StoreHeroSlideRequest.php`
- `app/Http/Requests/Api/UpdateHeroSlideRequest.php`
- `database/migrations/2026_03_01_120000_create_hero_slides_table.php`

## 2. On the server (SSH or same machine as the app)

```bash
cd /path/to/e3-alumni-backend   # or your backend root

# Clear route cache (important if you had run route:cache before)
php artisan route:clear

# Run migrations (creates hero_slides table)
php artisan migrate

# Optional: if you use route caching in production, rebuild it after deploy
# php artisan route:cache
```

## 3. Verify

```bash
php artisan route:list --path=hero
```

You should see lines like:

- `GET|HEAD  api/hero-slides`  
- `POST      api/hero-slides`  
- `GET|HEAD  api/hero-slides/{heroSlide}`  
- etc.

## 4. If you use a different backend path

If your backend repo is in another folder or the live app is not this repo, copy the hero-slides related files from this repo into that project and run the same `route:clear` and `migrate` there.
