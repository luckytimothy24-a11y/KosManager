
<?php $__env->startPush('scripts'); ?>
<script>
window.chatPoll = function (pollUrl, latestId, draftKey) {
    return {
        latestId: latestId,
        fresh: 0,
        timer: null,
        pollUrl: pollUrl,
        draftKey: draftKey,
        start() {
            const ta = document.getElementById('message');
            if (ta) {
                const saved = sessionStorage.getItem(this.draftKey);
                if (saved) {
                    ta.value = saved;
                    sessionStorage.removeItem(this.draftKey);
                }
            }
            this.timer = setInterval(() => this.poll(), 15000);
        },
        pollError: '',
        async poll() {
            try {
                const res = await fetch(this.pollUrl + '?since=' + this.latestId, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) {
                    if (res.status === 403 || res.status === 404) {
                        this.pollError = res.status === 403 ? 'Akses chat tidak valid untuk kos ini.' : 'Kos tidak ditemukan.';
                        if (this.timer) { clearInterval(this.timer); this.timer = null; }
                    }
                    return;
                }
                const json = await res.json();
                const n = (json && json.data && json.data.new_count) ? json.data.new_count : 0;
                if (n > 0) {
                    this.fresh = n;
                }
                const latest = (json && json.data && json.data.latest_id) ? json.data.latest_id : 0;
                if (latest) this.latestId = latest;
            } catch (e) {
            }
        },
        reload() {
            const ta = document.getElementById('message');
            if (ta) {
                sessionStorage.setItem(this.draftKey, ta.value);
            }
            window.location.reload();
        },
    };
};
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\components\chat-poll-script.blade.php ENDPATH**/ ?>