<?php
/**
 * Habesha-Equb Admin Translation Management
 * Direct editing of language files (en.json and am.json)
 */

require_once 'includes/admin_auth_guard.php';

// Get current admin info
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Define language file paths
$language_files = [
    'en' => '../languages/en.json',
    'am' => '../languages/am.json'
];

// Load current translations
$translations = [];
foreach ($language_files as $lang => $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $translations[$lang] = json_decode($content, true);
    } else {
        $translations[$lang] = [];
    }
}

// Include language handler for UI
require_once '../languages/translator.php';
$t = Translator::getInstance();
?>
<!DOCTYPE html>
<html lang="<?php echo $t->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translation Management - HabeshaEqub Admin</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Meta tags -->
    <meta name="description" content="HabeshaEqub Translation Management">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <div class="header-content">
                <div class="header-text">
                    <h1>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M19 22s-3-3-3-6 3-6 3-6"/>
                            <path d="M16 12s3 3 3 6-3 6-3 6"/>
                        </svg>
                        Translation Management
                    </h1>
                    <p>Manage English and Amharic translations for the entire system</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="backupTranslations()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7,10 12,15 17,10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Backup Files
                    </button>
                    <a href="settings.php" class="btn btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H6m13 0l-4 4m4-4l-4-4"/>
                        </svg>
                        Back to Settings
                    </a>
                </div>
            </div>
        </div>

        <!-- Language Tabs -->
        <div class="language-tabs">
            <button class="tab-btn active" data-lang="en" onclick="switchLanguage('en')">
                <span class="flag">🇬🇧</span>
                English
                <span class="count" id="en-count">0</span>
            </button>
            <button class="tab-btn" data-lang="am" onclick="switchLanguage('am')">
                <span class="flag">🇪🇹</span>
                አማርኛ (Amharic)
                <span class="count" id="am-count">0</span>
            </button>
        </div>

        <!-- Translation Editor -->
        <div class="translation-editor">
            
            <!-- Toolbar -->
            <div class="editor-toolbar">
                <div class="toolbar-left">
                    <button class="btn btn-sm btn-primary" onclick="addNewSection()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add Section
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="addNewKey()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Add Key
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="scanForNewKeys()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Scan Codebase
                    </button>
                    <button class="btn btn-sm btn-outline" onclick="expandAll()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6,9 12,15 18,9"/>
                        </svg>
                        Expand All
                    </button>
                    <button class="btn btn-sm btn-outline" onclick="collapseAll()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="18,15 12,9 6,15"/>
                        </svg>
                        Collapse All
                    </button>
                </div>
                
                <div class="toolbar-right">
                    <div class="search-box">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input type="text" id="searchKeys" placeholder="Search keys..." onkeyup="searchTranslations()">
                    </div>
                    <button class="btn btn-sm btn-success" onclick="saveTranslations()" id="saveBtn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>

            <!-- Translation Tree -->
            <div class="translation-tree" id="translationTree">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>

        <!-- Statistics Panel -->
        <div class="stats-panel">
            <div class="stat-card">
                <h4>Translation Stats</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-label">Total Sections</span>
                        <span class="stat-value" id="totalSections">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Keys</span>
                        <span class="stat-value" id="totalKeys">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Missing Translations</span>
                        <span class="stat-value" id="missingTranslations">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Completion</span>
                        <span class="stat-value" id="completionRate">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div id="addSectionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Section</h3>
                <span class="close" onclick="closeModal('addSectionModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="sectionName">Section Name</label>
                    <input type="text" id="sectionName" class="form-control" placeholder="e.g. dashboard, members, payments">
                    <small>Use lowercase letters and underscores only</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('addSectionModal')">Cancel</button>
                <button class="btn btn-primary" onclick="createSection()">Create Section</button>
            </div>
        </div>
    </div>

    <!-- Add Key Modal -->
    <div id="addKeyModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Translation Key</h3>
                <span class="close" onclick="closeModal('addKeyModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="keySection">Section</label>
                    <select id="keySection" class="form-control">
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="keyName">Key Name</label>
                    <input type="text" id="keyName" class="form-control" placeholder="e.g. page_title, welcome_message">
                    <small>Use lowercase letters and underscores only</small>
                </div>
                <div class="form-group">
                    <label for="keyEnglish">English Text</label>
                    <input type="text" id="keyEnglish" class="form-control" placeholder="Enter English translation">
                </div>
                <div class="form-group">
                    <label for="keyAmharic">Amharic Text</label>
                    <input type="text" id="keyAmharic" class="form-control" placeholder="Enter Amharic translation">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('addKeyModal')">Cancel</button>
                <button class="btn btn-primary" onclick="createKey()">Add Key</button>
            </div>
        </div>
    </div>

    <!-- Scan Results Modal -->
    <div id="scanResultsModal" class="modal" style="display: none;">
        <div class="modal-content scan-modal">
            <div class="modal-header">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Codebase Scan Results
                </h3>
                <span class="close" onclick="closeModal('scanResultsModal')">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Scan Summary -->
                <div class="scan-summary">
                    <div class="summary-grid">
                        <div class="summary-item">
                            <span class="summary-label">Total Found</span>
                            <span class="summary-value" id="totalFoundKeys">0</span>
                        </div>
                        <div class="summary-item new">
                            <span class="summary-label">New Keys</span>
                            <span class="summary-value" id="newKeysCount">0</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Already Exist</span>
                            <span class="summary-value" id="existingKeysCount">0</span>
                        </div>
                    </div>
                </div>

                <!-- New Keys List -->
                <div class="new-keys-section" id="newKeysSection" style="display: none;">
                    <h4>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        New Translation Keys Found
                    </h4>
                    <p class="section-description">These translation keys were found in your code but don't exist in your language files yet.</p>
                    
                    <!-- Section Selection -->
                    <div class="form-group">
                        <label for="newKeysSection">Add to Section</label>
                        <select id="newKeysSectionSelect" class="form-control">
                            <option value="new">New (create new section)</option>
                        </select>
                    </div>

                    <!-- Keys List -->
                    <div class="new-keys-list" id="newKeysList">
                        <!-- Dynamic content -->
                    </div>
                </div>

                <!-- No New Keys Message -->
                <div class="no-new-keys" id="noNewKeysMessage" style="display: none;">
                    <div class="success-message">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                        <h4>All Keys Are Up to Date!</h4>
                        <p>No new translation keys were found. All your t() calls are already defined in the language files.</p>
                    </div>
                </div>

                <!-- Scan Progress -->
                <div class="scan-progress" id="scanProgress" style="display: none;">
                    <div class="progress-content">
                        <div class="spinner"></div>
                        <h4>Scanning Codebase...</h4>
                        <p>Searching for translation keys in PHP files...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('scanResultsModal')">Close</button>
                <button class="btn btn-primary" id="addNewKeysBtn" onclick="addNewKeysToTranslations()" style="display: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Selected Keys
                </button>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="messageContainer" class="message-container"></div>

    <script>
        // Global variables
        let currentLanguage = 'en';
        let translations = <?php echo json_encode($translations); ?>;
        let unsavedChanges = false;

        // Initialize the editor
        document.addEventListener('DOMContentLoaded', function() {
            loadTranslationTree();
            updateStats();
            updateCounts();
            populateSectionSelect();
        });

        // Switch between languages
        function switchLanguage(lang) {
            currentLanguage = lang;
            
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-lang="${lang}"]`).classList.add('active');
            
            // Reload tree
            loadTranslationTree();
        }

        // Load translation tree
        function loadTranslationTree() {
            const tree = document.getElementById('translationTree');
            const data = translations[currentLanguage] || {};
            
            tree.innerHTML = '';
            
            Object.keys(data).forEach(section => {
                const sectionEl = createSectionElement(section, data[section]);
                tree.appendChild(sectionEl);
            });
            
            updateStats();
        }

        // Create section element
        function createSectionElement(sectionName, sectionData) {
            const section = document.createElement('div');
            section.className = 'translation-section';
            section.innerHTML = `
                <div class="section-header" onclick="toggleSection('${sectionName}')">
                    <div class="section-title">
                        <svg class="section-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,18 15,12 9,6"/>
                        </svg>
                        <span class="section-name">${sectionName}</span>
                        <span class="section-count">${Object.keys(sectionData).length} keys</span>
                    </div>
                    <div class="section-actions">
                        <button class="btn-icon" onclick="event.stopPropagation(); deleteSection('${sectionName}')" title="Delete Section">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3,6 5,6 21,6"/>
                                <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="section-content" id="section-${sectionName}">
                    ${createKeysHTML(sectionName, sectionData)}
                </div>
            `;
            
            return section;
        }

        // Create keys HTML
        function createKeysHTML(sectionName, sectionData) {
            let html = '';
            
            Object.keys(sectionData).forEach(key => {
                const value = sectionData[key];
                const keyId = `${sectionName}.${key}`;
                
                html += `
                    <div class="translation-key" data-key="${keyId}">
                        <div class="key-header">
                            <span class="key-name">${key}</span>
                            <button class="btn-icon" onclick="deleteKey('${sectionName}', '${key}')" title="Delete Key">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                        <div class="key-input">
                            <textarea 
                                class="translation-input" 
                                onchange="updateTranslation('${sectionName}', '${key}', this.value)"
                                oninput="markUnsaved()"
                                placeholder="Enter translation..."
                            >${escapeHtml(value)}</textarea>
                        </div>
                    </div>
                `;
            });
            
            return html;
        }

        // Toggle section expand/collapse
        function toggleSection(sectionName) {
            const content = document.getElementById(`section-${sectionName}`);
            const icon = content.parentElement.querySelector('.section-icon');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.style.transform = 'rotate(90deg)';
            } else {
                content.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Update translation
        function updateTranslation(section, key, value) {
            if (!translations[currentLanguage]) {
                translations[currentLanguage] = {};
            }
            if (!translations[currentLanguage][section]) {
                translations[currentLanguage][section] = {};
            }
            
            translations[currentLanguage][section][key] = value;
            markUnsaved();
            updateStats();
        }

        // Save translations
        async function saveTranslations() {
            const saveBtn = document.getElementById('saveBtn');
            const originalText = saveBtn.innerHTML;
            
            // Show loading
            saveBtn.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                </svg>
                Saving...
            `;
            saveBtn.disabled = true;
            
            try {
                const response = await fetch('api/translations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'save',
                        translations: translations
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    unsavedChanges = false;
                    showMessage('Translations saved successfully!', 'success');
                } else {
                    showMessage('Failed to save translations: ' + result.message, 'error');
                }
            } catch (error) {
                showMessage('Error saving translations: ' + error.message, 'error');
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        // Show message
        function showMessage(message, type) {
            const container = document.getElementById('messageContainer');
            const messageEl = document.createElement('div');
            messageEl.className = `message message-${type}`;
            messageEl.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentElement.remove()">×</button>
            `;
            
            container.appendChild(messageEl);
            
            setTimeout(() => {
                messageEl.remove();
            }, 5000);
        }

        // Mark as unsaved
        function markUnsaved() {
            unsavedChanges = true;
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.classList.add('btn-warning');
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function expandAll() {
            document.querySelectorAll('.section-content').forEach(content => {
                content.style.display = 'block';
                content.parentElement.querySelector('.section-icon').style.transform = 'rotate(90deg)';
            });
        }

        function collapseAll() {
            document.querySelectorAll('.section-content').forEach(content => {
                content.style.display = 'none';
                content.parentElement.querySelector('.section-icon').style.transform = 'rotate(0deg)';
            });
        }

        function searchTranslations() {
            const query = document.getElementById('searchKeys').value.toLowerCase();
            const keys = document.querySelectorAll('.translation-key');
            
            keys.forEach(key => {
                const keyName = key.dataset.key.toLowerCase();
                const text = key.querySelector('.translation-input').value.toLowerCase();
                
                if (keyName.includes(query) || text.includes(query)) {
                    key.style.display = 'block';
                } else {
                    key.style.display = 'none';
                }
            });
        }

        function updateStats() {
            const data = translations[currentLanguage] || {};
            let totalSections = Object.keys(data).length;
            let totalKeys = 0;
            let missingTranslations = 0;
            
            Object.keys(data).forEach(section => {
                const sectionKeys = Object.keys(data[section]);
                totalKeys += sectionKeys.length;
                
                sectionKeys.forEach(key => {
                    if (!data[section][key] || data[section][key].trim() === '') {
                        missingTranslations++;
                    }
                });
            });
            
            const completionRate = totalKeys > 0 ? Math.round(((totalKeys - missingTranslations) / totalKeys) * 100) : 100;
            
            document.getElementById('totalSections').textContent = totalSections;
            document.getElementById('totalKeys').textContent = totalKeys;
            document.getElementById('missingTranslations').textContent = missingTranslations;
            document.getElementById('completionRate').textContent = completionRate + '%';
        }

        function updateCounts() {
            const enData = translations.en || {};
            const amData = translations.am || {};
            
            let enCount = 0;
            let amCount = 0;
            
            Object.keys(enData).forEach(section => {
                enCount += Object.keys(enData[section]).length;
            });
            
            Object.keys(amData).forEach(section => {
                amCount += Object.keys(amData[section]).length;
            });
            
            document.getElementById('en-count').textContent = enCount;
            document.getElementById('am-count').textContent = amCount;
        }

        // Modal functions
        function addNewSection() {
            document.getElementById('addSectionModal').style.display = 'block';
        }

        function addNewKey() {
            populateSectionSelect();
            document.getElementById('addKeyModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function populateSectionSelect() {
            const select = document.getElementById('keySection');
            const data = translations[currentLanguage] || {};
            
            select.innerHTML = '';
            Object.keys(data).forEach(section => {
                const option = document.createElement('option');
                option.value = section;
                option.textContent = section;
                select.appendChild(option);
            });
        }

        function createSection() {
            const sectionName = document.getElementById('sectionName').value.trim();
            
            if (!sectionName) {
                showMessage('Please enter a section name', 'error');
                return;
            }
            
            if (!translations[currentLanguage]) {
                translations[currentLanguage] = {};
            }
            
            if (translations[currentLanguage][sectionName]) {
                showMessage('Section already exists', 'error');
                return;
            }
            
            translations[currentLanguage][sectionName] = {};
            loadTranslationTree();
            updateStats();
            updateCounts();
            markUnsaved();
            closeModal('addSectionModal');
            document.getElementById('sectionName').value = '';
            showMessage('Section created successfully', 'success');
        }

        function createKey() {
            const section = document.getElementById('keySection').value;
            const keyName = document.getElementById('keyName').value.trim();
            const englishText = document.getElementById('keyEnglish').value.trim();
            const amharicText = document.getElementById('keyAmharic').value.trim();
            
            if (!section || !keyName) {
                showMessage('Please fill in all required fields', 'error');
                return;
            }
            
            // Add to both languages
            if (!translations.en[section]) translations.en[section] = {};
            if (!translations.am[section]) translations.am[section] = {};
            
            translations.en[section][keyName] = englishText;
            translations.am[section][keyName] = amharicText;
            
            loadTranslationTree();
            updateStats();
            updateCounts();
            markUnsaved();
            closeModal('addKeyModal');
            
            // Clear form
            document.getElementById('keyName').value = '';
            document.getElementById('keyEnglish').value = '';
            document.getElementById('keyAmharic').value = '';
            
            showMessage('Translation key added successfully', 'success');
        }

        function deleteSection(sectionName) {
            if (confirm(`Are you sure you want to delete the "${sectionName}" section? This will remove all keys in this section.`)) {
                delete translations[currentLanguage][sectionName];
                loadTranslationTree();
                updateStats();
                updateCounts();
                markUnsaved();
                showMessage('Section deleted successfully', 'success');
            }
        }

        function deleteKey(section, key) {
            if (confirm(`Are you sure you want to delete the "${key}" key?`)) {
                delete translations[currentLanguage][section][key];
                loadTranslationTree();
                updateStats();
                updateCounts();
                markUnsaved();
                showMessage('Translation key deleted successfully', 'success');
            }
        }

        function backupTranslations() {
            const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
            const backup = {
                timestamp: timestamp,
                translations: translations
            };
            
            const blob = new Blob([JSON.stringify(backup, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `translations-backup-${timestamp}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            showMessage('Backup downloaded successfully', 'success');
        }

        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (unsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // === SCANNING FUNCTIONALITY ===
        
        // Global variable to store scan results
        let scanResults = null;
        
        // Scan for new translation keys
        async function scanForNewKeys() {
            const modal = document.getElementById('scanResultsModal');
            const progressDiv = document.getElementById('scanProgress');
            const summaryDiv = document.querySelector('.scan-summary');
            const newKeysSection = document.getElementById('newKeysSection');
            const noNewKeysDiv = document.getElementById('noNewKeysMessage');
            
            // Show modal and progress
            modal.style.display = 'block';
            progressDiv.style.display = 'block';
            summaryDiv.style.display = 'none';
            newKeysSection.style.display = 'none';
            noNewKeysDiv.style.display = 'none';
            
            try {
                const response = await fetch('api/translations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'scan'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    scanResults = result.data;
                    displayScanResults(result.data);
                } else {
                    showMessage('Scan failed: ' + result.message, 'error');
                    closeModal('scanResultsModal');
                }
            } catch (error) {
                console.error('Scan error:', error);
                showMessage('Scan failed: ' + error.message, 'error');
                closeModal('scanResultsModal');
            } finally {
                progressDiv.style.display = 'none';
            }
        }
        
        // Display scan results
        function displayScanResults(data) {
            const summaryDiv = document.querySelector('.scan-summary');
            const newKeysSection = document.getElementById('newKeysSection');
            const noNewKeysDiv = document.getElementById('noNewKeysMessage');
            const addBtn = document.getElementById('addNewKeysBtn');
            
            // Update summary
            document.getElementById('totalFoundKeys').textContent = data.total_found;
            document.getElementById('newKeysCount').textContent = data.total_new;
            document.getElementById('existingKeysCount').textContent = data.existing_keys;
            
            summaryDiv.style.display = 'block';
            
            if (data.total_new > 0) {
                // Show new keys section
                newKeysSection.style.display = 'block';
                noNewKeysDiv.style.display = 'none';
                addBtn.style.display = 'inline-flex';
                
                // Populate section select
                populateNewKeysSectionSelect();
                
                // Display new keys
                displayNewKeysList(data.new_keys);
            } else {
                // No new keys found
                newKeysSection.style.display = 'none';
                noNewKeysDiv.style.display = 'block';
                addBtn.style.display = 'none';
            }
        }
        
        // Populate section select for new keys
        function populateNewKeysSectionSelect() {
            const select = document.getElementById('newKeysSectionSelect');
            const data = translations[currentLanguage] || {};
            
            // Clear existing options except "new"
            select.innerHTML = '<option value="new">New (create new section)</option>';
            
            // Add existing sections
            Object.keys(data).forEach(section => {
                const option = document.createElement('option');
                option.value = section;
                option.textContent = section.charAt(0).toUpperCase() + section.slice(1);
                select.appendChild(option);
            });
        }
        
        // Display new keys list with checkboxes
        function displayNewKeysList(newKeys) {
            const container = document.getElementById('newKeysList');
            
            container.innerHTML = newKeys.map((key, index) => {
                // Try to suggest a section based on the key name
                const suggestedSection = suggestSectionForKey(key);
                
                return `
                    <div class="new-key-item">
                        <div class="key-checkbox">
                            <input type="checkbox" id="newKey_${index}" value="${key}" checked>
                            <label for="newKey_${index}" class="key-label">
                                <span class="key-name">${key}</span>
                                ${suggestedSection ? `<span class="suggested-section">→ ${suggestedSection}</span>` : ''}
                            </label>
                        </div>
                        <div class="key-actions">
                            <button class="btn-icon" onclick="previewKey('${key}')" title="Preview in context">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Add select all / deselect all
            const selectAllHtml = `
                <div class="select-all-controls">
                    <button class="btn btn-sm btn-outline" onclick="selectAllNewKeys(true)">Select All</button>
                    <button class="btn btn-sm btn-outline" onclick="selectAllNewKeys(false)">Deselect All</button>
                    <span class="selected-count" id="selectedKeysCount">${newKeys.length} of ${newKeys.length} selected</span>
                </div>
            `;
            
            container.insertAdjacentHTML('afterbegin', selectAllHtml);
            
            // Add event listeners to checkboxes
            container.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });
            
            updateSelectedCount();
        }
        
        // Suggest section based on key name
        function suggestSectionForKey(key) {
            const keyLower = key.toLowerCase();
            
            // Common patterns
            if (keyLower.includes('dashboard')) return 'dashboard';
            if (keyLower.includes('member')) return 'members';
            if (keyLower.includes('payment')) return 'payments';
            if (keyLower.includes('payout')) return 'payouts';
            if (keyLower.includes('report')) return 'reports';
            if (keyLower.includes('auth') || keyLower.includes('login') || keyLower.includes('register')) return 'user_auth';
            if (keyLower.includes('profile')) return 'profile';
            if (keyLower.includes('navigation') || keyLower.includes('nav')) return 'navigation';
            if (keyLower.includes('common') || keyLower.includes('button') || keyLower.includes('form')) return 'common';
            if (keyLower.includes('error')) return 'errors';
            
            return null;
        }
        
        // Select/deselect all new keys
        function selectAllNewKeys(selectAll) {
            const checkboxes = document.querySelectorAll('#newKeysList input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll;
            });
            updateSelectedCount();
        }
        
        // Update selected count
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('#newKeysList input[type="checkbox"]');
            const selected = Array.from(checkboxes).filter(cb => cb.checked).length;
            const total = checkboxes.length;
            
            const countElement = document.getElementById('selectedKeysCount');
            if (countElement) {
                countElement.textContent = `${selected} of ${total} selected`;
            }
            
            // Enable/disable add button
            const addBtn = document.getElementById('addNewKeysBtn');
            if (addBtn) {
                addBtn.disabled = selected === 0;
                if (selected === 0) {
                    addBtn.classList.add('btn-disabled');
                } else {
                    addBtn.classList.remove('btn-disabled');
                }
            }
        }
        
        // Add selected new keys to translations
        async function addNewKeysToTranslations() {
            const selectedKeys = Array.from(document.querySelectorAll('#newKeysList input[type="checkbox"]:checked'))
                .map(cb => cb.value);
            
            if (selectedKeys.length === 0) {
                showMessage('No keys selected', 'error');
                return;
            }
            
            const targetSection = document.getElementById('newKeysSectionSelect').value;
            const addBtn = document.getElementById('addNewKeysBtn');
            const originalText = addBtn.innerHTML;
            
            // Show loading
            addBtn.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                </svg>
                Adding...
            `;
            addBtn.disabled = true;
            
            try {
                const response = await fetch('api/translations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_new_keys',
                        keys: selectedKeys,
                        section: targetSection
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(`${result.data.total_added} keys added successfully!`, 'success');
                    
                    // Reload translations
                    await reloadTranslations();
                    
                    // Close modal
                    closeModal('scanResultsModal');
                    
                    // Switch to the section where keys were added if it's a new section
                    if (targetSection === 'new') {
                        markUnsaved();
                    }
                } else {
                    showMessage('Failed to add keys: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Add keys error:', error);
                showMessage('Failed to add keys: ' + error.message, 'error');
            } finally {
                addBtn.innerHTML = originalText;
                addBtn.disabled = false;
            }
        }
        
        // Reload translations from server
        async function reloadTranslations() {
            try {
                const response = await fetch('api/translations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'load'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    translations = result.data.translations;
                    loadTranslationTree();
                    updateStats();
                    updateCounts();
                    populateSectionSelect();
                }
            } catch (error) {
                console.error('Failed to reload translations:', error);
            }
        }
        
        // Preview key in context (placeholder for future feature)
        function previewKey(key) {
            showMessage(`Preview feature coming soon for: ${key}`, 'info');
        }
    </script>

    <!-- Styles -->
    <style>
        /* Translation Editor Styles */
        .language-tabs {
            display: flex;
            margin: 1rem 0;
            border-bottom: 2px solid #e5e7eb;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: var(--color-teal);
            background: #f9fafb;
        }

        .tab-btn.active {
            color: var(--color-teal);
            border-bottom-color: var(--color-teal);
            background: #f0fdfa;
        }

        .flag {
            font-size: 1.2em;
        }

        .count {
            background: #e5e7eb;
            color: #374151;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .tab-btn.active .count {
            background: var(--color-teal);
            color: white;
        }

        .translation-editor {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .editor-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            flex-wrap: wrap;
            gap: 12px;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box svg {
            position: absolute;
            left: 12px;
            color: #9ca3af;
        }

        .search-box input {
            padding: 8px 12px 8px 36px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            width: 200px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--color-teal);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-outline {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }

        .translation-tree {
            max-height: 70vh;
            overflow-y: auto;
            padding: 20px;
        }

        .translation-section {
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            background: #f9fafb;
            padding: 12px 16px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-header:hover {
            background: #f3f4f6;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #374151;
        }

        .section-icon {
            transition: transform 0.3s ease;
        }

        .section-count {
            background: #e5e7eb;
            color: #6b7280;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .section-actions {
            display: flex;
            gap: 4px;
        }

        .btn-icon {
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            border-radius: 4px;
            color: #6b7280;
            transition: all 0.3s ease;
        }

        .btn-icon:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .section-content {
            padding: 16px;
            background: white;
        }

        .translation-key {
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .key-header {
            background: #f9fafb;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .key-input {
            padding: 8px 12px;
        }

        .translation-input {
            width: 100%;
            border: none;
            outline: none;
            resize: vertical;
            min-height: 40px;
            font-family: inherit;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        .stats-panel {
            margin-top: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card h4 {
            margin: 0 0 16px 0;
            color: #374151;
            font-size: 1.125rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }

        .stat-item {
            text-align: center;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .stat-label {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #1f2937;
        }

        .close {
            color: #9ca3af;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #374151;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: right;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .form-group small {
            color: #6b7280;
            font-size: 0.75rem;
            margin-top: 4px;
            display: block;
        }

        /* Message Styles */
        .message-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }

        .message {
            background: white;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 300px;
        }

        .message-success {
            border-left-color: #10b981;
            background: #ecfdf5;
            color: #065f46;
        }

        .message-error {
            border-left-color: #ef4444;
            background: #fef2f2;
            color: #991b1b;
        }

        .message button {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        }

        .message button:hover {
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .editor-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-left,
            .toolbar-right {
                justify-content: center;
            }

            .search-box input {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .translation-tree {
                max-height: 60vh;
            }
        }

        /* === SCANNING MODAL STYLES === */
        .scan-modal .modal-content {
            max-width: 700px;
            width: 95%;
        }

        .scan-summary {
            margin-bottom: 24px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .summary-item {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid transparent;
        }

        .summary-item.new {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .summary-label {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .summary-value {
            display: block;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
        }

        .summary-item.new .summary-value {
            color: #d97706;
        }

        .new-keys-section {
            margin-top: 24px;
        }

        .new-keys-section h4 {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 8px 0;
            color: #1f2937;
            font-size: 1.125rem;
        }

        .section-description {
            color: #6b7280;
            margin-bottom: 20px;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .select-all-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .selected-count {
            margin-left: auto;
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }

        .new-keys-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
        }

        .new-key-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s ease;
        }

        .new-key-item:last-child {
            border-bottom: none;
        }

        .new-key-item:hover {
            background: #f9fafb;
        }

        .key-checkbox {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .key-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--color-teal);
        }

        .key-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin: 0;
            flex: 1;
        }

        .key-name {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875rem;
            color: #1f2937;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .suggested-section {
            font-size: 0.75rem;
            color: #059669;
            font-weight: 500;
            background: #d1fae5;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .key-actions {
            display: flex;
            gap: 4px;
        }

        .no-new-keys {
            text-align: center;
            padding: 40px 20px;
        }

        .success-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .success-message svg {
            color: #10b981;
        }

        .success-message h4 {
            margin: 0;
            color: #1f2937;
            font-size: 1.25rem;
        }

        .success-message p {
            margin: 0;
            color: #6b7280;
            max-width: 400px;
            line-height: 1.5;
        }

        .scan-progress {
            text-align: center;
            padding: 60px 20px;
        }

        .progress-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid var(--color-teal);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .scan-progress h4 {
            margin: 0;
            color: #1f2937;
            font-size: 1.25rem;
        }

        .scan-progress p {
            margin: 0;
            color: #6b7280;
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Mobile responsive for scan modal */
        @media (max-width: 768px) {
            .scan-modal .modal-content {
                width: 98%;
                margin: 2% auto;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .select-all-controls {
                flex-wrap: wrap;
                gap: 8px;
            }

            .selected-count {
                margin-left: 0;
                width: 100%;
                text-align: center;
            }

            .new-key-item {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .key-checkbox {
                justify-content: flex-start;
            }

            .key-actions {
                justify-content: flex-end;
            }
        }
    </style>
</body>
</html>