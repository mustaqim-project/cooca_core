/**
 * Cooca UMKM — Product Tour Engine (Multi-Device & High-Precision Mobile Support)
 * Supports dynamic industry selection during onboarding, direct HPP simulation guide,
 * and high-precision target coordinate tracking with auto-drawer open/close.
 */
class GuidedProductTour {
    constructor() {
        this.steps = window.TOUR_STEPS || [];
        this.currentStepIndex = 0;
        this.isActive = false;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        this.overlay = null;
        this.spotlight = null;
        this.popover = null;

        // Complete 20 Industry Templates List for Onboarding Selection
        this.industryTemplates = [
            // F&B
            { code: 'fnb_resto', name: 'Restoran / Rumah Makan', category: 'F&B', desc: 'BOM multi-porsi, bumbu, waste dapur, sewa outlet' },
            { code: 'fnb_cafe', name: 'Coffee Shop & Cafe', category: 'F&B', desc: 'Biji kopi, fresh milk, sirup, cup takeaway, barista' },
            { code: 'fnb_bakery', name: 'Bakery & Cake Shop', category: 'F&B', desc: 'Adonan batch, topping, oven hours, sisa display' },
            { code: 'fnb_cloud_kitchen', name: 'Cloud Kitchen & Delivery', category: 'F&B', desc: 'Bahan porsi, premium packaging, fee delivery' },
            { code: 'fnb_catering', name: 'Catering & Prasmanan', category: 'F&B', desc: 'Paket buffet/box porsi besar, koki harian' },
            { code: 'fnb_frozen_food', name: 'Frozen Food Manufacturing', category: 'F&B', desc: 'Olahan beku, blast freezer, kemasan vacuum' },

            // Manufaktur & Kerajinan
            { code: 'mfg_garment', name: 'Konveksi & Garment', category: 'Manufaktur', desc: 'Kain meter/kg, kancing, zipper, ongkos jahit CMT' },
            { code: 'mfg_precision', name: 'Pabrik Plastik & Metal Presisi', category: 'Manufaktur', desc: 'Injeksi molding, stamping CNC, mold tooling' },
            { code: 'mfg_furniture', name: 'Furniture & Woodworking', category: 'Manufaktur', desc: 'Kayu solid, plywood, cat duco, tukang kayu' },
            { code: 'mfg_craft', name: 'Kerajinan Tangan & Handmade', category: 'Manufaktur', desc: 'Bahan craft, upah pengrajin, box packaging' },
            { code: 'mfg_printing', name: 'Percetakan & Digital Printing', category: 'Manufaktur', desc: 'Kertas rim, tinta banner, mesin offset' },

            // Retail & Dagang
            { code: 'retail_reseller', name: 'Reseller & Toko Retail', category: 'Retail', desc: 'Landed cost, ongkir masuk, diskon beli supplier' },
            { code: 'retail_pharmacy', name: 'Apotek & Toko Obat', category: 'Retail', desc: 'HPP obat PBF, embalase racikan, buffer expired' },

            // Jasa Profesional & Layanan
            { code: 'service_agency', name: 'Digital Agency / IT Software', category: 'Jasa', desc: 'Man-hours developer/designer, cloud server' },
            { code: 'service_workshop', name: 'Bengkel Mobil & Motor', category: 'Jasa', desc: 'Sparepart ganti + jasa mekanik flat rate' },
            { code: 'service_barbershop', name: 'Barbershop & Salon', category: 'Jasa', desc: 'Komisi kapster + bahan pomade/shampoo' },
            { code: 'service_laundry', name: 'Laundry Kiloan & Satuan', category: 'Jasa', desc: 'Detergen, softener, gas dryer, operator' },
            { code: 'service_contractor', name: 'Kontraktor & Renovasi', category: 'Jasa', desc: 'Semen, pasir, tukang & mandor borongan' },
            { code: 'service_event', name: 'Event & Wedding Organizer', category: 'Jasa', desc: 'Dekorasi, sound system, honor crew event' },

            // Agribisnis & Peternakan
            { code: 'agri_farming', name: 'Peternakan & Pertanian', category: 'Agri', desc: 'Bibit/DOC ayam, pakan FCR, panen mortality' }
        ];

        this.selectedIndustryCode = null;
        this.init();
    }

