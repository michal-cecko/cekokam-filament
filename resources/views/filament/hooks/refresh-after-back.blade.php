{{-- SPA mode would show a saved copy of the page on Back and Forward, with old data. Load the page fresh instead. --}}
<script>
    document.addEventListener('livewire:navigate', (event) => {
        if (event.detail.history) {
            event.preventDefault();
            window.location.reload();
        }
    });
</script>
