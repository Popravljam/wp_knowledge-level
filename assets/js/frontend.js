/**
 * Knowledge Level - Frontend JavaScript
 * Handles tooltip display for help icons
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTooltips);
    } else {
        initTooltips();
    }
    
    function initTooltips() {
        var helpIcons = document.querySelectorAll('.kl-badge-help[data-tooltip]');
        
        helpIcons.forEach(function(icon) {
            // Create tooltip element
            var tooltip = document.createElement('div');
            tooltip.className = 'kl-tooltip';
            tooltip.textContent = icon.getAttribute('data-tooltip');
            tooltip.style.display = 'none';
            document.body.appendChild(tooltip);
            
            // Show tooltip on hover
            icon.addEventListener('mouseenter', function(e) {
                var rect = icon.getBoundingClientRect();
                tooltip.style.display = 'block';
                tooltip.style.left = (rect.left + rect.width / 2) + 'px';
                tooltip.style.top = (rect.bottom + 8) + 'px';
            });
            
            // Hide tooltip on mouse leave
            icon.addEventListener('mouseleave', function() {
                tooltip.style.display = 'none';
            });
            
            // Update position on scroll
            window.addEventListener('scroll', function() {
                if (tooltip.style.display === 'block') {
                    var rect = icon.getBoundingClientRect();
                    tooltip.style.left = (rect.left + rect.width / 2) + 'px';
                    tooltip.style.top = (rect.bottom + 8) + 'px';
                }
            });
        });
    }
})();
