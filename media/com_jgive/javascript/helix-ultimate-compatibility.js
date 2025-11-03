/**
 * JGive Helix Ultimate Compatibility Fix
 * Ensures campaign filters work properly with Helix Ultimate template
 * 
 * @package     JGive
 * @version     1.0.0
 * @author      Custom Fix
 */

(function($) {
    'use strict';
    
    // Early initialization to prevent undefined tjListFilters
    window.tjListFilters = window.tjListFilters || [
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
    
    // Ensure global variable is available
    if (typeof tjListFilters === 'undefined') {
        tjListFilters = window.tjListFilters;
    }
    
    // Patch the submitFilters function for better compatibility
    $(document).ready(function() {
        // Wait for jgiveCommon to be available
        var initializeFilters = function() {
            if (typeof jgiveCommon !== 'undefined' && jgiveCommon.filters) {
                var originalSubmitFilters = jgiveCommon.filters.submitFilters;
                
                jgiveCommon.filters.submitFilters = function(form) {
                    // Ensure tjListFilters is always available
                    if (typeof tjListFilters === 'undefined' || !tjListFilters.length) {
                        tjListFilters = window.tjListFilters;
                    }
                    
                    // Call original function
                    return originalSubmitFilters.call(this, form);
                };
                
                console.log('JGive Helix Ultimate compatibility fix applied');
            } else {
                // Retry after a short delay
                setTimeout(initializeFilters, 100);
            }
        };
        
        initializeFilters();
    });
    
})(jQuery);
