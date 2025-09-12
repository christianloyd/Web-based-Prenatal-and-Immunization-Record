{{-- Reusable Refresh Data Script Component --}}
@props([
    'contentId' => 'main-content',
    'skeletonId' => 'table-skeleton',
    'statsId' => 'stats-container',
    'statsSkeletonId' => 'stats-skeleton',
    'refreshBtnId' => 'refresh-btn',
    'hasStats' => true
])

<script>
/**
 * Reusable Refresh Data Functionality
 * Handles skeleton loading and data refresh for any page
 */

// Show skeleton loaders
function showSkeletonLoaders() {
    // Hide main content
    const mainContent = document.getElementById('{{ $contentId }}');
    if (mainContent) {
        mainContent.classList.add('hidden');
    }
    
    @if($hasStats)
    // Hide stats and show stats skeleton
    const statsContainer = document.getElementById('{{ $statsId }}');
    const statsSkeletonContainer = document.getElementById('{{ $statsSkeletonId }}');
    
    if (statsContainer) {
        statsContainer.classList.add('hidden');
    }
    if (statsSkeletonContainer) {
        statsSkeletonContainer.classList.remove('hidden');
    }
    @endif
    
    // Show table skeleton
    const tableSkeleton = document.getElementById('{{ $skeletonId }}');
    if (tableSkeleton) {
        tableSkeleton.classList.remove('hidden');
    }
}

// Hide skeleton loaders
function hideSkeletonLoaders() {
    // Show main content
    const mainContent = document.getElementById('{{ $contentId }}');
    if (mainContent) {
        mainContent.classList.remove('hidden');
    }
    
    @if($hasStats)
    // Show stats and hide stats skeleton
    const statsContainer = document.getElementById('{{ $statsId }}');
    const statsSkeletonContainer = document.getElementById('{{ $statsSkeletonId }}');
    
    if (statsContainer) {
        statsContainer.classList.remove('hidden');
    }
    if (statsSkeletonContainer) {
        statsSkeletonContainer.classList.add('hidden');
    }
    @endif
    
    // Hide table skeleton
    const tableSkeleton = document.getElementById('{{ $skeletonId }}');
    if (tableSkeleton) {
        tableSkeleton.classList.add('hidden');
    }
}

// Main refresh function with skeleton loading
function refreshDataWithSkeleton() {
    // Update refresh button state
    const refreshBtn = document.getElementById('{{ $refreshBtnId }}');
    const refreshIcon = refreshBtn ? refreshBtn.querySelector('i') : null;
    const refreshText = refreshBtn ? refreshBtn.querySelector('span') : null;
    const originalText = refreshText ? refreshText.textContent : 'Refresh Data';
    
    if (refreshBtn) {
        if (refreshIcon) {
            refreshIcon.classList.add('fa-spin');
        }
        if (refreshText) {
            refreshText.textContent = 'Refreshing...';
        }
        refreshBtn.disabled = true;
        refreshBtn.classList.add('opacity-75', 'cursor-not-allowed');
    }
    
    // Show skeleton loaders
    showSkeletonLoaders();
    
    // Get current URL with all filters and search parameters
    const currentUrl = window.location.href;
    
    // Add a small delay to show the skeleton, then reload
    setTimeout(() => {
        window.location.href = currentUrl;
    }, 800);
}

// Alternative function for immediate refresh without skeleton
function refreshData() {
    window.location.reload();
}

// Function to simulate refresh for testing (shows skeleton then hides it)
function simulateRefresh() {
    showSkeletonLoaders();
    
    // Simulate API call delay
    setTimeout(() => {
        hideSkeletonLoaders();
    }, 2000);
}

// Export functions for global use
window.refreshDataWithSkeleton = refreshDataWithSkeleton;
window.refreshData = refreshData;
window.simulateRefresh = simulateRefresh;
window.showSkeletonLoaders = showSkeletonLoaders;
window.hideSkeletonLoaders = hideSkeletonLoaders;
</script>