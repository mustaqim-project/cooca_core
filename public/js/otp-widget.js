/*
 * Cooca OTP Widget
 *  - countdown: masa berlaku OTP + cooldown resend 60 detik
 *  - boxes:     input OTP 6 kotak (Apple HIG style) + auto-submit saat penuh
 * Dipakai oleh: auth/otp, auth/register-otp, app/profile/verify-contact
 */
(function () {
    'use strict';

    function base(expiresAt, resendIn) {
        return {
            expiresAtSeconds: Math.floor(Number(expiresAt)) || 0,
            resendInSeconds: Math.max(0, Math.floor(Number(resendIn))) || 0,
            loadedAt: Math.floor(Date.now() / 1000),
            now: Math.floor(Date.now() / 1000),
            timer: null,
            init() {
                this.now = Math.floor(Date.now() / 1000);
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(() => { this.now = Math.floor(Date.now() / 1000); }, 1000);
            },
            destroy() {
                if (this.timer) clearInterval(this.timer);
            },
            remaining() { return Math.max(0, this.expiresAtSeconds - this.now); },
            available() { return this.remaining() > 0; },
            cooldown() { return Math.max(0, this.resendInSeconds - (this.now - this.loadedAt)); },
            clockLabel() {
                const s = this.remaining();
                const m = Math.floor(s / 60);
                const r = s % 60;
                return m + ':' + String(r).padStart(2, '0');
            }
        };
    }

    window.coocaOtp = {
        countdown: base,

        // 6 kotak input + hitung mundur, dengan auto-submit saat penuh
        boxes(expiresAt, resendIn) {
            const data = base(expiresAt, resendIn);
            data.parts = ['', '', '', '', '', ''];

            data.otp = function () { return this.parts.join(''); };

            data.setValue = function (raw) {
                const digits = String(raw || '').replace(/\D/g, '').slice(0, 6).split('');
                this.parts = ['', '', '', '', '', ''].map((_, i) => digits[i] || '');
            };

            data.paste = function (evt) {
                evt.preventDefault();
                const source = evt.clipboardData || window.clipboardData || {};
                const text = (typeof source.getData === 'function' ? source.getData('text') : '') || '';
                this.setValue(text);

                const form = evt.target && evt.target.closest ? evt.target.closest('form') : null;
                if (form) {
                    const hidden = form.querySelector('input[name="otp"]');
                    if (hidden) hidden.value = this.otp();
                }

                this.parts.forEach((val, idx) => {
                    const box = document.getElementById('otp-box-' + idx);
                    if (box) box.value = val;
                });

                if (this.otp().length === 6) this.trySubmitFrom(evt.target);
            };

            data.handleInput = function (i, evt) {
                const el = evt.target;
                el.value = el.value.replace(/\D/g, '').slice(0, 1);
                this.parts[i] = el.value;

                const form = el && el.closest ? el.closest('form') : null;
                if (form) {
                    const hidden = form.querySelector('input[name="otp"]');
                    if (hidden) hidden.value = this.otp();
                }

                if (el.value && i < 5) {
                    const next = document.getElementById('otp-box-' + (i + 1));
                    if (next) next.focus();
                }
                if (this.otp().length === 6) this.trySubmitFrom(el);
            };

            data.handleKeydown = function (i, evt) {
                if ((evt.key === 'Backspace' || evt.key === 'Delete') && !evt.target.value && i > 0) {
                    evt.preventDefault();
                    const prev = document.getElementById('otp-box-' + (i - 1));
                    if (prev) { prev.value = ''; prev.focus(); }
                    this.parts[i - 1] = '';

                    const form = evt.target && evt.target.closest ? evt.target.closest('form') : null;
                    if (form) {
                        const hidden = form.querySelector('input[name="otp"]');
                        if (hidden) hidden.value = this.otp();
                    }
                }
            };

            data.trySubmitFrom = function (el) {
                const form = el && el.closest ? el.closest('form') : null;
                if (!form) return;

                const hidden = form.querySelector('input[name="otp"]');
                if (hidden) hidden.value = this.otp();

                setTimeout(() => {
                    if (hidden) hidden.value = this.otp();
                    if (form.requestSubmit) {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }, 50);
            };

            return data;
        }
    };
})();