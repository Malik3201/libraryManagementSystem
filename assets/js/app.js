/**
 * app.js
 * Main JavaScript file for Library Management System
 * Handles mobile navigation, sidebar toggle, toast notifications, and UI interactions
 */

(function () {
    'use strict';

    /* ======================================
       Mobile Navigation Toggle
    ====================================== */
    var toggle = document.querySelector('[data-nav-toggle]');
    var links = document.querySelector('[data-nav-links]');
    if (toggle && links) {
        toggle.addEventListener('click', function () {
            links.classList.toggle('show');
        });
    }

    /* ======================================
       Dashboard Sidebar Toggle (Mobile)
    ====================================== */
    var sidebarToggle = document.querySelector('.sidebar-toggle');
    var sidebar = document.querySelector('.dashboard-sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    /* ======================================
       Toast Notifications
    ====================================== */
    // Display flash messages as toast notifications
    (function(){
        var container = document.getElementById('toast-container');
        if (!container) return;
        
        // Get flash messages from server-side (set in header.php)
        var toastData = window.__FLASH__ || [];
        
        /**
         * Create and display a toast notification
         * @param {string} type - 'success' or 'error'
         * @param {string} message - Message to display
         * @param {number} timeout - Auto-hide timeout in milliseconds
         */
        function showToast(type, message, timeout) {
            var toast = document.createElement('div');
            toast.className = 'toast ' + (type === 'error' ? 'toast-error' : 'toast-success');
            
            var msg = document.createElement('div');
            msg.className = 'toast-message';
            msg.textContent = message;
            
            var close = document.createElement('button');
            close.className = 'toast-close';
            close.innerHTML = '&times;';
            close.addEventListener('click', function() { 
                if (toast.parentNode) {
                    container.removeChild(toast); 
                }
            });
            
            toast.appendChild(msg);
            toast.appendChild(close);
            container.appendChild(toast);
            
            // Auto-hide after timeout
            setTimeout(function() { 
                if (toast.parentNode) {
                    container.removeChild(toast); 
                }
            }, timeout || 4000);
        }
        
        // Display all flash messages
        for (var i = 0; i < toastData.length; i++) {
            showToast(toastData[i].type, toastData[i].message);
        }
    })();

    /* ======================================
       Dropdown Navigation
    ====================================== */
    var dropdownToggles = document.querySelectorAll('[data-dropdown-toggle]');
    dropdownToggles.forEach(function (toggle) {
        var dropdown = toggle.closest('.nav-dropdown');
        var menu = dropdown.querySelector('[data-dropdown-menu]');
        
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close other dropdowns first
            document.querySelectorAll('.nav-dropdown').forEach(function (other) {
                if (other !== dropdown) {
                    other.classList.remove('open');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('open');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function () {
        document.querySelectorAll('.nav-dropdown').forEach(function (dropdown) {
            dropdown.classList.remove('open');
        });
    });

    /* ======================================
       Accordion (FAQ Pages)
    ====================================== */
    var accordionItems = document.querySelectorAll('.accordion-item');
    accordionItems.forEach(function (item) {
        var header = item.querySelector('.accordion-header');
        if (header) {
            header.addEventListener('click', function () {
                // Toggle the accordion item
                item.classList.toggle('open');
            });
        }
    });

})();


