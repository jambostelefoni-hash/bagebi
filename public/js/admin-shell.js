(function () {
    'use strict';

    function isCompactViewport() {
        return window.matchMedia('(max-width: 991.98px)').matches;
    }

    function setMenuState(open) {
        var body = document.body;
        if (isCompactViewport()) {
            body.classList.toggle('sidebar-open', open);
            body.classList.remove('sidebar-collapse');
        } else {
            body.classList.toggle('sidebar-collapse', !open);
            body.classList.remove('sidebar-open');
        }

        document.querySelectorAll('.admin-menu-button').forEach(function (button) {
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        syncMenuButton();
    }

    function syncMenuButton() {
        var open = isCompactViewport()
            ? document.body.classList.contains('sidebar-open')
            : !document.body.classList.contains('sidebar-collapse');

        document.querySelectorAll('.admin-menu-button').forEach(function (button) {
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        var sidebar = document.getElementById('admin-sidebar');
        var backdrop = document.querySelector('.admin-sidebar-backdrop');
        if (sidebar) sidebar.inert = !open;
        if (backdrop) backdrop.hidden = !(isCompactViewport() && open);
    }

    document.addEventListener('click', function (event) {
        var menuButton = event.target.closest('.admin-menu-button');
        if (menuButton) {
            event.preventDefault();
            var body = document.body;
            var open = isCompactViewport()
                ? !body.classList.contains('sidebar-open')
                : body.classList.contains('sidebar-collapse');
            setMenuState(open);
            if (open && isCompactViewport()) {
                var firstLink = document.querySelector('#admin-sidebar a');
                if (firstLink) firstLink.focus();
            }
            return;
        }

        if (event.target.closest('.admin-sidebar-backdrop, .admin-sidebar-close')) {
            setMenuState(false);
            document.querySelector('.admin-menu-button').focus();
            return;
        }

        if (isCompactViewport() && event.target.closest('.main-sidebar a[href]:not([href="#"])')) {
            setMenuState(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && isCompactViewport() && document.body.classList.contains('sidebar-open')) {
            setMenuState(false);
            document.querySelector('.admin-menu-button').focus();
        }
        if (event.key === 'Tab' && isCompactViewport() && document.body.classList.contains('sidebar-open')) {
            var links = document.querySelectorAll('#admin-sidebar a[href], #admin-sidebar button:not([disabled])');
            if (!links.length) return;
            var first = links[0];
            var last = links[links.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    window.addEventListener('resize', function () {
        if (!isCompactViewport()) document.body.classList.remove('sidebar-open');
        syncMenuButton();
    });

    syncMenuButton();
}());
