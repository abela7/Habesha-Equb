<?php
/**
 * HabeshaEqub Admin Translation Management
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
    </style>
</body>
</html>