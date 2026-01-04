<?php

// Define the temp path
$tempPath = storage_path('app/temp');

// Create the directory if it doesn't exist
if (!file_exists($tempPath)) {
    @mkdir($tempPath, 0755, true);
}

// Get the real absolute path
$realPath = realpath($tempPath);

// If realpath works, use it with separator; otherwise use the original path
if ($realPath !== false) {
    $tempPath = $realPath . DIRECTORY_SEPARATOR;
} else {
    // Fallback: use the path as-is with separator
    $tempPath = rtrim($tempPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
}

return [
    'debug'       => env('APP_DEBUG_PDF', false),
    'binpath'     => base_path('lib/'),  // Changed from 'lib/' to base_path('lib/')
    'binfile'     => env('WKHTML2PDF_BIN_FILE', 'wkhtmltopdf-amd64.exe'),  // Added .exe extension
    'output_mode' => 'I',
    'temp_path'   => $tempPath,
];