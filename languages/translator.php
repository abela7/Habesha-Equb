<?php
/**
 * HabeshaEqub Translation System
 * Simple JSON-based translation with caching
 */

class Translator {
    private static $instance = null;
    private $currentLanguage = 'am';
    private $translations = [];
    private $fallbackLanguage = 'am';
    
    private function __construct() {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            @session_start(); // Suppress warnings in case headers already sent
        }
        
        // Get language from session or set default to Amharic
        $this->currentLanguage = $_SESSION['app_language'] ?? 'am';
        error_log("Translator: Initialized with language: {$this->currentLanguage} (from session: " . ($_SESSION['app_language'] ?? 'not set') . ")");
        $this->loadTranslations();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load translations for current language
     */
    private function loadTranslations() {
        // Since translator.php is IN the languages directory, just use __DIR__
        $langFile = __DIR__ . '/' . $this->currentLanguage . '.json';
        
        error_log("Translator: Current DIR: " . __DIR__);
        error_log("Translator: Loading translations from: $langFile");
        error_log("Translator: File exists: " . (file_exists($langFile) ? 'YES' : 'NO'));
        
        if (file_exists($langFile)) {
            $content = file_get_contents($langFile);
            error_log("Translator: File content length: " . strlen($content));
            
            $this->translations = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Translation JSON error for {$this->currentLanguage}: " . json_last_error_msg());
                $this->loadFallback();
            } else {
                error_log("Translator: Successfully loaded " . count($this->translations) . " translation groups for {$this->currentLanguage}");
                if (isset($this->translations['dashboard'])) {
                    error_log("Translator: Dashboard section loaded with " . count($this->translations['dashboard']) . " keys");
                } else {
                    error_log("Translator: NO dashboard section found!");
                    error_log("Translator: Available sections: " . implode(', ', array_keys($this->translations)));
                }
            }
        } else {
            error_log("Translation file not found: {$langFile}");
            error_log("Translator: Files in dir: " . implode(', ', scandir(__DIR__)));
            $this->loadFallback();
        }
    }
    
    /**
     * Load fallback language (Amharic)
     */
    private function loadFallback() {
        if ($this->currentLanguage !== $this->fallbackLanguage) {
            $fallbackFile = __DIR__ . '/' . $this->fallbackLanguage . '.json';
            error_log("Translator: Loading fallback from: $fallbackFile");
            
            if (file_exists($fallbackFile)) {
                $content = file_get_contents($fallbackFile);
                $this->translations = json_decode($content, true);
                error_log("Translator: Fallback loaded successfully");
            } else {
                error_log("Translator: Fallback file not found: $fallbackFile");
            }
        } else {
            error_log("Translator: Cannot load fallback - current language IS fallback language");
        }
    }
    
    /**
     * Get translation by key (dot notation supported)
     * Example: t('rules.page_title') or t('common.save')
     */
    public function translate($key, $params = []) {
        // Debug: Check if translations are loaded
        if (empty($this->translations)) {
            error_log("Translator: No translations loaded! Current language: {$this->currentLanguage}");
            return $key;
        }
        
        $keys = explode('.', $key);
        $value = $this->translations;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                // Return key if translation not found
                error_log("Translation key not found: {$key} for language: {$this->currentLanguage}. Available keys at this level: " . implode(', ', array_keys($value)));
                return $key;
            }
        }
        
        // Replace parameters if provided
        if (!empty($params) && is_string($value)) {
            foreach ($params as $param => $replacement) {
                $value = str_replace('{' . $param . '}', $replacement, $value);
            }
        }
        
        return $value;
    }
    
    /**
     * Set current language
     */
    public function setLanguage($language) {
        if (in_array($language, ['en', 'am'])) {
            $this->currentLanguage = $language;
            $_SESSION['app_language'] = $language;
            
            // Force reload translations for new language
            $this->translations = [];
            $this->loadTranslations();
            
            error_log("Translator::setLanguage: Set language to '$language', session now: " . $_SESSION['app_language']);
            error_log("Translator::setLanguage: Loaded " . count($this->translations) . " translation groups");
            return true;
        }
        error_log("Translator::setLanguage: Invalid language '$language'");
        return false;
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage() {
        return $this->currentLanguage;
    }
    
    /**
     * Get available languages
     */
    public function getAvailableLanguages() {
        return [
            'en' => 'English',
            'am' => 'አማርኛ'
        ];
    }
    
    /**
     * Check if current language is RTL
     */
    public function isRTL() {
        return false; // Amharic is LTR, but you can modify this if needed
    }
}

/**
 * Global translation function - shorthand for Translator::translate()
 */
function t($key, $params = []) {
    // Force fresh instance check for debugging
    $instance = Translator::getInstance();
    $result = $instance->translate($key, $params);
    
    // Additional debug for specific keys that are failing
    if ($result === $key && strpos($key, 'dashboard.') === 0) {
        error_log("TRANSLATION FAILED for $key - returning key instead");
    }
    
    return $result;
}

/**
 * Get current language
 */
function getCurrentLanguage() {
    return Translator::getInstance()->getCurrentLanguage();
}

/**
 * Set language
 */
function setLanguage($language) {
    return Translator::getInstance()->setLanguage($language);
}

/**
 * Get available languages
 */
function getAvailableLanguages() {
    return Translator::getInstance()->getAvailableLanguages();
}
?> 