<?php
namespace controllers;

class ImageController {

    public function index() {
        requireAuth('admin');

        try {
            $images = db()->fetchAll("SELECT * FROM site_images ORDER BY image_type, title");
        } catch (\PDOException $e) {
            error_log("Image index error: " . $e->getMessage());
            flash('error', 'Database error: Unable to load images. Please ensure Phase 2 database updates have been run.');
            $images = [];
        }

        view('admin/images', compact('images'));
    }

    public function upload() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/images');
        }

        $imageKey = $_POST['image_key'] ?? '';

        if (empty($imageKey)) {
            flash('error', 'Image key is required');
            redirect('/admin/images');
        }

        // Check if file was uploaded
        if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'No file uploaded or upload error occurred');
            redirect('/admin/images');
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image_file']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            flash('error', 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed');
            redirect('/admin/images');
        }

        // Validate file size (5MB max)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES['image_file']['size'] > $maxSize) {
            flash('error', 'File too large. Maximum size is 5MB');
            redirect('/admin/images');
        }

        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../storage/uploads/site-images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = $imageKey . '-' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        $webPath = '/storage/uploads/site-images/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadPath)) {
            flash('error', 'Failed to upload file');
            redirect('/admin/images');
        }

        // Update database
        try {
            $currentImage = db()->fetch("SELECT image_path FROM site_images WHERE image_key = ?", [$imageKey]);

            db()->execute("UPDATE site_images SET image_path = ?, updated_at = NOW(), uploaded_by = ? WHERE image_key = ?",
                         [$webPath, $_SESSION['user_id'], $imageKey]);

            logAudit('update_site_image', 'site_images', null, ['image_key' => $imageKey, 'new_path' => $webPath]);

            flash('success', 'Image uploaded successfully');
        } catch (\PDOException $e) {
            error_log("Image upload database error: " . $e->getMessage());
            // Delete the uploaded file since database update failed
            @unlink($uploadPath);
            flash('error', 'Database error: Unable to save image. Please ensure Phase 2 database updates have been run.');
        }

        redirect('/admin/images');
    }

    public function revertToDefault() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/images');
        }

        $imageKey = $_POST['image_key'] ?? '';

        if (empty($imageKey)) {
            flash('error', 'Image key is required');
            redirect('/admin/images');
        }

        try {
            // Get default path
            $image = db()->fetch("SELECT default_image_path FROM site_images WHERE image_key = ?", [$imageKey]);

            if (!$image) {
                flash('error', 'Image not found');
                redirect('/admin/images');
            }

            // Revert to default
            db()->execute("UPDATE site_images SET image_path = default_image_path, updated_at = NOW() WHERE image_key = ?",
                         [$imageKey]);

            logAudit('revert_site_image', 'site_images', null, ['image_key' => $imageKey]);

            flash('success', 'Image reverted to default');
        } catch (\PDOException $e) {
            error_log("Image revert error: " . $e->getMessage());
            flash('error', 'Database error: Unable to revert image. Please ensure Phase 2 database updates have been run.');
        }

        redirect('/admin/images');
    }
}
