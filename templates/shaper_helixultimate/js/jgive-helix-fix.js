/**
 * JGive Helix Ultimate Template Compatibility Fix
 * Fixes campaign filter reset issue when using Helix Ultimate template
 * 
 * @package     JGive
 * @author      Custom Fix for Helix Ultimate
 * @copyright   2025
 */

// Ensure tjListFilters is properly initialized for Helix Ultimate
(function($) {
    'use strict';
    
    // Initialize tjListFilters array if not already defined
    if (typeof window.tjListFilters === 'undefined') {
        window.tjListFilters = [
            'filter_campaigns_to_show',
            'filter_campaign_countries', 
            'filter_campaign_states',
            'filter_campaign_city',
            'filter_org_ind_type',
            'filter_org_ind_type_my',
            'filter_campaign_type',
            'filter_search',
            'filter_order',
            'filter_order_Dir',
            'limit',
            'limitstart',
            'filter_campaign_cat'
        ];
    }
    
    // Ensure global tjListFilters variable is available
    if (typeof tjListFilters === 'undefined') {
        tjListFilters = window.tjListFilters;
    }
    
    // Override the submitFilters function to ensure compatibility
    if (typeof jgiveCommon !== 'undefined' && jgiveCommon.filters) {
        var originalSubmitFilters = jgiveCommon.filters.submitFilters;
        
        jgiveCommon.filters.submitFilters = function(form) {
            // Ensure tjListFilters is available before calling original function
            if (typeof tjListFilters === 'undefined') {
                tjListFilters = window.tjListFilters || [
                    'filter_campaigns_to_show',
                    'filter_campaign_countries', 
                    'filter_campaign_states',
                    'filter_campaign_city',
                    'filter_org_ind_type',
                    'filter_org_ind_type_my',
                    'filter_campaign_type',
                    'filter_search',
                    'filter_order',
                    'filter_order_Dir',
                    'limit',
                    'limitstart',
                    'filter_campaign_cat'
                ];
            }
            
            // Call the original function
            return originalSubmitFilters.call(this, form);
        };
    }
    
    // Document ready handler for Helix Ultimate
    $(document).ready(function() {
        // Double-check initialization after DOM is ready
        if (typeof tjListFilters === 'undefined') {
            window.tjListFilters = [
                'filter_campaigns_to_show',
                'filter_campaign_countries', 
                'filter_campaign_states',
                'filter_campaign_city',
                'filter_org_ind_type',
                'filter_org_ind_type_my',
                'filter_campaign_type',
                'filter_search',
                'filter_order',
                'filter_order_Dir',
                'limit',
                'limitstart',
                'filter_campaign_cat'
            ];
            tjListFilters = window.tjListFilters;
        }
        
        // Add event listeners for filter forms if they exist
        var filterForms = ['jgVerticalCoreFilters', 'jgHorizontalCoreFilters', 'adminForm4', 'adminForm', 'campaignFilterform'];
        
        filterForms.forEach(function(formId) {
            var form = document.getElementById(formId);
            if (form) {
                // Ensure form has proper event handling
                $(form).find('input, select').on('change', function() {
                    // Small delay to ensure all values are updated
                    setTimeout(function() {
                        if (typeof jgiveCommon !== 'undefined' && jgiveCommon.filters && jgiveCommon.filters.submitFilters) {
                            jgiveCommon.filters.submitFilters(formId);
                        }
                    }, 100);
                });
            }
        });
    });
    
})(jQuery);
