<?php
/**
 * HabeshaEqub - PWA Debug Page
 * Diagnostic tool to check PWA status and service worker issues
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Debug - HabeshaEqub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .debug-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .status-good { color: #28a745; }
        .status-bad { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .log-output {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        .log-entry {
            margin-bottom: 8px;
            padding: 4px 8px;
            border-left: 3px solid #007bff;
        }
        .log-error { border-left-color: #dc3545; }
        .log-warning { border-left-color: #ffc107; }
        .log-success { border-left-color: #28a745; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4"><i class="fas fa-bug"></i> PWA Debug Center</h1>
        
        <!-- Service Worker Status -->
        <div class="debug-section">
            <h3><i class="fas fa-cog"></i> Service Worker Status</h3>
            <div id="swStatus"></div>
            <div class="mt-3">
                <button class="btn btn-primary" onclick="checkServiceWorker()">
                    <i class="fas fa-sync"></i> Refresh Status
                </button>
                <button class="btn btn-warning" onclick="unregisterServiceWorker()">
                    <i class="fas fa-trash"></i> Unregister Service Worker
                </button>
                <button class="btn btn-info" onclick="clearCache()">
                    <i class="fas fa-broom"></i> Clear Cache
                </button>
            </div>
        </div>
        
        <!-- Network Test -->
        <div class="debug-section">
            <h3><i class="fas fa-network-wired"></i> Network Tests</h3>
            <div id="networkTests"></div>
            <button class="btn btn-primary mt-2" onclick="runNetworkTests()">
                <i class="fas fa-play"></i> Run Tests
            </button>
        </div>
        
        <!-- Manifest Check -->
        <div class="debug-section">
            <h3><i class="fas fa-file-code"></i> Manifest Check</h3>
            <div id="manifestCheck"></div>
            <button class="btn btn-primary mt-2" onclick="checkManifest()">
                <i class="fas fa-search"></i> Check Manifest
            </button>
        </div>
        
        <!-- Live Logs -->
        <div class="debug-section">
            <h3><i class="fas fa-terminal"></i> Live Debug Logs</h3>
            <div class="log-output" id="debugLogs"></div>
            <button class="btn btn-secondary mt-2" onclick="clearLogs()">
                <i class="fas fa-eraser"></i> Clear Logs
            </button>
        </div>
        
        <!-- Cache Contents -->
        <div class="debug-section">
            <h3><i class="fas fa-database"></i> Cache Contents</h3>
            <div id="cacheContents"></div>
            <button class="btn btn-primary mt-2" onclick="showCacheContents()">
                <i class="fas fa-list"></i> Show Cache
            </button>
        </div>
    </div>
    
    <script>
        let logCount = 0;
        
        function log(message, type = 'info') {
            const logs = document.getElementById('debugLogs');
            const entry = document.createElement('div');
            entry.className = `log-entry log-${type}`;
            entry.innerHTML = `<strong>[${new Date().toLocaleTimeString()}]</strong> ${message}`;
            logs.appendChild(entry);
            logs.scrollTop = logs.scrollHeight;
            logCount++;
            
            // Keep only last 100 logs
            if (logCount > 100) {
                logs.removeChild(logs.firstChild);
            }
        }
        
        function clearLogs() {
            document.getElementById('debugLogs').innerHTML = '';
            logCount = 0;
        }
        
        async function checkServiceWorker() {
            log('Checking Service Worker status...', 'info');
            const statusDiv = document.getElementById('swStatus');
            
            if (!('serviceWorker' in navigator)) {
                statusDiv.innerHTML = '<div class="alert alert-danger">Service Workers not supported in this browser</div>';
                log('Service Workers not supported', 'error');
                return;
            }
            
            try {
                const registrations = await navigator.serviceWorker.getRegistrations();
                log(`Found ${registrations.length} service worker registration(s)`, 'info');
                
                if (registrations.length === 0) {
                    statusDiv.innerHTML = '<div class="alert alert-warning">No service worker registered</div>';
                    log('No service worker registered', 'warning');
                    return;
                }
                
                let html = '<div class="alert alert-success">Service Worker Found!</div><ul class="list-group mt-3">';
                
                for (const reg of registrations) {
                    const state = reg.active ? reg.active.state : (reg.waiting ? reg.waiting.state : 'not active');
                    const scope = reg.scope;
                    const scriptURL = reg.active ? reg.active.scriptURL : (reg.waiting ? reg.waiting.scriptURL : 'N/A');
                    
                    html += `
                        <li class="list-group-item">
                            <strong>State:</strong> <span class="status-${state === 'activated' ? 'good' : 'warning'}">${state}</span><br>
                            <strong>Scope:</strong> ${scope}<br>
                            <strong>Script:</strong> ${scriptURL}<br>
                            <strong>Controlling:</strong> ${navigator.serviceWorker.controller ? 'Yes' : 'No'}
                        </li>
                    `;
                    
                    log(`SW State: ${state}, Scope: ${scope}`, state === 'activated' ? 'success' : 'warning');
                }
                
                html += '</ul>';
                statusDiv.innerHTML = html;
                
            } catch (error) {
                log(`Error checking service worker: ${error.message}`, 'error');
                statusDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            }
        }
        
        async function unregisterServiceWorker() {
            if (!confirm('Are you sure you want to unregister all service workers?')) return;
            
            log('Unregistering service workers...', 'warning');
            try {
                const registrations = await navigator.serviceWorker.getRegistrations();
                for (const reg of registrations) {
                    await reg.unregister();
                    log(`Unregistered: ${reg.scope}`, 'success');
                }
                alert('Service workers unregistered. Please reload the page.');
                window.location.reload();
            } catch (error) {
                log(`Error unregistering: ${error.message}`, 'error');
                alert('Error: ' + error.message);
            }
        }
        
        async function clearCache() {
            if (!confirm('Clear all caches? This will remove offline content.')) return;
            
            log('Clearing caches...', 'warning');
            try {
                const cacheNames = await caches.keys();
                for (const name of cacheNames) {
                    await caches.delete(name);
                    log(`Deleted cache: ${name}`, 'success');
                }
                alert('Caches cleared. Please reload the page.');
                window.location.reload();
            } catch (error) {
                log(`Error clearing cache: ${error.message}`, 'error');
                alert('Error: ' + error.message);
            }
        }
        
        async function runNetworkTests() {
            log('Running network tests...', 'info');
            const testsDiv = document.getElementById('networkTests');
            testsDiv.innerHTML = '<div class="spinner-border" role="status"></div>';
            
            const tests = [
                { name: 'Homepage', url: '/' },
                { name: 'Index.php', url: '/index.php' },
                { name: 'Manifest', url: '/manifest.json' },
                { name: 'Service Worker', url: '/service-worker.js' },
                { name: 'User Dashboard', url: '/user/dashboard.php' },
                { name: 'Admin Dashboard', url: '/admin/welcome_admin.php' }
            ];
            
            let html = '<div class="list-group">';
            
            for (const test of tests) {
                try {
                    const startTime = Date.now();
                    const response = await fetch(test.url, { method: 'HEAD', cache: 'no-store' });
                    const duration = Date.now() - startTime;
                    const status = response.status;
                    const statusClass = status >= 200 && status < 300 ? 'success' : (status >= 400 ? 'danger' : 'warning');
                    
                    html += `
                        <div class="list-group-item">
                            <strong>${test.name}:</strong> 
                            <span class="status-${statusClass}">${status}</span> 
                            (${duration}ms) 
                            <code>${test.url}</code>
                        </div>
                    `;
                    
                    log(`${test.name}: ${status} (${duration}ms)`, statusClass);
                } catch (error) {
                    html += `
                        <div class="list-group-item">
                            <strong>${test.name}:</strong> 
                            <span class="status-bad">FAILED</span> 
                            <code>${test.url}</code>
                            <br><small class="text-danger">${error.message}</small>
                        </div>
                    `;
                    log(`${test.name}: FAILED - ${error.message}`, 'error');
                }
            }
            
            html += '</div>';
            testsDiv.innerHTML = html;
        }
        
        async function checkManifest() {
            log('Checking manifest...', 'info');
            const manifestDiv = document.getElementById('manifestCheck');
            manifestDiv.innerHTML = '<div class="spinner-border" role="status"></div>';
            
            try {
                const response = await fetch('/manifest.json', { cache: 'no-store' });
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const manifest = await response.json();
                log('Manifest loaded successfully', 'success');
                
                let html = '<div class="alert alert-success">Manifest is valid!</div>';
                html += '<pre class="bg-light p-3 rounded"><code>' + JSON.stringify(manifest, null, 2) + '</code></pre>';
                
                // Check for potential issues
                html += '<h5 class="mt-3">Checks:</h5><ul class="list-group">';
                
                if (manifest.start_url === '/') {
                    html += '<li class="list-group-item list-group-item-warning"><i class="fas fa-exclamation-triangle"></i> start_url is "/" - might cause issues. Consider "/index.php"</li>';
                    log('Warning: start_url is "/"', 'warning');
                } else {
                    html += '<li class="list-group-item list-group-item-success"><i class="fas fa-check"></i> start_url is valid</li>';
                }
                
                if (!manifest.icons || manifest.icons.length === 0) {
                    html += '<li class="list-group-item list-group-item-danger"><i class="fas fa-times"></i> No icons defined</li>';
                    log('Error: No icons in manifest', 'error');
                } else {
                    html += `<li class="list-group-item list-group-item-success"><i class="fas fa-check"></i> ${manifest.icons.length} icon(s) defined</li>`;
                }
                
                html += '</ul>';
                manifestDiv.innerHTML = html;
                
            } catch (error) {
                log(`Manifest check failed: ${error.message}`, 'error');
                manifestDiv.innerHTML = `<div class="alert alert-danger">Failed to load manifest: ${error.message}</div>`;
            }
        }
        
        async function showCacheContents() {
            log('Fetching cache contents...', 'info');
            const cacheDiv = document.getElementById('cacheContents');
            cacheDiv.innerHTML = '<div class="spinner-border" role="status"></div>';
            
            try {
                const cacheNames = await caches.keys();
                if (cacheNames.length === 0) {
                    cacheDiv.innerHTML = '<div class="alert alert-info">No caches found</div>';
                    log('No caches found', 'info');
                    return;
                }
                
                let html = '<div class="list-group">';
                
                for (const cacheName of cacheNames) {
                    const cache = await caches.open(cacheName);
                    const keys = await cache.keys();
                    
                    html += `
                        <div class="list-group-item">
                            <strong>${cacheName}</strong> (${keys.length} items)
                            <ul class="mt-2">
                    `;
                    
                    for (const key of keys.slice(0, 20)) { // Show first 20
                        html += `<li><code>${key.url}</code></li>`;
                    }
                    
                    if (keys.length > 20) {
                        html += `<li><em>... and ${keys.length - 20} more</em></li>`;
                    }
                    
                    html += '</ul></div>';
                    log(`Cache "${cacheName}": ${keys.length} items`, 'info');
                }
                
                html += '</div>';
                cacheDiv.innerHTML = html;
                
            } catch (error) {
                log(`Error fetching cache: ${error.message}`, 'error');
                cacheDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            }
        }
        
        // Auto-run checks on load
        window.addEventListener('load', () => {
            log('PWA Debug Page Loaded', 'success');
            checkServiceWorker();
            checkManifest();
        });
        
        // Listen for service worker messages
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                log(`SW Message: ${JSON.stringify(event.data)}`, 'info');
            });
            
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                log('Service Worker controller changed', 'warning');
                checkServiceWorker();
            });
        }
    </script>
</body>
</html>

