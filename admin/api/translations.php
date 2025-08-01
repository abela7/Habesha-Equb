<?php
/**
 * HabeshaEqub Translation Management API
 * Handles CRUD operations for language files (en.json and am.json)
 */

// Start session and authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent any HTML output
ob_start();

// Disable error display to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Set JSON headers immediately
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit('{}');
}

// Clean JSON response function
function json_response($success, $message = '', $data = []) {
    // Clear any output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Check admin authentication
function is_admin_authenticated() {
    return isset($_SESSION['admin_id']) && 
           isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true;
}

if (!is_admin_authenticated()) {
    json_response(false, 'Access denied. Admin authentication required.');
}

// Define language file paths
$language_files = [
    'en' => '../../languages/en.json',
    'am' => '../../languages/am.json'
];

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    json_response(false, 'Invalid JSON data received');
}

$action = $data['action'] ?? '';

if (empty($action)) {
    json_response(false, 'No action specified');
}

// Handle different actions
switch ($action) {
    case 'save':
        // Save translations to files
        if (!isset($data['translations'])) {
            json_response(false, 'No translations data provided');
        }
        
        $translations = $data['translations'];
        $errors = [];
        $saved_files = [];
        
        foreach ($language_files as $lang => $file_path) {
            if (isset($translations[$lang])) {
                try {
                    // Create backup first
                    if (file_exists($file_path)) {
                        $backup_path = $file_path . '.backup.' . date('Y-m-d_H-i-s');
                        if (!copy($file_path, $backup_path)) {
                            $errors[] = "Failed to create backup for $lang";
                            continue;
                        }
                    }
                    
                    // Format and save JSON
                    $json_data = json_encode($translations[$lang], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    
                    if ($json_data === false) {
                        $errors[] = "Failed to encode JSON for $lang";
                        continue;
                    }
                    
                    // Ensure directory exists
                    $dir = dirname($file_path);
                    if (!is_dir($dir)) {
                        if (!mkdir($dir, 0755, true)) {
                            $errors[] = "Failed to create directory for $lang";
                            continue;
                        }
                    }
                    
                    // Write to file
                    if (file_put_contents($file_path, $json_data) === false) {
                        $errors[] = "Failed to write file for $lang";
                        continue;
                    }
                    
                    $saved_files[] = $lang;
                    
                } catch (Exception $e) {
                    $errors[] = "Error saving $lang: " . $e->getMessage();
                }
            }
        }
        
        if (empty($errors)) {
            json_response(true, 'All translations saved successfully', [
                'saved_files' => $saved_files,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            json_response(false, 'Some files failed to save: ' . implode(', ', $errors), [
                'saved_files' => $saved_files,
                'errors' => $errors
            ]);
        }
        break;
        
    case 'load':
        // Load current translations
        $translations = [];
        $errors = [];
        
        foreach ($language_files as $lang => $file_path) {
            try {
                if (file_exists($file_path)) {
                    $content = file_get_contents($file_path);
                    if ($content === false) {
                        $errors[] = "Failed to read $lang file";
                        continue;
                    }
                    
                    $decoded = json_decode($content, true);
                    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                        $errors[] = "Invalid JSON in $lang file: " . json_last_error_msg();
                        continue;
                    }
                    
                    $translations[$lang] = $decoded ?: [];
                } else {
                    $translations[$lang] = [];
                }
            } catch (Exception $e) {
                $errors[] = "Error loading $lang: " . $e->getMessage();
                $translations[$lang] = [];
            }
        }
        
        json_response(true, 'Translations loaded successfully', [
            'translations' => $translations,
            'errors' => $errors
        ]);
        break;
        
    case 'backup':
        // Create backup of all translation files
        $backups = [];
        $errors = [];
        $timestamp = date('Y-m-d_H-i-s');
        
        foreach ($language_files as $lang => $file_path) {
            try {
                if (file_exists($file_path)) {
                    $backup_path = $file_path . '.backup.' . $timestamp;
                    if (copy($file_path, $backup_path)) {
                        $backups[] = [
                            'language' => $lang,
                            'original' => $file_path,
                            'backup' => $backup_path,
                            'size' => filesize($backup_path)
                        ];
                    } else {
                        $errors[] = "Failed to backup $lang";
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Error backing up $lang: " . $e->getMessage();
            }
        }
        
        if (empty($errors)) {
            json_response(true, 'Backup created successfully', [
                'backups' => $backups,
                'timestamp' => $timestamp
            ]);
        } else {
            json_response(false, 'Backup failed: ' . implode(', ', $errors), [
                'backups' => $backups,
                'errors' => $errors
            ]);
        }
        break;
        
    case 'restore':
        // Restore from backup
        $backup_timestamp = $data['timestamp'] ?? '';
        if (empty($backup_timestamp)) {
            json_response(false, 'No backup timestamp provided');
        }
        
        $restored = [];
        $errors = [];
        
        foreach ($language_files as $lang => $file_path) {
            try {
                $backup_path = $file_path . '.backup.' . $backup_timestamp;
                
                if (file_exists($backup_path)) {
                    if (copy($backup_path, $file_path)) {
                        $restored[] = $lang;
                    } else {
                        $errors[] = "Failed to restore $lang from backup";
                    }
                } else {
                    $errors[] = "Backup file not found for $lang";
                }
            } catch (Exception $e) {
                $errors[] = "Error restoring $lang: " . $e->getMessage();
            }
        }
        
        if (empty($errors)) {
            json_response(true, 'Restored from backup successfully', [
                'restored' => $restored,
                'timestamp' => $backup_timestamp
            ]);
        } else {
            json_response(false, 'Restore failed: ' . implode(', ', $errors), [
                'restored' => $restored,
                'errors' => $errors
            ]);
        }
        break;
        
    case 'validate':
        // Validate translation files
        $validation_results = [];
        
        foreach ($language_files as $lang => $file_path) {
            $result = [
                'language' => $lang,
                'exists' => file_exists($file_path),
                'readable' => false,
                'writable' => false,
                'valid_json' => false,
                'size' => 0,
                'sections' => 0,
                'keys' => 0,
                'errors' => []
            ];
            
            try {
                if ($result['exists']) {
                    $result['readable'] = is_readable($file_path);
                    $result['writable'] = is_writable($file_path);
                    $result['size'] = filesize($file_path);
                    
                    if ($result['readable']) {
                        $content = file_get_contents($file_path);
                        $decoded = json_decode($content, true);
                        
                        if ($decoded !== null) {
                            $result['valid_json'] = true;
                            $result['sections'] = count($decoded);
                            
                            foreach ($decoded as $section => $keys) {
                                if (is_array($keys)) {
                                    $result['keys'] += count($keys);
                                }
                            }
                        } else {
                            $result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
                        }
                    } else {
                        $result['errors'][] = 'File not readable';
                    }
                } else {
                    $result['errors'][] = 'File does not exist';
                }
            } catch (Exception $e) {
                $result['errors'][] = 'Validation error: ' . $e->getMessage();
            }
            
            $validation_results[] = $result;
        }
        
        json_response(true, 'Validation completed', [
            'results' => $validation_results
        ]);
        break;
        
    case 'export':
        // Export translations in various formats
        $format = $data['format'] ?? 'json';
        $languages = $data['languages'] ?? ['en', 'am'];
        
        $export_data = [];
        
        foreach ($languages as $lang) {
            if (isset($language_files[$lang]) && file_exists($language_files[$lang])) {
                $content = file_get_contents($language_files[$lang]);
                $decoded = json_decode($content, true);
                if ($decoded !== null) {
                    $export_data[$lang] = $decoded;
                }
            }
        }
        
        switch ($format) {
            case 'json':
                $output = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;
                
            case 'csv':
                $csv_data = [];
                $csv_data[] = ['Section', 'Key', 'English', 'Amharic'];
                
                $en_data = $export_data['en'] ?? [];
                $am_data = $export_data['am'] ?? [];
                
                foreach ($en_data as $section => $keys) {
                    foreach ($keys as $key => $en_value) {
                        $am_value = $am_data[$section][$key] ?? '';
                        $csv_data[] = [$section, $key, $en_value, $am_value];
                    }
                }
                
                $output = '';
                foreach ($csv_data as $row) {
                    $output .= '"' . implode('","', $row) . "\"\n";
                }
                break;
                
            default:
                json_response(false, 'Unsupported export format');
        }
        
        json_response(true, 'Export completed', [
            'format' => $format,
            'data' => $output,
            'filename' => 'translations_' . date('Y-m-d_H-i-s') . '.' . $format
        ]);
        break;
        
    case 'import':
        // Import translations from uploaded data
        $import_data = $data['data'] ?? [];
        $merge_mode = $data['merge'] ?? false;
        
        if (empty($import_data)) {
            json_response(false, 'No import data provided');
        }
        
        $imported = [];
        $errors = [];
        
        foreach ($import_data as $lang => $translations_data) {
            if (!isset($language_files[$lang])) {
                $errors[] = "Unsupported language: $lang";
                continue;
            }
            
            try {
                $current_data = [];
                
                if ($merge_mode && file_exists($language_files[$lang])) {
                    $content = file_get_contents($language_files[$lang]);
                    $current_data = json_decode($content, true) ?: [];
                }
                
                // Merge or replace
                if ($merge_mode) {
                    $final_data = array_merge_recursive($current_data, $translations_data);
                } else {
                    $final_data = $translations_data;
                }
                
                // Save the data
                $json_output = json_encode($final_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
                if (file_put_contents($language_files[$lang], $json_output) !== false) {
                    $imported[] = $lang;
                } else {
                    $errors[] = "Failed to write imported data for $lang";
                }
                
            } catch (Exception $e) {
                $errors[] = "Error importing $lang: " . $e->getMessage();
            }
        }
        
        if (empty($errors)) {
            json_response(true, 'Import completed successfully', [
                'imported' => $imported,
                'merge_mode' => $merge_mode
            ]);
        } else {
            json_response(false, 'Import failed: ' . implode(', ', $errors), [
                'imported' => $imported,
                'errors' => $errors
            ]);
        }
        break;
        
    case 'stats':
        // Get translation statistics
        $stats = [];
        
        foreach ($language_files as $lang => $file_path) {
            $lang_stats = [
                'language' => $lang,
                'sections' => 0,
                'keys' => 0,
                'empty_values' => 0,
                'file_size' => 0,
                'last_modified' => null
            ];
            
            try {
                if (file_exists($file_path)) {
                    $lang_stats['file_size'] = filesize($file_path);
                    $lang_stats['last_modified'] = date('Y-m-d H:i:s', filemtime($file_path));
                    
                    $content = file_get_contents($file_path);
                    $decoded = json_decode($content, true);
                    
                    if ($decoded !== null) {
                        $lang_stats['sections'] = count($decoded);
                        
                        foreach ($decoded as $section => $keys) {
                            if (is_array($keys)) {
                                $lang_stats['keys'] += count($keys);
                                
                                foreach ($keys as $key => $value) {
                                    if (empty(trim($value))) {
                                        $lang_stats['empty_values']++;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $lang_stats['error'] = $e->getMessage();
            }
            
            $stats[] = $lang_stats;
        }
        
        json_response(true, 'Statistics retrieved successfully', [
            'stats' => $stats,
            'total_languages' => count($language_files)
        ]);
        break;
        
    case 'scan':
        // Scan codebase for translation keys
        $scan_directories = $data['directories'] ?? [
            '../../admin/',
            '../../user/',
            '../../languages/',
            '../../'
        ];
        
        $found_keys = [];
        $errors = [];
        
        try {
            foreach ($scan_directories as $dir) {
                $real_dir = realpath($dir);
                if ($real_dir && is_dir($real_dir)) {
                    $keys = scanDirectoryForTranslationKeys($real_dir);
                    $found_keys = array_merge($found_keys, $keys);
                } else {
                    $errors[] = "Directory not found: $dir";
                }
            }
            
            // Remove duplicates
            $found_keys = array_unique($found_keys);
            sort($found_keys);
            
            // Load existing translations to compare
            $existing_keys = [];
            foreach ($language_files as $lang => $file_path) {
                if (file_exists($file_path)) {
                    $content = file_get_contents($file_path);
                    $decoded = json_decode($content, true);
                    if ($decoded) {
                        $existing_keys = array_merge($existing_keys, flattenTranslationKeys($decoded, $lang));
                    }
                }
            }
            
            // Find new keys that don't exist in any language file
            $new_keys = [];
            $existing_keys_flat = array_map(function($key) {
                return explode('.', $key, 2)[1] ?? $key; // Remove language prefix
            }, $existing_keys);
            
            foreach ($found_keys as $key) {
                if (!in_array($key, $existing_keys_flat)) {
                    $new_keys[] = $key;
                }
            }
            
            json_response(true, 'Codebase scan completed', [
                'found_keys' => $found_keys,
                'total_found' => count($found_keys),
                'new_keys' => $new_keys,
                'total_new' => count($new_keys),
                'existing_keys' => count($existing_keys_flat),
                'scan_directories' => array_filter($scan_directories, function($dir) {
                    return realpath($dir) && is_dir(realpath($dir));
                }),
                'errors' => $errors
            ]);
            
        } catch (Exception $e) {
            json_response(false, 'Scan failed: ' . $e->getMessage(), [
                'errors' => [$e->getMessage()]
            ]);
        }
        break;
        
    case 'add_new_keys':
        // Add newly discovered keys to translation files
        $new_keys = $data['keys'] ?? [];
        $default_section = $data['section'] ?? 'new';
        
        if (empty($new_keys)) {
            json_response(false, 'No keys provided');
        }
        
        $added_keys = [];
        $errors = [];
        
        try {
            // Load current translations
            $current_translations = [];
            foreach ($language_files as $lang => $file_path) {
                if (file_exists($file_path)) {
                    $content = file_get_contents($file_path);
                    $current_translations[$lang] = json_decode($content, true) ?: [];
                } else {
                    $current_translations[$lang] = [];
                }
            }
            
            // Add new keys to both languages
            foreach ($new_keys as $key) {
                // Parse section and key name
                $parts = explode('.', $key, 2);
                $section = count($parts) > 1 ? $parts[0] : $default_section;
                $key_name = count($parts) > 1 ? $parts[1] : $key;
                
                // Add to both language files
                foreach (['en', 'am'] as $lang) {
                    if (!isset($current_translations[$lang][$section])) {
                        $current_translations[$lang][$section] = [];
                    }
                    
                    if (!isset($current_translations[$lang][$section][$key_name])) {
                        // Set placeholder text indicating it needs translation
                        $placeholder = ($lang === 'en') ? 
                            "Translation needed: $key" : 
                            "ትርጉም ያስፈልጋል: $key";
                        
                        $current_translations[$lang][$section][$key_name] = $placeholder;
                        $added_keys[] = "$lang.$section.$key_name";
                    }
                }
            }
            
            // Save updated translations
            $saved_files = [];
            foreach ($language_files as $lang => $file_path) {
                if (isset($current_translations[$lang])) {
                    try {
                        // Create backup first
                        if (file_exists($file_path)) {
                            $backup_path = $file_path . '.backup.' . date('Y-m-d_H-i-s');
                            copy($file_path, $backup_path);
                        }
                        
                        // Format and save JSON
                        $json_data = json_encode($current_translations[$lang], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        
                        if (file_put_contents($file_path, $json_data) !== false) {
                            $saved_files[] = $lang;
                        } else {
                            $errors[] = "Failed to save $lang file";
                        }
                    } catch (Exception $e) {
                        $errors[] = "Error saving $lang: " . $e->getMessage();
                    }
                }
            }
            
            if (empty($errors)) {
                json_response(true, 'New keys added successfully', [
                    'added_keys' => $added_keys,
                    'total_added' => count($added_keys),
                    'saved_files' => $saved_files
                ]);
            } else {
                json_response(false, 'Some keys failed to save: ' . implode(', ', $errors), [
                    'added_keys' => $added_keys,
                    'errors' => $errors
                ]);
            }
            
        } catch (Exception $e) {
            json_response(false, 'Failed to add new keys: ' . $e->getMessage());
        }
        break;
        
    default:
        json_response(false, 'Invalid action specified');
}

/**
 * Scan directory recursively for translation keys
 */
function scanDirectoryForTranslationKeys($directory) {
    $keys = [];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $file_keys = extractTranslationKeysFromFile($file->getPathname());
            $keys = array_merge($keys, $file_keys);
        }
    }
    
    return $keys;
}

/**
 * Extract translation keys from a PHP file
 */
function extractTranslationKeysFromFile($file_path) {
    $keys = [];
    
    try {
        $content = file_get_contents($file_path);
        if ($content === false) {
            return $keys;
        }
        
        // Pattern to match t('key') and t("key") calls
        $patterns = [
            "/t\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", // t('key') or t("key")
            "/\$t->get\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/" // $t->get('key')
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $key) {
                    // Clean and validate key
                    $key = trim($key);
                    if (!empty($key) && strlen($key) < 200) { // Reasonable length limit
                        $keys[] = $key;
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        error_log("Error scanning file $file_path: " . $e->getMessage());
    }
    
    return $keys;
}

/**
 * Flatten nested translation keys for comparison
 */
function flattenTranslationKeys($translations, $prefix = '') {
    $flat_keys = [];
    
    foreach ($translations as $section => $values) {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $full_key = $prefix ? "$prefix.$section.$key" : "$section.$key";
                $flat_keys[] = $full_key;
            }
        }
    }
    
    return $flat_keys;
}
?>