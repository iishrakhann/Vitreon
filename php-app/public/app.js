document.addEventListener('DOMContentLoaded', () => {
    const otpForm = document.querySelector('[data-otp-form]');
    const otpHidden = document.querySelector('[data-otp-hidden]');
    const otpInputs = Array.from(document.querySelectorAll('[data-otp-digit]'));
    const otpSubmitButton = document.querySelector('[data-otp-submit]');
    const otpSubmitLabel = document.querySelector('[data-otp-submit-label]');
    const otpResendButton = document.querySelector('[data-otp-resend-button]');
    const otpResendNote = document.querySelector('[data-otp-resend-note]');
    const bookingForm = document.querySelector('[data-booking-form]');
    const galleryRoot = document.querySelector('[data-venue-gallery]');

    if (bookingForm) {
        const bookingDateTimeInput = bookingForm.querySelector('[data-booking-datetime]');
        const authModal = document.querySelector('[data-auth-modal]');
        const authModalClosers = Array.from(document.querySelectorAll('[data-auth-modal-close]'));
        const draftKey = bookingForm.getAttribute('data-draft-key') || '';
        const isAuthenticated = bookingForm.getAttribute('data-authenticated') === 'true';
        const formFields = Array.from(bookingForm.querySelectorAll('input[name], select[name], textarea[name]'));

        const openAuthModal = () => {
            if (!authModal) {
                return;
            }

            authModal.hidden = false;
            document.body.classList.add('auth-modal-open');
        };

        const closeAuthModal = () => {
            if (!authModal) {
                return;
            }

            authModal.hidden = true;
            document.body.classList.remove('auth-modal-open');
        };

        const saveDraft = () => {
            if (!draftKey) {
                return;
            }

            const payload = {};
            formFields.forEach((field) => {
                if (!field.name || field.type === 'hidden') {
                    return;
                }

                payload[field.name] = field.value;
            });

            window.localStorage.setItem(draftKey, JSON.stringify(payload));
        };

        const restoreDraft = () => {
            if (!draftKey) {
                return;
            }

            const rawDraft = window.localStorage.getItem(draftKey);
            if (!rawDraft) {
                return;
            }

            try {
                const payload = JSON.parse(rawDraft);
                formFields.forEach((field) => {
                    if (!field.name || field.type === 'hidden') {
                        return;
                    }

                    if (typeof payload[field.name] === 'string') {
                        field.value = payload[field.name];
                    }
                });
            } catch (error) {
                window.localStorage.removeItem(draftKey);
            }
        };

        if (bookingDateTimeInput) {
            bookingDateTimeInput.addEventListener('input', () => {
                if (bookingDateTimeInput.value === '') {
                    bookingDateTimeInput.setCustomValidity('');
                    return;
                }

                const selectedDate = new Date(bookingDateTimeInput.value);
                if (Number.isNaN(selectedDate.getTime()) || selectedDate.getTime() < Date.now()) {
                    bookingDateTimeInput.setCustomValidity('Please choose a future event date and time.');
                } else {
                    bookingDateTimeInput.setCustomValidity('');
                }
            });
        }

        formFields.forEach((field) => {
            field.addEventListener('input', saveDraft);
            field.addEventListener('change', saveDraft);
        });

        authModalClosers.forEach((closer) => {
            closer.addEventListener('click', closeAuthModal);
        });

        restoreDraft();

        bookingForm.addEventListener('submit', (event) => {
            saveDraft();

            if (bookingDateTimeInput) {
                bookingDateTimeInput.dispatchEvent(new Event('input', { bubbles: true }));
                if (!bookingDateTimeInput.checkValidity()) {
                    event.preventDefault();
                    bookingDateTimeInput.reportValidity();
                    return;
                }
            }

            if (!isAuthenticated) {
                event.preventDefault();
                openAuthModal();
            }
        });
    }

    if (galleryRoot) {
        const slides = Array.from(galleryRoot.querySelectorAll('[data-venue-slide]'));
        const thumbs = Array.from(galleryRoot.querySelectorAll('[data-venue-thumb]'));

        if (slides.length > 0 && thumbs.length > 0) {
            let slideTimer = null;
            let isPointerInside = false;

            const activateSlide = (index) => {
                const nextIndex = Math.max(0, Math.min(index, slides.length - 1));
                slides.forEach((slide, slideIndex) => {
                    slide.classList.toggle('is-active', slideIndex === nextIndex);
                });

                thumbs.forEach((thumb, thumbIndex) => {
                    thumb.classList.toggle('is-active', thumbIndex === nextIndex);
                });
            };

            const startAutoAdvance = () => {
                if (slides.length < 2 || slideTimer) {
                    return;
                }

                slideTimer = window.setInterval(() => {
                    if (isPointerInside) {
                        return;
                    }

                    const activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
                    activateSlide((activeIndex + 1) % slides.length);
                }, 4500);
            };

            thumbs.forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    const targetIndex = Number.parseInt(thumb.getAttribute('data-target-index') || '0', 10);
                    activateSlide(targetIndex);
                });
            });

            galleryRoot.addEventListener('mouseenter', () => {
                isPointerInside = true;
            });

            galleryRoot.addEventListener('mouseleave', () => {
                isPointerInside = false;
            });

            galleryRoot.addEventListener('keydown', (event) => {
                const activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    activateSlide((activeIndex + 1) % slides.length);
                }

                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    activateSlide((activeIndex - 1 + slides.length) % slides.length);
                }
            });

            startAutoAdvance();
        }
    }

    if (!otpForm || !otpHidden || otpInputs.length === 0) {
        return;
    }

    if (otpResendButton && otpResendNote) {
        let remainingSeconds = Number.parseInt(otpResendNote.getAttribute('data-resend-seconds') || '60', 10);
        const updateResendUi = () => {
            if (Number.isNaN(remainingSeconds) || remainingSeconds <= 0) {
                otpResendButton.disabled = false;
                otpResendNote.textContent = "Didn't receive OTP? You can resend it now.";
                return;
            }

            otpResendButton.disabled = true;
            otpResendNote.textContent = `Didn't receive OTP? You can resend it in ${remainingSeconds} seconds.`;
            remainingSeconds -= 1;
            window.setTimeout(updateResendUi, 1000);
        };

        updateResendUi();
    }

    const updateOtpValue = () => {
        otpHidden.value = otpInputs.map((input) => input.value).join('');
        return otpHidden.value;
    };

    const setOtpLoadingState = (isLoading) => {
        if (otpSubmitButton) {
            otpSubmitButton.disabled = isLoading;
            otpSubmitButton.classList.toggle('is-loading', isLoading);
        }

        if (otpSubmitLabel) {
            otpSubmitLabel.textContent = isLoading ? 'Verifying OTP...' : 'Verify and continue';
        }

        otpInputs.forEach((input) => {
            input.disabled = isLoading;
        });
    };

    const submitIfComplete = () => {
        const value = updateOtpValue();
        if (value.length === otpInputs.length && /^\d{6}$/.test(value)) {
            setOtpLoadingState(true);
            otpForm.requestSubmit();
        }
    };

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (event) => {
            const sanitized = event.target.value.replace(/\D/g, '');
            event.target.value = sanitized.slice(-1);

            if (event.target.value !== '' && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
                otpInputs[index + 1].select();
            }

            submitIfComplete();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && input.value === '' && index > 0) {
                otpInputs[index - 1].focus();
                otpInputs[index - 1].value = '';
                updateOtpValue();
            }

            if (event.key === 'ArrowLeft' && index > 0) {
                otpInputs[index - 1].focus();
            }

            if (event.key === 'ArrowRight' && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('focus', () => {
            input.select();
        });
    });

    otpForm.addEventListener('paste', (event) => {
        const pastedText = (event.clipboardData || window.clipboardData).getData('text');
        const digits = pastedText.replace(/\D/g, '').slice(0, otpInputs.length).split('');

        if (digits.length === 0) {
            return;
        }

        event.preventDefault();
        otpInputs.forEach((input, index) => {
            input.value = digits[index] || '';
        });

        const focusIndex = Math.min(digits.length, otpInputs.length) - 1;
        if (focusIndex >= 0) {
            otpInputs[focusIndex].focus();
        }

        submitIfComplete();
    });

    otpForm.addEventListener('submit', (event) => {
        const value = updateOtpValue();
        if (!/^\d{6}$/.test(value)) {
            event.preventDefault();
            setOtpLoadingState(false);
            return;
        }

        setOtpLoadingState(true);
    });
});
