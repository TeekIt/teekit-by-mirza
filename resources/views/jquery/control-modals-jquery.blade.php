<script>
    window.addEventListener('close-modal', event => {
        $('#' + event.detail.id).modal('hide');
    });
</script>