/**
 * AppAlert — Centralized Custom Alert, Toast & Confirmation System
 * Cooca SaaS Platform
 * 
 * Replaces native browser alert() and confirm() with modern, responsive,
 * accessible, and theme-adaptive styled popups and toasts.
 */
(function (window, document) {
    'use strict';

    // Prevent duplicate initialization
    if (window.AppAlert && window.AppAlert.__initialized) {
        return;
    }

    // Insert CSS Stylesheet once
    const styleId = 'app-alert-styles';
    if (!document.getElementById(styleId)) {
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            :root {
                --aa-bg: #0f172a;
                --aa-card-bg: rgba(15, 23, 42, 0.95);
                --aa-border: rgba(51, 65, 85, 0.85);
                --aa-text: #f8fafc;
                --aa-text-muted: #94a3b8;
                --aa-backdrop: rgba(2, 6, 23, 0.75);
                --aa-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
                --aa-success: #10b981;
                --aa-error: #ef4444;
                --aa-warning: #f59e0b;
                --aa-info: #0ea5e9;
                --aa-danger: #ef4444;
                --aa-radius: 1rem;
                --aa-font: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            }

            .light, [data-theme="light"], body.light {
                --aa-bg: #ffffff;
                --aa-card-bg: rgba(255, 255, 255, 0.98);
                --aa-border: rgba(226, 232, 240, 0.95);
                --aa-text: #0f172a;
                --aa-text-muted: #64748b;
                --aa-backdrop: rgba(15, 23, 42, 0.45);
                --aa-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            }

            /* Container Toast (Top-Right) */
            .aa-toast-container {
                position: fixed;
                top: 1.25rem;
                right: 1.25rem;
                z-index: 99999;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                max-width: 420px;
                width: calc(100vw - 2.5rem);
                pointer-events: none;
                font-family: var(--aa-font);
            }

            .aa-toast {
                position: relative;
                pointer-events: auto;
                background: var(--aa-card-bg);
                color: var(--aa-text);
                border: 1px solid var(--aa-border);
                border-radius: var(--aa-radius);
                box-shadow: var(--aa-shadow);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                padding: 1rem 1.15rem;
                display: flex;
                align-items: flex-start;
                gap: 0.875rem;
                overflow: hidden;
                opacity: 0;
                transform: translateX(30px) scale(0.96);
                transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .aa-toast.aa-show {
                opacity: 1;
                transform: translateX(0) scale(1);
            }

            .aa-toast.aa-hide {
                opacity: 0;
                transform: translateX(40px) scale(0.92);
            }

            .aa-toast-icon {
                flex-shrink: 0;
                width: 2.25rem;
                height: 2.25rem;
                border-radius: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 900;
                font-size: 1.1rem;
            }

            .aa-toast-body {
                flex: 1;
                min-width: 0;
            }

            .aa-toast-title {
                font-size: 0.875rem;
                font-weight: 700;
                line-height: 1.25;
                margin-bottom: 0.25rem;
                color: var(--aa-text);
            }

            .aa-toast-message {
                font-size: 0.8125rem;
                line-height: 1.45;
                color: var(--aa-text-muted);
                word-break: break-word;
            }

            .aa-toast-close {
                flex-shrink: 0;
                background: transparent;
                border: none;
                color: var(--aa-text-muted);
                cursor: pointer;
                padding: 0.25rem;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: color 0.15s, background-color 0.15s;
            }

            .aa-toast-close:hover {
                color: var(--aa-text);
                background: rgba(148, 163, 184, 0.15);
            }

            .aa-toast-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                width: 100%;
                background: currentColor;
                opacity: 0.4;
                transform-origin: left;
                animation: aa-progress linear forwards;
            }

            @keyframes aa-progress {
                from { transform: scaleX(1); }
                to { transform: scaleX(0); }
            }

            /* Types Styling */
            .aa-type-success .aa-toast-icon {
                background: rgba(16, 185, 129, 0.15);
                color: var(--aa-success);
                border: 1px solid rgba(16, 185, 129, 0.25);
            }
            .aa-type-success .aa-toast-progress { color: var(--aa-success); }

            .aa-type-error .aa-toast-icon,
            .aa-type-danger .aa-toast-icon {
                background: rgba(239, 68, 68, 0.15);
                color: var(--aa-error);
                border: 1px solid rgba(239, 68, 68, 0.25);
            }
            .aa-type-error .aa-toast-progress,
            .aa-type-danger .aa-toast-progress { color: var(--aa-error); }

            .aa-type-warning .aa-toast-icon {
                background: rgba(245, 158, 11, 0.15);
                color: var(--aa-warning);
                border: 1px solid rgba(245, 158, 11, 0.25);
            }
            .aa-type-warning .aa-toast-progress { color: var(--aa-warning); }

            .aa-type-info .aa-toast-icon {
                background: rgba(14, 165, 233, 0.15);
                color: var(--aa-info);
                border: 1px solid rgba(14, 165, 233, 0.25);
            }
            .aa-type-info .aa-toast-progress { color: var(--aa-info); }

            /* Modal Backdrop & Dialog */
            .aa-modal-backdrop {
                position: fixed;
                inset: 0;
                background: var(--aa-backdrop);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                z-index: 100000;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                opacity: 0;
                transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                font-family: var(--aa-font);
            }

            .aa-modal-backdrop.aa-show {
                opacity: 1;
            }

            .aa-modal {
                background: var(--aa-card-bg);
                color: var(--aa-text);
                border: 1px solid var(--aa-border);
                border-radius: 1.25rem;
                box-shadow: var(--aa-shadow);
                width: 100%;
                max-width: 420px;
                padding: 1.75rem 1.5rem 1.5rem;
                text-align: center;
                position: relative;
                transform: scale(0.94) translateY(8px);
                opacity: 0;
                transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .aa-modal-backdrop.aa-show .aa-modal {
                transform: scale(1) translateY(0);
                opacity: 1;
            }

            .aa-modal-icon-wrap {
                width: 3.5rem;
                height: 3.5rem;
                border-radius: 1rem;
                margin: 0 auto 1.25rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                font-weight: 900;
            }

            .aa-modal-title {
                font-size: 1.15rem;
                font-weight: 800;
                color: var(--aa-text);
                margin: 0 0 0.5rem;
                line-height: 1.3;
            }

            .aa-modal-message {
                font-size: 0.875rem;
                color: var(--aa-text-muted);
                line-height: 1.55;
                margin: 0 0 1.5rem;
                word-break: break-word;
            }

            .aa-modal-actions {
                display: flex;
                gap: 0.75rem;
                justify-content: center;
            }

            .aa-btn {
                flex: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.65rem 1.25rem;
                font-size: 0.875rem;
                font-weight: 700;
                border-radius: 0.75rem;
                border: 1px solid transparent;
                cursor: pointer;
                transition: all 0.15s ease;
                font-family: inherit;
            }

            .aa-btn:focus-visible {
                outline: 2px solid var(--aa-info);
                outline-offset: 2px;
            }

            .aa-btn-cancel {
                background: rgba(148, 163, 184, 0.1);
                color: var(--aa-text-muted);
                border-color: var(--aa-border);
            }

            .aa-btn-cancel:hover {
                background: rgba(148, 163, 184, 0.2);
                color: var(--aa-text);
            }

            .aa-btn-confirm-success {
                background: var(--aa-success);
                color: #020617;
                box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            }
            .aa-btn-confirm-success:hover {
                background: #059669;
            }

            .aa-btn-confirm-danger,
            .aa-btn-confirm-error {
                background: var(--aa-danger);
                color: #ffffff;
                box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
            }
            .aa-btn-confirm-danger:hover,
            .aa-btn-confirm-error:hover {
                background: #dc2626;
            }

            .aa-btn-confirm-warning {
                background: var(--aa-warning);
                color: #020617;
                box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
            }
            .aa-btn-confirm-warning:hover {
                background: #d97706;
            }

            .aa-btn-confirm-info {
                background: var(--aa-info);
                color: #ffffff;
                box-shadow: 0 4px 14px rgba(14, 165, 233, 0.35);
            }
            .aa-btn-confirm-info:hover {
                background: #0284c7;
            }

            @media (max-width: 480px) {
                .aa-toast-container {
                    top: 0.75rem;
                    right: 0.75rem;
                    width: calc(100vw - 1.5rem);
                }
                .aa-modal {
                    padding: 1.5rem 1.25rem 1.25rem;
                    border-radius: 1rem;
                }
                .aa-modal-actions {
                    flex-direction: column-reverse;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Create Toast Container
    let toastContainer = null;
    function getToastContainer() {
        if (!toastContainer || !document.body.contains(toastContainer)) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'aa-toast-container';
            toastContainer.setAttribute('role', 'region');
            toastContainer.setAttribute('aria-label', 'Notifikasi');
            document.body.appendChild(toastContainer);
        }
        return toastContainer;
    }

    // Icon SVG SVGs for crispy rendering without external font dependency
    const ICONS = {
        success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
        danger: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>',
        question: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        close: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
    };

    // Deduplication tracking (prevents rapid double-click spamming)
    const recentAlerts = new Map();

    function isDuplicate(key) {
        const now = Date.now();
        if (recentAlerts.has(key) && (now - recentAlerts.get(key) < 1200)) {
            return true;
        }
        recentAlerts.set(key, now);
        return false;
    }

    /**
     * Main AppAlert Object
     */
    const AppAlert = {
        __initialized: true,

        /**
         * Show Toast Notification
         */
        toast: function (type, message, options = {}) {
            if (!message && !options.title) return;

            const safeType = ['success', 'error', 'warning', 'info', 'danger'].includes(type) ? type : 'info';
            const dedupeKey = `${safeType}:${options.title || ''}:${message}`;
            if (isDuplicate(dedupeKey)) return;

            const duration = options.duration !== undefined ? options.duration : (
                safeType === 'error' ? 6000 : (safeType === 'warning' ? 5000 : 4000)
            );
            const autoClose = options.autoClose !== false && duration > 0;

            const container = getToastContainer();
            const toast = document.createElement('div');
            toast.className = `aa-toast aa-type-${safeType}`;
            toast.setAttribute('role', safeType === 'error' ? 'alert' : 'status');
            toast.setAttribute('aria-live', safeType === 'error' ? 'assertive' : 'polite');

            // Icon
            const iconEl = document.createElement('div');
            iconEl.className = 'aa-toast-icon';
            iconEl.innerHTML = ICONS[safeType] || ICONS.info;
            toast.appendChild(iconEl);

            // Body
            const bodyEl = document.createElement('div');
            bodyEl.className = 'aa-toast-body';

            if (options.title) {
                const titleEl = document.createElement('div');
                titleEl.className = 'aa-toast-title';
                titleEl.textContent = options.title;
                bodyEl.appendChild(titleEl);
            }

            if (message) {
                const msgEl = document.createElement('div');
                msgEl.className = 'aa-toast-message';
                if (options.html) {
                    msgEl.innerHTML = String(message);
                } else {
                    msgEl.textContent = String(message);
                }
                bodyEl.appendChild(msgEl);
            }
            toast.appendChild(bodyEl);

            // Close button
            if (options.showClose !== false) {
                const closeBtn = document.createElement('button');
                closeBtn.className = 'aa-toast-close';
                closeBtn.setAttribute('aria-label', 'Tutup Notifikasi');
                closeBtn.innerHTML = ICONS.close;
                closeBtn.onclick = () => dismiss();
                toast.appendChild(closeBtn);
            }

            // Progress bar
            let progressEl = null;
            if (autoClose) {
                progressEl = document.createElement('div');
                progressEl.className = 'aa-toast-progress';
                progressEl.style.animationDuration = `${duration}ms`;
                toast.appendChild(progressEl);
            }

            container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.classList.add('aa-show');
            });

            let timer = null;
            let remaining = duration;
            let start = Date.now();

            function startTimer() {
                if (!autoClose) return;
                start = Date.now();
                timer = setTimeout(dismiss, remaining);
                if (progressEl) {
                    progressEl.style.animationPlayState = 'running';
                }
            }

            function pauseTimer() {
                if (!autoClose) return;
                clearTimeout(timer);
                remaining -= Date.now() - start;
                if (progressEl) {
                    progressEl.style.animationPlayState = 'paused';
                }
            }

            function dismiss() {
                clearTimeout(timer);
                toast.classList.remove('aa-show');
                toast.classList.add('aa-hide');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 260);
            }

            toast.addEventListener('mouseenter', pauseTimer);
            toast.addEventListener('mouseleave', startTimer);
            toast.addEventListener('touchstart', pauseTimer, { passive: true });
            toast.addEventListener('touchend', startTimer, { passive: true });

            startTimer();

            return { dismiss };
        },

        /**
         * Show Modal Alert / Confirm Dialog
         */
        modal: function (options = {}) {
            return new Promise((resolve) => {
                const type = options.type || 'info';
                const safeType = ['success', 'error', 'warning', 'info', 'danger'].includes(type) ? type : 'info';
                const isConfirm = Boolean(options.confirm);

                // Backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'aa-modal-backdrop';
                backdrop.setAttribute('role', 'dialog');
                backdrop.setAttribute('aria-modal', 'true');
                if (options.title) {
                    backdrop.setAttribute('aria-labelledby', 'aa-modal-title');
                }

                // Modal dialog
                const modal = document.createElement('div');
                modal.className = `aa-modal aa-type-${safeType}`;

                // Icon
                const iconWrap = document.createElement('div');
                iconWrap.className = 'aa-modal-icon-wrap';
                iconWrap.classList.add(`aa-type-${safeType}`);
                if (safeType === 'success') {
                    iconWrap.style.background = 'rgba(16, 185, 129, 0.15)';
                    iconWrap.style.color = '#10b981';
                    iconWrap.style.border = '1px solid rgba(16, 185, 129, 0.25)';
                    iconWrap.innerHTML = ICONS.success;
                } else if (safeType === 'warning') {
                    iconWrap.style.background = 'rgba(245, 158, 11, 0.15)';
                    iconWrap.style.color = '#f59e0b';
                    iconWrap.style.border = '1px solid rgba(245, 158, 11, 0.25)';
                    iconWrap.innerHTML = ICONS.warning;
                } else if (safeType === 'error' || safeType === 'danger') {
                    iconWrap.style.background = 'rgba(239, 68, 68, 0.15)';
                    iconWrap.style.color = '#ef4444';
                    iconWrap.style.border = '1px solid rgba(239, 68, 68, 0.25)';
                    iconWrap.innerHTML = safeType === 'danger' ? ICONS.danger : ICONS.error;
                } else {
                    iconWrap.style.background = 'rgba(14, 165, 233, 0.15)';
                    iconWrap.style.color = '#0ea5e9';
                    iconWrap.style.border = '1px solid rgba(14, 165, 233, 0.25)';
                    iconWrap.innerHTML = isConfirm ? ICONS.question : ICONS.info;
                }
                modal.appendChild(iconWrap);

                // Title
                const titleEl = document.createElement('h3');
                titleEl.id = 'aa-modal-title';
                titleEl.className = 'aa-modal-title';
                titleEl.textContent = options.title || (isConfirm ? 'Konfirmasi' : (safeType === 'success' ? 'Berhasil!' : (safeType === 'error' ? 'Terjadi Kesalahan' : 'Perhatian')));
                modal.appendChild(titleEl);

                // Message
                const msgEl = document.createElement('div');
                msgEl.className = 'aa-modal-message';
                if (options.html) {
                    msgEl.innerHTML = String(options.message || '');
                } else {
                    msgEl.textContent = String(options.message || '');
                }
                modal.appendChild(msgEl);

                // Actions
                const actionsEl = document.createElement('div');
                actionsEl.className = 'aa-modal-actions';

                let cancelBtn = null;
                if (isConfirm) {
                    cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'aa-btn aa-btn-cancel';
                    cancelBtn.textContent = options.cancelText || 'Batal';
                    cancelBtn.onclick = () => close(false);
                    actionsEl.appendChild(cancelBtn);
                }

                const confirmBtn = document.createElement('button');
                confirmBtn.type = 'button';
                confirmBtn.className = `aa-btn aa-btn-confirm-${safeType}`;
                confirmBtn.textContent = options.confirmText || (isConfirm ? (safeType === 'danger' ? 'Hapus' : 'Ya, Lanjutkan') : 'OK');
                confirmBtn.onclick = () => close(true);
                actionsEl.appendChild(confirmBtn);

                modal.appendChild(actionsEl);
                backdrop.appendChild(modal);
                document.body.appendChild(backdrop);

                // Previous active element for focus restoration
                const prevFocus = document.activeElement;

                // Animate in
                requestAnimationFrame(() => {
                    backdrop.classList.add('aa-show');
                    if (isConfirm && cancelBtn && safeType === 'danger') {
                        // Safety: Focus Cancel button on destructive actions to avoid accidental triggers
                        cancelBtn.focus();
                    } else {
                        confirmBtn.focus();
                    }
                });

                // Backdrop click behavior
                backdrop.addEventListener('click', (e) => {
                    if (e.target === backdrop) {
                        if (!isConfirm) {
                            close(false);
                        }
                    }
                });

                // Keyboard handling (ESC & Enter & Tab trap)
                function handleKey(e) {
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        close(false);
                    } else if (e.key === 'Enter') {
                        if (document.activeElement === cancelBtn) {
                            close(false);
                        } else {
                            e.preventDefault();
                            close(true);
                        }
                    } else if (e.key === 'Tab') {
                        const focusables = modal.querySelectorAll('button:not([disabled])');
                        if (focusables.length === 0) return;
                        const first = focusables[0];
                        const last = focusables[focusables.length - 1];
                        if (e.shiftKey && document.activeElement === first) {
                            e.preventDefault();
                            last.focus();
                        } else if (!e.shiftKey && document.activeElement === last) {
                            e.preventDefault();
                            first.focus();
                        }
                    }
                }
                document.addEventListener('keydown', handleKey);

                function close(result) {
                    document.removeEventListener('keydown', handleKey);
                    backdrop.classList.remove('aa-show');
                    setTimeout(() => {
                        if (backdrop.parentNode) {
                            backdrop.parentNode.removeChild(backdrop);
                        }
                        if (prevFocus && typeof prevFocus.focus === 'function') {
                            try { prevFocus.focus(); } catch (_) {}
                        }
                        if (result && typeof options.onConfirm === 'function') {
                            options.onConfirm();
                        } else if (!result && typeof options.onCancel === 'function') {
                            options.onCancel();
                        }
                        resolve(result);
                    }, 220);
                }
            });
        },

        /**
         * Convenience wrappers
         */
        success: function (message, options = {}) {
            if (options.modal) {
                return this.modal(Object.assign({ type: 'success', message }, options));
            }
            return this.toast('success', message, options);
        },

        error: function (message, options = {}) {
            if (options.modal) {
                return this.modal(Object.assign({ type: 'error', message }, options));
            }
            return this.toast('error', message, options);
        },

        warning: function (message, options = {}) {
            if (options.modal) {
                return this.modal(Object.assign({ type: 'warning', message }, options));
            }
            return this.toast('warning', message, options);
        },

        info: function (message, options = {}) {
            if (options.modal) {
                return this.modal(Object.assign({ type: 'info', message }, options));
            }
            return this.toast('info', message, options);
        },

        show: function (options = {}) {
            if (options.modal) {
                return this.modal(options);
            }
            return this.toast(options.type || 'info', options.message || '', options);
        },

        /**
         * Confirmation dialog (Promise-based)
         */
        confirm: function (options = {}) {
            if (typeof options === 'string') {
                options = { message: options };
            }
            return this.modal(Object.assign({
                confirm: true,
                type: options.type || 'warning',
                title: options.title || 'Konfirmasi Tindakan',
                confirmText: options.confirmText || 'Ya, Lanjutkan',
                cancelText: options.cancelText || 'Batal'
            }, options));
        },

        /**
         * Helper to confirm form submit asynchronously
         */
        confirmSubmit: function (event, form, message, title = 'Konfirmasi Tindakan', type = 'danger') {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }
            this.confirm({
                title: title,
                message: message,
                type: type,
                confirmText: type === 'danger' ? 'Hapus' : 'Ya, Lanjutkan',
                cancelText: 'Batal'
            }).then((confirmed) => {
                if (confirmed) {
                    // Temporarily remove onsubmit to prevent re-interception loop
                    const oldOnSubmit = form.onsubmit;
                    form.onsubmit = null;
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                    form.onsubmit = oldOnSubmit;
                }
            });
            return false;
        },

        /**
         * Initialize Global Event Interceptors (e.g. data-confirm on forms)
         */
        initInterceptors: function () {
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form || !form.dataset) return;

                if (form.dataset.confirm && !form.dataset.confirmed) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    AppAlert.confirm({
                        title: form.dataset.confirmTitle || 'Konfirmasi Tindakan',
                        message: form.dataset.confirm,
                        type: form.dataset.confirmType || 'danger',
                        confirmText: form.dataset.confirmButton || 'Ya, Lanjutkan',
                        cancelText: 'Batal'
                    }).then((confirmed) => {
                        if (confirmed) {
                            form.dataset.confirmed = 'true';
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                            } else {
                                form.submit();
                            }
                        }
                    });
                }
            }, true);
        }
    };

    // Global Exposure
    window.AppAlert = AppAlert;

    // Safety Polyfill: Override native window.alert so no native browser popup ever triggers!
    try {
        window.alert = function (message) {
            AppAlert.warning(String(message == null ? '' : message));
        };
    } catch (_) {
        // In case environment restricts window.alert override
    }

    // Auto-init interceptors when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AppAlert.initInterceptors());
    } else {
        AppAlert.initInterceptors();
    }

})(window, document);
