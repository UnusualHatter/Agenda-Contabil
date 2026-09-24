{{-- Runs before the stylesheet so the page never flashes the wrong theme. --}}
<script>
    (() => {
        const stored = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const dark = stored === 'dark' || (stored === null && prefersDark);

        document.documentElement.dataset.theme = dark ? 'dark' : 'light';
    })();
</script>
