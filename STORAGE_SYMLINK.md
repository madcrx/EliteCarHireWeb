# Storage Symlink Setup

The `public/storage` is a symbolic link to `/storage` directory, allowing uploaded files to be accessible via HTTP.

## What This Does
- Uploaded vehicle images go to `/storage/vehicles/`
- The symlink makes them accessible at `/storage/vehicles/filename.jpg`
- Web URLs like `/storage/vehicles/image.jpg` work correctly

## Setup on Production

If deploying to a new server, recreate the symlink:

```bash
cd /path/to/EliteCarHireWeb/public
ln -sfn ../storage storage
```

Or use the artisan-style command pattern:
```bash
cd /path/to/EliteCarHireWeb
ln -sfn "$(pwd)/storage" "$(pwd)/public/storage"
```

## Verify Symlink
```bash
ls -la public/storage  # Should show: storage -> /path/to/storage
```

## Permissions
Ensure storage directory is writable:
```bash
chmod -R 755 storage
chmod -R 775 storage/vehicles  # Or 777 if needed
```

## Alternative: Direct Public Uploads

If symlinks don't work on your hosting (some shared hosts disable them):

1. Edit `app/helpers.php` uploadFile() function
2. Change destination to use `public/uploads/` instead of `storage/`
3. Update return path accordingly

```php
// Change this line:
$destination = __DIR__ . "/../public/uploads/$directory/" . $filename;
// And this:
return "uploads/$directory/$filename";
```
