{{-- Shared Alpine.js Logic for AI Office Views --}}
<script>
function aiOfficeBase() {
    return {
        showConsultationModal: false,
        userQuery: '',
        selectedTeam: 'auto',
        isSubmitting: false,
        isEvaluating: false,
        responseResult: null,

        init() {
            window.addEventListener('open-ai-consultation', (e) => {
                if (e.detail) {
                    if (e.detail.team) {
                        this.selectedTeam = e.detail.team;
                    }
                    if (e.detail.initialQuery) {
                        this.userQuery = e.detail.initialQuery;
                    }
                }
                this.openConsultationModal();
            });
        },

        openConsultationModal() {
            this.showConsultationModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        closeConsultationModal() {
            this.showConsultationModal = false;
        },

        quickAsk(text, team = null) {
            this.userQuery = text;
            if (team) {
                this.selectedTeam = team;
            }
            this.submitQuery();
        },

        async submitQuery() {
            if (!this.userQuery.trim() || this.isSubmitting) return;

            this.isSubmitting = true;
            this.responseResult = null;

            try {
                const res = await fetch("{{ route('cooca-ai.ask') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        query: this.userQuery,
                        team: this.selectedTeam
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.responseResult = data.data;
                } else {
                    alert(data.message || 'Gagal memproses permintaan.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                this.isSubmitting = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        async triggerDailyDiagnosis() {
            if (this.isEvaluating) return;

            this.isEvaluating = true;
            try {
                const res = await fetch("{{ route('cooca-ai.daily-check') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    this.responseResult = data.data;
                    this.showConsultationModal = true;
                } else {
                    alert(data.message || 'Gagal menjalankan evaluasi.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                this.isEvaluating = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        }
    };
}
</script>
