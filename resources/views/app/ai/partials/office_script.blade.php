@php
    $activeBusiness = \App\Support\Context::business();
    $hasConfiguredProvider = $activeBusiness ? $activeBusiness->aiProviderConfigs()
        ->where('is_active', true)
        ->whereNotNull('api_key')
        ->where('api_key', '!=', '')
        ->exists() : false;
    $businessId = $activeBusiness ? $activeBusiness->id : 'default';
@endphp

{{-- Shared Alpine.js Logic for AI Office Views with Interactive Chat --}}
<script>
function aiOfficeBase() {
    return {
        showConsultationModal: false,
        hasConfiguredProvider: @json($hasConfiguredProvider),
        businessId: @json($businessId),
        activeChatAgent: 'cfo',
        chatInput: '',
        isSendingChatMessage: false,
        isEvaluating: false,
        agentChatHistories: {},

        promptPresets: {
            // Executives
            cfo: [
                { icon: 'bar-chart-3', label: 'Profit & Cashflow', query: 'Bagaimana estimasi profit & cashflow bulan ini?' },
                { icon: 'package', label: 'Stok Kritis', query: 'Cek status inventaris dan stok kritis di gudang' },
                { icon: 'target', label: 'Promo Flash Sale', query: 'Buat ide promo flash sale untuk dongkrak omzet' },
                { icon: 'zap', label: 'Audit Harian', query: 'Kasih report lengkap keuangan dan audit transaksi hari ini' }
            ],
            ceo: [
                { icon: 'building', label: 'Executive Summary', query: 'Berikan ringkasan eksekutif performa dan kesehatan bisnis secara menyeluruh' },
                { icon: 'target', label: 'Target Strategis', query: 'Bagaimana progres pencapaian target bisnis dan profitabilitas bulan ini?' },
                { icon: 'alert-triangle', label: 'Deteksi Risiko', query: 'Apakah ada anomali atau risiko operasional lintas divisi hari ini?' },
                { icon: 'lightbulb', label: 'Arahan Prioritas', query: 'Rekomendasikan langkah prioritas untuk memaksimalkan omzet minggu ini' }
            ],
            coo: [
                { icon: 'package', label: 'Stok Kritis', query: 'Cek status inventaris dan stok kritis di gudang' },
                { icon: 'file-text', label: 'Ajukan Purchase', query: 'Buatkan usulan purchase order bahan baku yang menipis ke supplier' },
                { icon: 'truck', label: 'SLA Pemenuhan', query: 'Bagaimana SLA pemenuhan pesanan dan logistik hari ini?' },
                { icon: 'refresh-cw', label: 'Sinkronisasi Stok', query: 'Periksa sinkronisasi stok toko fisik dan online' }
            ],
            cmo: [
                { icon: 'target', label: 'Promo Flash Sale', query: 'Buat ide promo flash sale untuk dongkrak omzet' },
                { icon: 'megaphone', label: 'Performa Iklan', query: 'Bagaimana performa kampanye iklan dan CTR saat ini?' },
                { icon: 'users', label: 'Lead Prospek', query: 'Analisis pertambahan pelanggan baru dan lead prospek' },
                { icon: 'sparkles', label: 'Ide Kampanye', query: 'Rekomendasikan strategi marketing omnichannel untuk akhir pekan' }
            ],
            sales_director: [
                { icon: 'trending-up', label: 'Performa Penjualan', query: 'Bagaimana ringkasan kinerja omzet dan tren penjualan minggu ini?' },
                { icon: 'package-check', label: 'Produk Terlaris', query: 'Produk apa yang memberikan kontribusi omzet terbesar saat ini?' },
                { icon: 'user-check', label: 'Pelanggan Kunci', query: 'Tinjau daftar pelanggan dengan kontribusi nilai belanja tertinggi' },
                { icon: 'target', label: 'Strategi Pipeline', query: 'Rekomendasikan taktik mempercepat siklus penutupan transaksi pelanggan' }
            ],
            hr_lead: [
                { icon: 'users', label: 'Rekap Presensi', query: 'Bagaimana rekapitulasi kehadiran staf hari ini?' },
                { icon: 'clock', label: 'Disiplin Shift', query: 'Evaluasi kepatuhan jam kerja dan shift karyawan toko' },
                { icon: 'award', label: 'Produktivitas', query: 'Bagaimana perbandingan produktivitas antar shift kasir?' },
                { icon: 'calendar', label: 'Alokasi Roster', query: 'Tinjau kecukupan tenaga kerja untuk operasional akhir pekan' }
            ],

            // Operations & Sales Specialists
            business: [
                { icon: 'shield-alert', label: 'Deteksi Anomali', query: 'Periksa apakah ada anomali atau indikasi transaksi mencurigakan hari ini' },
                { icon: 'bar-chart-2', label: 'Kesehatan Finansial', query: 'Analisis margin keuntungan dan rasio beban bisnis terkini' },
                { icon: 'trending-up', label: 'Performa Penjualan', query: 'Ringkaskan performa transaksi dan produk terlaris hari ini' },
                { icon: 'layers', label: 'Kondisi Stok', query: 'Bagaimana kondisi menyeluruh rantai pasok dan inventaris toko?' }
            ],
            sales: [
                { icon: 'trending-up', label: 'Ringkasan Penjualan', query: 'Bagaimana performa penjualan dan produk terlaris hari ini?' },
                { icon: 'file-text', label: 'Draf Invoice', query: 'Bantu siapkan draf invoice untuk pesanan pelanggan terbaru' },
                { icon: 'bar-chart', label: 'Tren Omzet', query: 'Analisis tren penjualan harian dibandingkan periode sebelumnya' },
                { icon: 'tag', label: 'Produk Favorit', query: 'Tampilkan produk dengan perputaran stok paling cepat hari ini' }
            ],
            customer: [
                { icon: 'users', label: 'Profil Pelanggan', query: 'Berikan ringkasan data pelanggan aktif dan segmen pembeli terbesar' },
                { icon: 'repeat', label: 'Retensi Pembeli', query: 'Bagaimana tingkat pembelian ulang (repeat order) pelanggan toko kita?' },
                { icon: 'heart', label: 'Pelanggan Loyal', query: 'Identifikasi pelanggan yang paling sering bertransaksi bulan ini' },
                { icon: 'user-x', label: 'Pelanggan Pasif', query: 'Daftar pelanggan yang sudah lebih dari 30 hari tidak bertransaksi' }
            ],
            inventory: [
                { icon: 'package', label: 'Stok Kritis', query: 'Tampilkan daftar produk yang stoknya sudah di bawah batas minimum' },
                { icon: 'alert-circle', label: 'Barang Mati', query: 'Apakah ada stok yang lambat bergerak (slow-moving) di gudang?' },
                { icon: 'check-square', label: 'Status Stok', query: 'Berapa total nilai inventaris dan ketersediaan stok fisik saat ini?' },
                { icon: 'truck', label: 'Buffer Gudang', query: 'Rekomendasi jumlah stok pengaman untuk produk unggulan' }
            ],
            purchasing: [
                { icon: 'file-plus', label: 'Usulan PO Supplier', query: 'Buatkan usulan purchase order (PO) untuk barang-barang yang stoknya menipis' },
                { icon: 'package-search', label: 'Cek Stok Menipis', query: 'Periksa stok produk apa saja yang mendesak untuk segera dipesan' },
                { icon: 'clock', label: 'Lead Time Vendor', query: 'Evaluasi perkiraan waktu pengiriman restock dari supplier utama' },
                { icon: 'dollar-sign', label: 'Estimasi Biaya PO', query: 'Hitung estimasi modal belanja restock berdasarkan kebutuhan gudang' }
            ],
            marketplace: [
                { icon: 'shopping-bag', label: 'Penjualan Saluran', query: 'Bagaimana ringkasan performa penjualan dari saluran toko online dan marketplace?' },
                { icon: 'refresh-cw', label: 'Sinkronisasi Stok', query: 'Periksa apakah stok di channel online sinkron dengan stok fisik toko' },
                { icon: 'tag', label: 'Top Produk Online', query: 'Produk apa yang paling laris di channel online minggu ini?' },
                { icon: 'alert-triangle', label: 'Stok Habis Online', query: 'Cek apakah ada produk populer yang habis di etalase marketplace' }
            ],

            // Analytics, Content & People Specialists
            finance: [
                { icon: 'dollar-sign', label: 'Kesehatan Finansial', query: 'Bagaimana kondisi arus kas, profit, dan beban operasional bulan ini?' },
                { icon: 'shield-check', label: 'Audit Transaksi', query: 'Lakukan audit transaksi harian dan cek apakah ada selisih kas fisik kasir' },
                { icon: 'pie-chart', label: 'Margin Produk', query: 'Analisis margin keuntungan kotor dari produk-produk unggulan' },
                { icon: 'credit-card', label: 'Pencairan Kas', query: 'Evaluasi jadwal kewajiban tagihan dan kesiapan likuiditas' }
            ],
            reporting: [
                { icon: 'file-bar-chart', label: 'Laporan Penjualan', query: 'Susun laporan ringkasan penjualan komprehensif untuk periode ini' },
                { icon: 'trending-up', label: 'Tren Omzet', query: 'Bagaimana perbandingan tren omzet minggu ini dibanding minggu lalu?' },
                { icon: 'award', label: 'Top Kontributor', query: 'Produk dan kategori mana yang menyumbang pendapatan terbesar?' },
                { icon: 'briefcase', label: 'Laporan Finansial', query: 'Sajikan ringkasan kesehatan finansial bisnis untuk pemilik toko' }
            ],
            marketing: [
                { icon: 'send', label: 'Proposal Kampanye', query: 'Buatkan draf proposal kampanye promosi untuk mendongkrak penjualan minggu depan' },
                { icon: 'users', label: 'Target Audiens', query: 'Siapa target audiens pembeli yang paling potensial untuk promosi ini?' },
                { icon: 'percent', label: 'Skema Diskon', query: 'Rekomendasikan struktur diskon atau bundling yang tetap menguntungkan margin' },
                { icon: 'sparkles', label: 'Ide Promosi Baru', query: 'Berikan 3 ide aktivasi promo kreatif untuk pelanggan setia' }
            ],
            content: [
                { icon: 'feather', label: 'Draf Post Promo', query: 'Buatkan salinan copywriting postingan media sosial untuk produk terlaris kita' },
                { icon: 'file-text', label: 'Deskripsi Produk', query: 'Tuliskan deskripsi katalog produk yang menarik untuk etalase digital' },
                { icon: 'tag', label: 'Headline Iklan', query: 'Buat 5 variasi headline iklan yang memikat calon pembeli baru' },
                { icon: 'message-square', label: 'Template Chat CS', query: 'Susun template balasan ramah untuk admin CS toko saat menjawab promo' }
            ],
            social_media: [
                { icon: 'share-2', label: 'Jadwal Konten', query: 'Rekomendasikan jadwal dan kalender posting media sosial minggu ini' },
                { icon: 'feather', label: 'Copywriting Story', query: 'Buatkan naskah story Instagram/WhatsApp interaktif untuk promo hari ini' },
                { icon: 'heart', label: 'Tingkatkan Interaksi', query: 'Bagaimana cara meningkatkan engagement dan interaksi audiens toko kita?' },
                { icon: 'trending-up', label: 'Tren Tagar', query: 'Identifikasi topik dan gaya bahasa yang relevan untuk target pembeli UMKM' }
            ],
            hr: [
                { icon: 'users', label: 'Rekap Presensi', query: 'Bagaimana rekapitulasi kehadiran staf dan catatan keterlambatan hari ini?' },
                { icon: 'clock', label: 'Kedisiplinan Shift', query: 'Periksa kepatuhan jam kerja dan waktu buka-tutup shift kasir' },
                { icon: 'briefcase', label: 'Kinerja Kasir', query: 'Bagaimana akurasi kas dan performa register kasir hari ini?' },
                { icon: 'check-circle-2', label: 'Kesiapan Tim', query: 'Berapa total staf aktif yang terdaftar di sistem toko kita?' }
            ]
        },

        agentDefaultGreetings: {
            // Executives
            cfo: 'Halo Pak! Executive Command Center siap menerima instruksi finansial dan evaluasi cashflow bisnis Anda.',
            ceo: 'Halo Pak Direktur! Ruang kerja eksekutif siap. Apa prioritas atau evaluasi strategis bisnis kita hari ini?',
            coo: 'Siap, Pak! Tim Operasional siap memantau ketersediaan stok gudang, pengadaan supplier, dan SLA pesanan.',
            cmo: 'Halo Pak! Studio Marketing siap merumuskan kampanye promosi, diskon omnichannel, dan strategi pertumbuhan omzet.',
            sales_director: 'Halo Pak! Ruang Direktur Penjualan siap menganalisis pipeline transaksi, target tim sales, dan pelanggan bernilai tinggi.',
            hr_lead: 'Halo Pak! Pimpinan SDM siap mendiskusikan kepatuhan kerja tim, struktur shift, dan kesejahteraan staf.',

            // Operations & Sales Specialists
            business: 'Halo Pak! AI Business Analyst siap mendeteksi anomali transaksi, fraud kasir, dan kesehatan margin bisnis.',
            sales: 'Halo Pak! AI Sales Specialist siap meninjau transaksi kasir, produk paling laris, dan target harian.',
            customer: 'Halo Pak! AI Customer Care siap memantau kepuasan pelanggan, keluhan toko, dan segmentasi pembeli setia.',
            inventory: 'Halo Pak! AI Inventory Specialist siap memeriksa kartu stok, buffer persediaan, dan potensi barang macet.',
            purchasing: 'Halo Pak! AI Purchasing Specialist siap menyusun draf Purchase Order supplier dan memantau restock.',
            marketplace: 'Halo Pak! AI Marketplace Specialist siap menyelaraskan katalog produk, sinkronisasi stok, dan pesanan multichannel.',

            // Analytics, Content & People Specialists
            finance: 'Halo Pak! AI Finance Specialist siap memeriksa rekonsiliasi kas fisik, piutang tertunggak, dan audit pembukuan.',
            reporting: 'Halo Pak! AI Reporting Specialist siap mengompilasi laporan laba rugi, ringkasan eksekutif, dan tren omzet.',
            marketing: 'Halo Pak! AI Marketing Specialist siap merancang proposal kampanye iklan terarah dan optimasi konversi.',
            content: 'Halo Pak! AI Content Specialist siap menyusun draf konten katalog produk, deskripsi promo, dan copy visual.',
            social_media: 'Halo Pak! AI Social Media Specialist siap membedah tren media sosial, engagement audiens, dan jadwal tayang.',
            hr: 'Halo Pak! AI HR Specialist siap merekapitulasi presensi karyawan hari ini dan kepatuhan shift kasir.'
        },

        init() {
            this.loadChatHistoriesFromStorage();

            window.addEventListener('open-ai-consultation', (e) => {
                if (!this.hasConfiguredProvider) {
                    window.location.href = "{{ route('cooca-ai.providers') }}";
                    return;
                }

                if (e.detail) {
                    if (e.detail.agent) {
                        this.activeChatAgent = e.detail.agent;
                    }
                    if (e.detail.initialQuery || e.detail.initialMessage) {
                        this.chatInput = e.detail.initialQuery || e.detail.initialMessage;
                    }
                }
                this.openConsultationModal();
            });
        },

        get currentChatMessages() {
            const role = this.activeChatAgent || 'cfo';
            if (!this.agentChatHistories[role] || this.agentChatHistories[role].length === 0) {
                this.agentChatHistories[role] = [{
                    sender: 'ai',
                    text: this.agentDefaultGreetings[role] || this.agentDefaultGreetings.cfo,
                    time: 'Baru saja'
                }];
                this.saveChatHistoriesToStorage();
            }
            return this.agentChatHistories[role];
        },

        get activeQuickPrompts() {
            const role = this.activeChatAgent || 'cfo';
            return this.promptPresets[role] || this.promptPresets.cfo;
        },

        loadChatHistoriesFromStorage() {
            try {
                const stored = localStorage.getItem('cooca_chat_history_' + this.businessId);
                if (stored) {
                    this.agentChatHistories = JSON.parse(stored) || {};
                }
            } catch (err) {
                this.agentChatHistories = {};
            }
        },

        saveChatHistoriesToStorage() {
            try {
                localStorage.setItem('cooca_chat_history_' + this.businessId, JSON.stringify(this.agentChatHistories));
            } catch (err) {
                // Storage full or disabled
            }
        },

        openConsultationModal(targetAgent = null) {
            if (!this.hasConfiguredProvider) {
                window.location.href = "{{ route('cooca-ai.providers') }}";
                return;
            }

            if (targetAgent) {
                this.activeChatAgent = targetAgent;
            }
            this.showConsultationModal = true;
            this.$nextTick(() => {
                this.scrollChatToBottom();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        closeConsultationModal() {
            this.showConsultationModal = false;
        },

        switchAgent(role) {
            this.activeChatAgent = role;
            if (!this.agentChatHistories[role] || this.agentChatHistories[role].length === 0) {
                this.agentChatHistories[role] = [{
                    sender: 'ai',
                    text: this.agentDefaultGreetings[role] || this.agentDefaultGreetings.cfo,
                    time: 'Baru saja'
                }];
                this.saveChatHistoriesToStorage();
            }
            this.$nextTick(() => {
                this.scrollChatToBottom();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        clickQuickPrompt(prompt) {
            if (!prompt || !prompt.query) return;
            this.sendChatMessage(prompt.query);
        },

        async sendChatMessage(explicitText = null) {
            const text = (explicitText !== null ? explicitText : this.chatInput).trim();
            if (!text || this.isSendingChatMessage) return;

            if (!this.hasConfiguredProvider) {
                window.location.href = "{{ route('cooca-ai.providers') }}";
                return;
            }

            const role = this.activeChatAgent || 'cfo';
            if (!this.agentChatHistories[role]) {
                this.agentChatHistories[role] = [];
            }

            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.');

            // 1. Append User Message
            this.agentChatHistories[role].push({
                sender: 'user',
                text: text,
                time: timeStr
            });
            this.saveChatHistoriesToStorage();

            if (explicitText === null) {
                this.chatInput = '';
            }

            this.isSendingChatMessage = true;
            this.$nextTick(() => {
                this.scrollChatToBottom();
                if (window.lucide) window.lucide.createIcons();
            });

            try {
                const res = await fetch("{{ route('cooca-ai.ask') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        query: text,
                        agent: role
                    })
                });

                const data = await res.json();

                if (data.needs_provider) {
                    this.agentChatHistories[role].push({
                        sender: 'ai',
                        text: 'Konfigurasi AI Provider diperlukan. Silakan hubungkan API Key Anda terlebih dahulu.',
                        time: timeStr,
                        needs_provider: true
                    });
                    this.saveChatHistoriesToStorage();
                    setTimeout(() => {
                        window.location.href = data.redirect_url || "{{ route('cooca-ai.providers') }}";
                    }, 1200);
                    return;
                }

                if (data.success && data.data) {
                    const aiReply = data.data.summary || data.data.raw || 'Analisis operasional diselesaikan.';
                    this.agentChatHistories[role].push({
                        sender: 'ai',
                        text: aiReply,
                        time: timeStr
                    });
                    this.saveChatHistoriesToStorage();
                } else {
                    this.agentChatHistories[role].push({
                        sender: 'ai',
                        text: data.message || 'Mohon maaf, terjadi kendala saat memproses instruksi bisnis.',
                        time: timeStr
                    });
                    this.saveChatHistoriesToStorage();
                }
            } catch (err) {
                this.agentChatHistories[role].push({
                    sender: 'ai',
                    text: 'Terjadi gangguan koneksi jaringan. Silakan periksa koneksi internet Anda.',
                    time: timeStr
                });
                this.saveChatHistoriesToStorage();
            } finally {
                this.isSendingChatMessage = false;
                this.$nextTick(() => {
                    this.scrollChatToBottom();
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        scrollChatToBottom() {
            const container = document.getElementById('cooca-chat-message-stream');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        clearCurrentAgentChat() {
            const role = this.activeChatAgent || 'cfo';
            this.agentChatHistories[role] = [{
                sender: 'ai',
                text: this.agentDefaultGreetings[role] || this.agentDefaultGreetings.cfo,
                time: 'Baru saja'
            }];
            this.saveChatHistoriesToStorage();
            this.$nextTick(() => {
                this.scrollChatToBottom();
            });
        },

        async triggerDailyDiagnosis() {
            if (this.isEvaluating) return;

            if (!this.hasConfiguredProvider) {
                window.location.href = "{{ route('cooca-ai.providers') }}";
                return;
            }

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
                    const role = 'ceo';
                    this.activeChatAgent = role;
                    if (!this.agentChatHistories[role]) {
                        this.agentChatHistories[role] = [];
                    }
                    const now = new Date();
                    const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.');
                    this.agentChatHistories[role].push({
                        sender: 'ai',
                        text: (data.data && (data.data.summary || data.data.raw)) ? data.data.summary : 'Evaluasi kesehatan bisnis harian selesai.',
                        time: timeStr
                    });
                    this.saveChatHistoriesToStorage();
                    this.showConsultationModal = true;
                } else {
                    alert(data.message || 'Gagal menjalankan evaluasi.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                this.isEvaluating = false;
                this.$nextTick(() => {
                    this.scrollChatToBottom();
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        }
    };
}
</script>
