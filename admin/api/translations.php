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
        
    default:
        json_response(false, 'Invalid action specified');
}
?>