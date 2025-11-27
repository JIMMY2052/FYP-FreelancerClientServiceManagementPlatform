$(() => {
    // Initiate GET request
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    // Close sidebar when clicking overlay
    $('#sidebarOverlay').on('click', function() {
        $('#sidebarToggle').prop('checked', false);
    });

    // Close sidebar when clicking a nav link on mobile
    $('.nav-item').on('click', function() {
        if (window.innerWidth <= 768) {
            $('#sidebarToggle').prop('checked', false);
        }
    });
});