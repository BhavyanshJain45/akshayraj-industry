<?php
/**
 * Image Handler Class
 * Secure file upload and image processing
 */

class ImageHandler {
    /**
     * Validate image upload
     */
    public static function validateImage($file) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file upload');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed: ' . self::getErrorMessage($file['error']));
        }

        // Check file size with user-friendly message
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $maxMB = MAX_UPLOAD_SIZE / (1024 * 1024);
            $fileMB = $file['size'] / (1024 * 1024);
            throw new Exception(sprintf('File size (%.2f MB) exceeds maximum allowed (%.2f MB). Please choose a smaller image.', $fileMB, $maxMB));
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WebP allowed.');
        }

        // Get and validate extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMAGE_EXT)) {
            throw new Exception('Invalid file extension');
        }

        return true;
    }

    /**
     * Upload and process image
     */
    public static function uploadImage($file, $subfolder = 'products') {
        try {
            // Validate
            self::validateImage($file);

            // Ensure upload directory exists and is writable
            $uploadDir = UPLOADS_PATH . '/' . $subfolder;
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('Failed to create upload directory: ' . $uploadDir);
                }
            }
            
            // Verify directory is writable
            if (!is_writable($uploadDir)) {
                throw new Exception('Upload directory is not writable: ' . $uploadDir);
            }

            // Generate unique filename
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $filepath = $uploadDir . '/' . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to save uploaded file to ' . $filepath);
            }
            
            // Verify file was actually moved
            if (!file_exists($filepath)) {
                throw new Exception('File upload verification failed - file does not exist');
            }

            // Process image - but don't fail if resize fails
            try {
                self::resizeImage($filepath, $ext);
            } catch (Exception $resizeError) {
                error_log('Image resize warning (non-fatal): ' . $resizeError->getMessage());
            }

            return [
                'filename' => $filename,
                'path' => '/uploads/' . $subfolder . '/' . $filename,
                'url' => SITE_URL . '/uploads/' . $subfolder . '/' . $filename
            ];

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Resize image to optimal dimensions
     */
    private static function resizeImage($filepath, $ext) {
        try {
            $maxWidth = 1200;
            $maxHeight = 900;
            $quality = 85;

            if ($ext === 'webp') {
                $image = imagecreatefromwebp($filepath);
            } elseif ($ext === 'png') {
                $image = imagecreatefrompng($filepath);
            } else { // jpg, jpeg
                $image = imagecreatefromjpeg($filepath);
            }

            if ($image === false) {
                return; // Can't process, but don't fail
            }

            $origWidth = imagesx($image);
            $origHeight = imagesy($image);

            // Calculate new dimensions
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
            if ($ratio < 1) {
                $newWidth = (int)($origWidth * $ratio);
                $newHeight = (int)($origHeight * $ratio);

                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                imagedestroy($image);
                $image = $resized;
            }

            // Save optimized image
            if ($ext === 'webp') {
                imagewebp($image, $filepath, $quality);
            } elseif ($ext === 'png') {
                imagepng($image, $filepath, 8);
            } else {
                imagejpeg($image, $filepath, $quality);
            }

            imagedestroy($image);
        } catch (Exception $e) {
            // Log error but don't fail upload
            error_log('Image resize error: ' . $e->getMessage());
        }
    }

    /**
     * Delete image file
     */
    public static function deleteImage($filepath) {
        $fullPath = ROOT_PATH . $filepath;
        if (file_exists($fullPath) && strpos(realpath($fullPath), realpath(UPLOADS_PATH)) === 0) {
            unlink($fullPath);
            return true;
        }
        return false;
    }

    /**
     * Get upload error message
     */
    private static function getErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File was partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            UPLOAD_ERR_EXTENSION => 'Invalid file extension'
        ];
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
}
