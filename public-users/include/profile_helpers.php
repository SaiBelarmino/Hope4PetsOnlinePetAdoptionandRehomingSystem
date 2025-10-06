<?php
/**
 * Resolve a user's profile photo to an absolute URL usable in img src.
 * - If $path is empty -> return default avatar path
 * - If $path is an absolute URL (http/https) -> return as-is
 * - If $path is a local path (relative or starts with uploads/) -> prefix with base relative path from views
 *
 * This keeps existing database values (which may be Google picture URLs or stored local paths)
 * and makes views simpler.
 *
 * @param string|null $path
 * @return string
 */
function resolve_profile_photo(?string $path): string {
    $default = '../../assets/images/profile/user-1.jpg';
    if (empty($path)) return $default;
    $path = trim($path);
    // If looks like an absolute URL, return directly
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    // Normalize slashes and remove surrounding whitespace
    $normalized = str_replace('\\', '/', $path);
    $normalized = trim($normalized);

    // If path contains the storage folder anywhere (handles full filesystem paths), extract it
    $pos = stripos($normalized, 'storage/');
    if ($pos !== false) {
        $sub = substr($normalized, $pos); // e.g. storage/uploads/images/profile-picture/...
        return '../../' . ltrim($sub, '/');
    }

    // Remove any leading ../ or ./ or leading slashes to make it project-root relative
    $normalized = preg_replace('#^(\.{1,2}/)+#', '', $normalized);
    $normalized = ltrim($normalized, '/');

    // If it now starts with storage/, prefix appropriately
    if (stripos($normalized, 'storage/') === 0) {
        return '../../' . $normalized;
    }

    // Otherwise assume it's a path relative to project root and prefix
    return '../../' . $normalized;
}

?>
