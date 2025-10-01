/*
 * Purpose: Site interactions (mobile nav toggle, accordion, dropdowns)
 */

(function () {
	'use strict';

	// Mobile nav toggle
	var toggle = document.querySelector('[data-nav-toggle]');
	var links = document.querySelector('[data-nav-links]');
	if (toggle && links) {
		toggle.addEventListener('click', function () {
			links.classList.toggle('show');
		});
	}

	// Dashboard sidebar toggle for mobile
	var sidebarToggle = document.querySelector('.sidebar-toggle');
	var sidebar = document.querySelector('.dashboard-sidebar');
	if (sidebarToggle && sidebar) {
		sidebarToggle.addEventListener('click', function () {
			sidebar.classList.toggle('open');
		});
	}

	// Dropdown navigation
	var dropdownToggles = document.querySelectorAll('[data-dropdown-toggle]');
	dropdownToggles.forEach(function (toggle) {
		var dropdown = toggle.closest('.nav-dropdown');
		var menu = dropdown.querySelector('[data-dropdown-menu]');
		
		toggle.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			
			// Close other dropdowns
			document.querySelectorAll('.nav-dropdown').forEach(function (other) {
				if (other !== dropdown) {
					other.classList.remove('open');
				}
			});
			
			dropdown.classList.toggle('open');
		});
	});

	// Close dropdowns when clicking outside
	document.addEventListener('click', function () {
		document.querySelectorAll('.nav-dropdown').forEach(function (dropdown) {
			dropdown.classList.remove('open');
		});
	});

	// Accordion
	var acc = document.querySelectorAll('.accordion-item');
	acc.forEach(function (item) {
		var header = item.querySelector('.accordion-header');
		if (header) {
			header.addEventListener('click', function () {
				item.classList.toggle('open');
			});
		}
	});
})();