    async init() {
        if (!this.steps || this.steps.length === 0) return;

        // Fetch onboarding status from server
        try {
            const res = await fetch('/onboarding/status', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            if (res.ok) {
                const data = await res.json();
                const tourVersion = window.TOUR_VERSION || 1;
                if (!data.completed || (data.version || 1) < tourVersion) {
                    this.currentStepIndex = (data.version || 1) < tourVersion
                        ? 0
                        : Math.max(0, (data.current_step || 1) - 1);
                    if (this.currentStepIndex >= this.steps.length) {
                        this.currentStepIndex = 0;
                    }
                    // Start tour automatically after DOM load
                    setTimeout(() => {
                        this.start();
                    }, 600);
                }
            }
        } catch (e) {
            console.warn('Guided Tour: Status check deferred.', e);
        }

        // Global key handler (Escape to skip, Arrow keys for prev/next)
        window.addEventListener('keydown', (e) => {
            if (!this.isActive) return;
            if (e.key === 'Escape') {
                this.promptSkip();
            } else if (e.key === 'ArrowRight') {
                this.next();
            } else if (e.key === 'ArrowLeft') {
                this.prev();
            }
        });

        // Reposition on window resize / orientation change / scroll
        window.addEventListener('resize', () => {
            if (this.isActive) this.updateSpotlightAndPopoverPosition();
        });
        window.addEventListener('orientationchange', () => {
            if (this.isActive) setTimeout(() => this.updateSpotlightAndPopoverPosition(), 250);
        });
        window.addEventListener('scroll', () => {
            if (this.isActive) this.updateSpotlightAndPopoverPosition();
        }, { passive: true });
    }

    createDOM() {
        if (document.getElementById('tour-backdrop')) return;

        // 1. Transparent interaction layer; the spotlight ring provides the dimmed area.
        const backdrop = document.createElement('div');
        backdrop.id = 'tour-backdrop';
        backdrop.className = 'fixed inset-0 z-[9990] pointer-events-auto transition-opacity duration-200';
        document.body.appendChild(backdrop);
        this.overlay = backdrop;

        // 2. Spotlight Border Pulse Ring
        const ring = document.createElement('div');
        ring.id = 'tour-spotlight-ring';
        ring.className = 'fixed z-[9992] pointer-events-none rounded-2xl border-2 border-emerald-400 transition-all duration-200 ease-out hidden';
        ring.style.boxShadow = '0 0 0 100vmax rgba(2, 6, 23, 0.82), 0 0 30px rgba(52, 211, 153, 0.45)';
        document.body.appendChild(ring);
        this.spotlight = ring;

        // 3. Popover Card (Fully responsive with safe margins)
        const popover = document.createElement('div');
        popover.id = 'tour-popover';
        popover.className = 'fixed z-[9995] max-w-lg w-[calc(100vw-1.5rem)] sm:w-[460px] bg-slate-900/98 backdrop-blur-2xl border border-slate-700/90 rounded-2xl shadow-2xl shadow-black/90 p-4 sm:p-6 transition-all duration-300 ease-out hidden text-slate-100';
        document.body.appendChild(popover);
        this.popover = popover;
    }

    start() {
        this.createDOM();
        this.isActive = true;
        this.renderCurrentStep();
    }

    renderCurrentStep() {
        if (this.currentStepIndex < 0 || this.currentStepIndex >= this.steps.length) {
            this.finish();
            return;
        }

        const step = this.steps[this.currentStepIndex];
        const isFirst = this.currentStepIndex === 0;
        const isLast = this.currentStepIndex === this.steps.length - 1;
        const totalSteps = this.steps.length;
        const currentStepNum = this.currentStepIndex + 1;
        const isMobile = window.innerWidth < 1024;
        const isSidebarTarget = step.target && (
            step.target.startsWith('#tour-nav-') ||
            step.target.startsWith('#tour-group-') ||
            step.target === '#tour-active-business' ||
            step.target === '#tour-switch-business'
        );

        // On mobile, if step targets a sidebar navigation item, automatically open mobile drawer
        if (isSidebarTarget && isMobile) {
            window.dispatchEvent(new CustomEvent('tour-open-sidebar'));
        } else if (isMobile) {
            window.dispatchEvent(new CustomEvent('tour-close-sidebar'));
        }

        // Delay to allow mobile layout & sidebar drawer transition
        const drawerDelay = isMobile && isSidebarTarget ? 280 : 60;

        setTimeout(() => {
            let targetEl = null;
            if (step.target) {
                targetEl = document.querySelector(step.target);
                if (!targetEl) {
                    console.warn(`Tour: Target ${step.target} not found, proceeding to next step.`);
                    this.currentStepIndex++;
                    this.renderCurrentStep();
                    return;
                }

                // Open the sidebar accordion only when it is actually closed.
                const groupPanel = targetEl.closest('[x-show], [class~="hidden"]');
                const groupButton = groupPanel?.previousElementSibling;
                const panelStyle = groupPanel ? window.getComputedStyle(groupPanel) : null;
                const panelIsClosed = groupPanel && (
                    panelStyle?.display === 'none' ||
                    groupPanel.getBoundingClientRect().height === 0
                );

                if (panelIsClosed && groupButton?.tagName === 'BUTTON') {
                    groupButton.click();
                }
            }

            // Scroll first, then measure after the browser has laid out the open menu.
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'nearest' });
            }

            // Build Progress Dots
            const progressDots = this.steps.map((_, i) => {
                const activeClass = i === this.currentStepIndex ? 'bg-emerald-400 w-4 sm:w-5' : (i < this.currentStepIndex ? 'bg-emerald-600 w-1.5 sm:w-2' : 'bg-slate-700 w-1.5 sm:w-2');
                return `<div class="h-1.5 rounded-full transition-all duration-300 ${activeClass}"></div>`;
            }).join('');

            // Special Interactive Content: Industry Picker (Full 20 Industries with Quick Search)
            let interactiveContentHtml = '';
            if (step.type === 'industry_picker') {
                interactiveContentHtml = `
                    <div class="mt-3 space-y-2.5">
                        <div class="relative">
                            <input type="text" id="tour-industry-search" placeholder="Cari industri (cth: cafe, garment, laundry, retail)..."
                                   class="w-full pl-8 pr-3 py-1.5 bg-slate-950 border border-slate-700/80 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                        </div>

                        <div id="tour-industry-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-52 overflow-y-auto pr-1">
                            ${this.industryTemplates.map(tpl => `
                                <button type="button"
                                        data-industry="${tpl.code}"
                                        data-name="${tpl.name.toLowerCase()}"
                                        data-desc="${tpl.desc.toLowerCase()}"
                                        data-cat="${tpl.category.toLowerCase()}"
                                        class="tour-industry-btn p-2.5 rounded-xl border text-left transition-all ${this.selectedIndustryCode === tpl.code ? 'bg-emerald-500/20 border-emerald-400 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-950/80 border-slate-800 hover:border-slate-700 text-slate-300'}">
                                    <div class="font-bold text-xs flex items-center justify-between gap-1.5">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span class="w-2 h-2 rounded-full shrink-0 ${this.selectedIndustryCode === tpl.code ? 'bg-emerald-400' : 'bg-slate-600'}"></span>
                                            <span class="truncate">${tpl.name}</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 font-mono shrink-0">${tpl.category}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1 line-clamp-2 leading-tight">${tpl.desc}</div>
                                </button>
                            `).join('')}
                        </div>
                        <div id="tour-industry-status" class="text-[11px] text-emerald-400 font-semibold min-h-[18px]">
                            ${this.selectedIndustryCode ? '✓ Template terpilih. Klik Lanjut untuk melanjutkan.' : 'Pilih salah satu dari 20 template di atas untuk bisnis Anda'}
                        </div>
                    </div>
                `;
            }

            this.popover.innerHTML = `
                <div class="space-y-3 sm:space-y-4">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2 sm:px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold uppercase font-mono tracking-wider">
                                ${step.badge || 'Panduan'}
                            </span>
                            <span class="text-[10px] sm:text-[11px] text-slate-400 font-mono">
                                ${currentStepNum} / ${totalSteps}
                            </span>
                        </div>
                        <button id="tour-btn-skip" class="text-xs text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                            <span>Lewati</span>
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Title & Description -->
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-white tracking-tight flex items-center gap-2">
                            <span>${step.title}</span>
                        </h3>
                        <p class="text-xs text-slate-300 leading-relaxed mt-1.5">
                            ${step.description}
                        </p>
                        ${interactiveContentHtml}
                    </div>

                    <!-- Progress Dots -->
                    <div class="flex items-center gap-1 py-0.5">
                        ${progressDots}
                    </div>

                    <!-- Footer Navigation -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                        <div>
                            ${!isFirst ? `
                                <button id="tour-btn-prev" class="px-3 sm:px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-all flex items-center gap-1">
                                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                    <span>Kembali</span>
                                </button>
                            ` : `<div></div>`}
                        </div>

                        <div class="flex items-center gap-2">
                            ${isLast ? `
                                <button id="tour-btn-finish" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white text-xs font-bold shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-1.5">
                                    <i data-lucide="calculator" class="w-4 h-4"></i>
                                    <span>${step.actionUrl ? 'Buka Dashboard Cooca UMKM' : 'Selesai & Mulai'}</span>
                                </button>
                            ` : `
                                <button id="tour-btn-next" class="px-3.5 sm:px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-1.5">
                                    <span>${isFirst ? 'Mulai Panduan' : 'Lanjut'}</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            `;

            if (window.lucide) {
                window.lucide.createIcons();
            }

            // Event Listeners for Industry Picker buttons and Live Search
            if (step.type === 'industry_picker') {
                const searchInput = this.popover.querySelector('#tour-industry-search');
                const btns = this.popover.querySelectorAll('.tour-industry-btn');

                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        const q = e.target.value.toLowerCase().trim();
                        btns.forEach(btn => {
                            const name = btn.getAttribute('data-name') || '';
                            const desc = btn.getAttribute('data-desc') || '';
                            const cat = btn.getAttribute('data-cat') || '';
                            if (!q || name.includes(q) || desc.includes(q) || cat.includes(q)) {
                                btn.classList.remove('hidden');
                            } else {
                                btn.classList.add('hidden');
                            }
                        });
                    });
                }

                btns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const code = btn.getAttribute('data-industry');
                        this.selectIndustryTemplate(code);
                    });
                });
            }

            // Standard Navigation Listeners
            document.getElementById('tour-btn-skip')?.addEventListener('click', () => this.promptSkip());
            document.getElementById('tour-btn-prev')?.addEventListener('click', () => this.prev());
            document.getElementById('tour-btn-next')?.addEventListener('click', () => this.next());
            document.getElementById('tour-btn-finish')?.addEventListener('click', () => {
                if (step.actionUrl) {
                    this.finish().then(() => {
                        window.location.href = step.actionUrl;
                    });
                } else {
                    this.finish();
                }
            });

            // Precise Spotlight positioning update after Alpine and sidebar layout settle.
            this.updateSpotlightWhenReady(step, 0);

            // Persist step
            this.saveStepProgress(currentStepNum);
        }, drawerDelay);
    }

    updateSpotlightWhenReady(step, attempt = 0) {
        if (!this.isActive || !this.popover) return;

        const targetEl = step.target ? document.querySelector(step.target) : null;
        const rect = targetEl?.getBoundingClientRect();
        const hasLayout = rect && rect.width > 0 && rect.height > 0;

        if (targetEl && !hasLayout && attempt < 20) {
            requestAnimationFrame(() => this.updateSpotlightWhenReady(step, attempt + 1));
            return;
        }

        if (targetEl && !hasLayout) return;

        this.updateSpotlightAndPopoverPosition();
    }

    async selectIndustryTemplate(code) {
        this.selectedIndustryCode = code;
        const statusEl = document.getElementById('tour-industry-status');
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-amber-400 animate-pulse">Menerapkan template industri...</span>';
        }

        // Apply template via server API
        try {
            const formData = new FormData();
            formData.append('template_code', code);
            formData.append('_token', this.csrfToken);

            const res = await fetch('/settings/apply-template', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: formData
            });

            if (statusEl) {
                statusEl.innerHTML = '✓ Template industri berhasil diterapkan ke bisnis Anda!';
            }

            // Re-render button highlights
            const btns = this.popover.querySelectorAll('.tour-industry-btn');
            btns.forEach(b => {
                const bCode = b.getAttribute('data-industry');
                if (bCode === code) {
                    b.className = 'tour-industry-btn p-2.5 rounded-xl border text-left transition-all bg-emerald-500/20 border-emerald-400 text-white shadow-md shadow-emerald-500/20';
                } else {
                    b.className = 'tour-industry-btn p-2.5 rounded-xl border text-left transition-all bg-slate-950/80 border-slate-800 hover:border-slate-700 text-slate-300';
                }
            });
        } catch (e) {
            if (statusEl) statusEl.innerHTML = '✓ Template siap digunakan.';
        }
    }

    updateSpotlightAndPopoverPosition() {
        if (!this.isActive || !this.popover) return;

        const step = this.steps[this.currentStepIndex];
        const targetEl = step.target ? document.querySelector(step.target) : null;

        if (targetEl && this.spotlight) {
            const rect = targetEl.getBoundingClientRect();
            if (rect.width <= 0 || rect.height <= 0) return;
            const padding = 8;
            const x = Math.max(0, rect.left - padding);
            const y = Math.max(0, rect.top - padding);
            const width = rect.width + (padding * 2);
            const height = rect.height + (padding * 2);

            // Update Ring
            this.overlay.style.background = 'transparent';
            this.spotlight.style.left = `${x}px`;
            this.spotlight.style.top = `${y}px`;
            this.spotlight.style.width = `${width}px`;
            this.spotlight.style.height = `${height}px`;
            this.spotlight.classList.remove('hidden');

            // Position Popover with high precision
            this.positionPopover(rect, step.placement || 'bottom');
        } else if (this.popover) {
            // Modal Center Placement
            this.overlay.style.background = 'rgba(2, 6, 23, 0.82)';
            if (this.spotlight) {
                this.spotlight.classList.add('hidden');
            }

            // Center popover
            this.popover.style.left = '50%';
            this.popover.style.top = '50%';
            this.popover.style.transform = 'translate(-50%, -50%)';
            this.popover.classList.remove('hidden');
        }
    }

    positionPopover(targetRect, placement) {
        const popoverWidth = this.popover.offsetWidth || Math.min(window.innerWidth - 24, 420);
        const popoverHeight = this.popover.offsetHeight || 220;
        const gap = 12;
        const screenWidth = window.innerWidth;
        const screenHeight = window.innerHeight;

        let left = 0;
        let top = 0;

        // Mobile & Tablet Screen Calculation (< 1024px)
        if (screenWidth < 1024) {
            left = (screenWidth - popoverWidth) / 2;

            // Check vertical space
            const spaceBelow = screenHeight - targetRect.bottom;
            const spaceAbove = targetRect.top;

            if (spaceBelow >= popoverHeight + gap + 10) {
                top = targetRect.bottom + gap;
            } else if (spaceAbove >= popoverHeight + gap + 10) {
                top = targetRect.top - popoverHeight - gap;
            } else {
                // If neither has ample space, clamp gracefully to bottom or top
                top = Math.max(12, screenHeight - popoverHeight - 12);
            }
        } else {
            // Desktop Calculations
            switch (placement) {
                case 'right':
                    left = targetRect.right + gap;
                    top = targetRect.top + (targetRect.height / 2) - (popoverHeight / 2);
                    if (left + popoverWidth > screenWidth) {
                        left = targetRect.left - popoverWidth - gap;
                    }
                    break;
                case 'left':
                    left = targetRect.left - popoverWidth - gap;
                    top = targetRect.top + (targetRect.height / 2) - (popoverHeight / 2);
                    break;
                case 'top':
                    left = targetRect.left + (targetRect.width / 2) - (popoverWidth / 2);
                    top = targetRect.top - popoverHeight - gap;
                    break;
                case 'bottom':
                default:
                    left = targetRect.left + (targetRect.width / 2) - (popoverWidth / 2);
                    top = targetRect.bottom + gap;
                    break;
            }

            // Boundaries
            left = Math.max(12, Math.min(left, screenWidth - popoverWidth - 12));
            top = Math.max(12, Math.min(top, screenHeight - popoverHeight - 12));
        }

        this.popover.style.transform = 'none';
        this.popover.style.left = `${left}px`;
        this.popover.style.top = `${top}px`;
        this.popover.classList.remove('hidden');
    }

    next() {
        if (this.currentStepIndex < this.steps.length - 1) {
            this.currentStepIndex++;
            this.renderCurrentStep();
        } else {
            this.finish();
        }
    }

    prev() {
        if (this.currentStepIndex > 0) {
            this.currentStepIndex--;
            this.renderCurrentStep();
        }
    }

    promptSkip() {
        if (confirm('Lewati panduan pengenalan sistem ini? Anda tetap dapat mengaksesnya kembali kapan saja melalui menu pengaturan atau profil.')) {
            this.finish();
        }
    }

    async finish() {
        this.isActive = false;
        window.dispatchEvent(new CustomEvent('tour-close-sidebar'));
        if (this.overlay) this.overlay.remove();
        if (this.spotlight) this.spotlight.remove();
        if (this.popover) this.popover.remove();

        try {
            await fetch('/onboarding/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
        } catch (e) {
            console.error('Failed to save tour completion status:', e);
        }
    }

    async saveStepProgress(stepNum) {
        try {
            await fetch('/onboarding/step', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ step: stepNum })
            });
        } catch (e) {
            // Silently fail network sync for individual step
        }
    }
}

// Global initialization
window.guidedTour = new GuidedProductTour();
