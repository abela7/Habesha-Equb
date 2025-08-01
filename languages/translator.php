<?php
/**
 * HabeshaEqub Translation System - ENHANCED
 * Supports deferred initialization to allow language selection before loading translations.
 */

class Translator {
    private static $instance = null;
    private $currentLanguage = 'am';
    private $translations = [];
    private $fallbackLanguage = 'am';
    private $initialized = false;
    
    // The constructor is now private and does not automatically load translations.
    private function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initializes the translator by setting the language and loading the corresponding file.
     * This method must be called before using any translation functions.
     */
    public function init() {
        if ($this->initialized) {
            return;
        }
        
        // Use language from session if available, otherwise default to Amharic.
        $this->currentLanguage = $_SESSION['app_language'] ?? $this->fallbackLanguage;
        $this->loadTranslations();
        $this->initialized = true;
    }
    
    private function loadTranslations() {
        $langFile = __DIR__ . '/' . $this->currentLanguage . '.json';
        
        if (file_exists($langFile)) {
            $content = file_get_contents($langFile);
            $this->translations = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Translation JSON error for {$this->currentLanguage}: " . json_last_error_msg());
                $this->loadFallback();
            }
        } else {
            error_log("Translation file not found: {$langFile}");
            $this->loadFallback();
        }
    }
    
    private function loadFallback() {
        if ($this->currentLanguage !== $this->fallbackLanguage) {
            $fallbackFile = __DIR__ . '/' . $this->fallbackLanguage . '.json';
            if (file_exists($fallbackFile)) {
                $content = file_get_contents($fallbackFile);
                $this->translations = json_decode($content, true);
            }
        }
    }
    
    public function translate($key, $params = []) {
        if (!$this->initialized) {
            $this->init(); // Auto-initialize if not done, for safety.
        }

        $keys = explode('.', $key);
        $value = $this->translations;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                error_log("Translation key not found: '{$key}' for language: {$this->currentLanguage}");
                return $key;
            }
        }
        
        if (!empty($params) && is_string($value)) {
            foreach ($params as $param => $replacement) {
                $value = str_replace('{' . $param . '}', $replacement, $value);
            }
        }
        
        return $value;
    }
    
    public function setLanguage($language) {
        if (in_array($language, ['en', 'am'])) {
            $this->currentLanguage = $language;
            $_SESSION['app_language'] = $language;
            $this->loadTranslations();
            $this->initialized = true; // Mark as initialized after setting language
            return true;
        }
        return false;
    }
    
    public function getCurrentLanguage() {
        if (!$this->initialized) {
            $this->init();
        }
        return $this->currentLanguage;
    }
    
    public function getAvailableLanguages() {
        return [
            'en' => 'English',
            'am' => 'አማርኛ'
        ];
    }
    
    public function isRTL() {
        return false;
    }
}

/**
 * Global helper functions
 */
function t($key, $params = []) {
    return Translator::getInstance()->translate($key, $params);
}

function getCurrentLanguage() {
    return Translator::getInstance()->getCurrentLanguage();
}

function setLanguage($language) {
    return Translator::getInstance()->setLanguage($language);
}

function getAvailableLanguages() {
    return Translator::getInstance()->getAvailableLanguages();
}

// Global accessor to initialize the translator.
// This ensures that the translator is ready before any output is generated.
Translator::getInstance()->init();
