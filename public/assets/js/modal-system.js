/**
 * PROFESSIONAL MODAL SYSTEM - JavaScript Utilities
 * Handles confirmation dialogs, alerts, and request approval displays
 * Supports rich context: requester info, timestamps, status
 */

(function (window) {
    'use strict';

    // Configuration
    const CONFIG = {
        animationDuration: 300,
        closeOnBackdropClick: true,
        closeOnEscapeKey: true,
    };

    // Modal instance manager
    const ModalManager = {
        activeModal: null,
        queue: [],

        show(modal) {
            if (this.activeModal) {
                this.queue.push(modal);
                return;
            }
            this.activeModal = modal;
            modal.show();
        },

        hide(modal) {
            if (this.activeModal === modal) {
                this.activeModal = null;
                modal.hide();
                
                if (this.queue.length > 0) {
                    const nextModal = this.queue.shift();
                    this.show(nextModal);
                }
            }
        },

        hideAll() {
            if (this.activeModal) {
                this.activeModal.destroy();
                this.activeModal = null;
            }
            this.queue = [];
        },
    };

    /**
     * MODAL CLASS - Base modal implementation
     */
    class Modal {
        constructor(options = {}) {
            this.options = {
                title: 'Confirmation',
                message: 'Are you sure?',
                type: 'confirm', // confirm, alert, success, error, warning, info
                confirmText: 'Confirm',
                cancelText: 'Cancel',
                onConfirm: null,
                onCancel: null,
                closeOnBackdropClick: CONFIG.closeOnBackdropClick,
                closeOnEscapeKey: CONFIG.closeOnEscapeKey,
                ...options,
            };

            this.overlay = null;
            this.content = null;
            this.isVisible = false;
            this.isClosing = false;

            this.init();
        }

        init() {
            this.createDOM();
            this.attachEventListeners();
        }

        createDOM() {
            // Create overlay
            this.overlay = document.createElement('div');
            this.overlay.className = `modal-overlay ${this.getModalType()}`;
            this.overlay.setAttribute('aria-hidden', 'true');
            this.overlay.setAttribute('role', 'dialog');
            this.overlay.setAttribute('aria-modal', 'true');

            // Create content
            this.content = document.createElement('div');
            this.content.className = 'modal-content';

            // Header
            const header = this.createHeader();
            this.content.appendChild(header);

            // Body
            const body = this.createBody();
            this.content.appendChild(body);

            // Footer
            const footer = this.createFooter();
            this.content.appendChild(footer);

            this.overlay.appendChild(this.content);
            document.body.appendChild(this.overlay);
        }

        createHeader() {
            const header = document.createElement('div');
            header.className = 'modal-header';

            const headerContent = document.createElement('div');
            headerContent.className = 'modal-header-content';

            const title = document.createElement('h2');
            title.className = `modal-title ${this.getTitleClass()}`;
            title.innerHTML = `
                ${this.getTitleIcon()}
                <span>${this.options.title}</span>
            `;

            headerContent.appendChild(title);
            header.appendChild(headerContent);

            // Close button
            if (this.options.closeButton !== false) {
                const closeBtn = document.createElement('button');
                closeBtn.className = 'modal-close-btn';
                closeBtn.type = 'button';
                closeBtn.setAttribute('aria-label', 'Close');
                closeBtn.innerHTML = '✕';
                closeBtn.addEventListener('click', () => this.cancel());
                header.appendChild(closeBtn);
            }

            return header;
        }

        createBody() {
            const body = document.createElement('div');
            body.className = 'modal-body';

            // Message
            const message = document.createElement('p');
            message.className = 'modal-message';
            message.textContent = this.options.message;
            body.appendChild(message);

            // Request details section (if provided)
            if (this.options.details) {
                const details = this.createDetailsSection();
                body.appendChild(details);
            }

            return body;
        }

        createDetailsSection() {
            const section = document.createElement('div');
            section.className = 'request-details';

            const details = this.options.details;

            // Render each detail
            Object.entries(details).forEach(([key, value]) => {
                if (value === null || value === undefined) return;

                const item = document.createElement('div');
                item.className = 'detail-item';

                const label = document.createElement('div');
                label.className = 'detail-label';
                label.textContent = this.formatLabel(key);

                const valueEl = document.createElement('div');
                valueEl.className = 'detail-value';

                // Special formatting for certain fields
                if (key === 'status') {
                    valueEl.innerHTML = this.createStatusBadge(value);
                } else if (key === 'timestamp') {
                    valueEl.textContent = this.formatTimestamp(value);
                } else if (key === 'requestId' || key === 'id') {
                    valueEl.className += ' highlight';
                    valueEl.textContent = value;
                } else {
                    valueEl.textContent = String(value);
                }

                item.appendChild(label);
                item.appendChild(valueEl);
                section.appendChild(item);
            });

            return section;
        }

        createFooter() {
            const footer = document.createElement('div');
            footer.className = 'modal-footer';

            // Cancel button
            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'modal-btn cancel';
            cancelBtn.type = 'button';
            cancelBtn.textContent = this.options.cancelText;
            cancelBtn.addEventListener('click', () => this.cancel());

            // Confirm button
            const confirmBtn = document.createElement('button');
            confirmBtn.className = `modal-btn ${this.getButtonClass()}`;
            confirmBtn.type = 'button';
            confirmBtn.textContent = this.options.confirmText;
            confirmBtn.addEventListener('click', () => this.confirm());

            // For alert-only modals, only show confirm button
            if (this.options.type === 'alert' || this.options.type === 'success' || 
                this.options.type === 'error' || this.options.type === 'warning' ||
                this.options.type === 'info') {
                footer.appendChild(confirmBtn);
            } else {
                footer.appendChild(cancelBtn);
                footer.appendChild(confirmBtn);
            }

            return footer;
        }

        attachEventListeners() {
            // Backdrop click
            if (this.options.closeOnBackdropClick) {
                this.overlay.addEventListener('click', (e) => {
                    if (e.target === this.overlay) {
                        this.cancel();
                    }
                });
            }

            // Escape key
            if (this.options.closeOnEscapeKey) {
                this.keyListener = (e) => {
                    if (e.key === 'Escape' && this.isVisible) {
                        this.cancel();
                    }
                };
            }
        }

        show() {
            if (this.isVisible) return;

            this.isVisible = true;
            this.isClosing = false;

            this.overlay.classList.add('active');
            this.overlay.setAttribute('aria-hidden', 'false');

            if (this.keyListener) {
                document.addEventListener('keydown', this.keyListener);
            }

            // Focus management
            this.previouslyFocused = document.activeElement;
            const firstButton = this.content.querySelector('.modal-btn');
            if (firstButton) {
                setTimeout(() => firstButton.focus(), 50);
            }
        }

        hide() {
            if (!this.isVisible) return;

            this.isClosing = true;
            this.overlay.classList.add('closing');

            setTimeout(() => {
                this.isVisible = false;
                this.isClosing = false;
                this.overlay.classList.remove('active', 'closing');
                this.overlay.setAttribute('aria-hidden', 'true');

                if (this.keyListener) {
                    document.removeEventListener('keydown', this.keyListener);
                }

                // Restore focus
                if (this.previouslyFocused && typeof this.previouslyFocused.focus === 'function') {
                    this.previouslyFocused.focus();
                }
            }, CONFIG.animationDuration);
        }

        confirm() {
            const callback = this.options.onConfirm;
            this.hide();
            ModalManager.hide(this);

            if (typeof callback === 'function') {
                setTimeout(() => callback(), CONFIG.animationDuration);
            }
        }

        cancel() {
            const callback = this.options.onCancel;
            this.hide();
            ModalManager.hide(this);

            if (typeof callback === 'function') {
                setTimeout(() => callback(), CONFIG.animationDuration);
            }
        }

        destroy() {
            this.hide();
            setTimeout(() => {
                if (this.overlay && this.overlay.parentNode) {
                    this.overlay.parentNode.removeChild(this.overlay);
                }
            }, CONFIG.animationDuration);
        }

        // Helper methods
        getModalType() {
            const typeMap = {
                confirm: 'confirm',
                alert: 'alert',
                success: 'alert',
                error: 'alert',
                warning: 'alert',
                info: 'alert',
                request: 'request',
            };
            return typeMap[this.options.type] || 'confirm';
        }

        getTitleClass() {
            const typeMap = {
                success: 'success',
                error: 'danger',
                warning: 'warning',
                info: 'info',
            };
            return typeMap[this.options.type] || '';
        }

        getTitleIcon() {
            const icons = {
                success: '✓',
                error: '!',
                warning: '⚠',
                info: 'ℹ',
                confirm: '?',
                request: '?',
            };
            return `<span class="modal-title-icon">${icons[this.options.type] || '?'}</span>`;
        }

        getButtonClass() {
            const typeMap = {
                confirm: 'primary',
                alert: 'primary',
                success: 'primary',
                error: 'danger',
                warning: 'primary',
                info: 'primary',
                request: 'primary',
            };
            return typeMap[this.options.type] || 'primary';
        }

        formatLabel(key) {
            return key
                .replace(/([A-Z])/g, ' $1')
                .replace(/^./, (str) => str.toUpperCase())
                .trim();
        }

        formatTimestamp(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        }

        createStatusBadge(status) {
            const statusMap = {
                pending: 'Pending',
                approved: 'Approved',
                disposed: 'Disposed',
                rejected: 'Rejected',
                completed: 'Completed',
            };

            const displayStatus = statusMap[status] || status;
            const statusClass = status.toLowerCase();

            return `<span class="status-badge ${statusClass}">${displayStatus}</span>`;
        }
    }

    /**
     * PUBLIC API - Global functions
     */

    window.showConfirmation = function (options) {
        const modal = new Modal({
            type: 'confirm',
            ...options,
        });
        ModalManager.show(modal);
        return modal;
    };

    window.showAlert = function (message, options = {}) {
        const modal = new Modal({
            type: options.type || 'alert',
            message,
            title: options.title || 'Notice',
            confirmText: options.confirmText || 'OK',
            cancelText: undefined,
            ...options,
        });
        ModalManager.show(modal);
        return modal;
    };

    window.showSuccess = function (message, options = {}) {
        return window.showAlert(message, { type: 'success', title: 'Success', ...options });
    };

    window.showError = function (message, options = {}) {
        return window.showAlert(message, { type: 'error', title: 'Error', ...options });
    };

    window.showWarning = function (message, options = {}) {
        return window.showAlert(message, { type: 'warning', title: 'Warning', ...options });
    };

    window.showInfo = function (message, options = {}) {
        return window.showAlert(message, { type: 'info', title: 'Information', ...options });
    };

    /**
     * REQUEST APPROVAL DIALOG
     * Shows request details with requester, timestamp, and status
     */
    window.showRequestApproval = function (options) {
        const {
            title = 'Approve Request',
            requestDetails = {},
            requesterName = 'Unknown',
            timestamp = new Date(),
            currentStatus = 'Pending',
            onApprove = null,
            onReject = null,
        } = options;

        // Build details object
        const details = {
            requestId: requestDetails.id || requestDetails.requestId,
            requester: requesterName,
            timestamp: timestamp,
            status: currentStatus,
            ...requestDetails,
        };

        const modal = new Modal({
            type: 'request',
            title,
            message: requestDetails.message || 'Please review and approve this request.',
            confirmText: 'Approve',
            cancelText: 'Cancel',
            details,
            onConfirm: onApprove,
            onCancel: onReject,
        });

        ModalManager.show(modal);
        return modal;
    };

    /**
     * FORM CONFIRMATION HANDLER
     * Automatically intercepts form submissions with data-confirm-message
     */
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const confirmMessage = form.getAttribute('data-confirm-message');
        if (!confirmMessage) return;

        // If already confirmed, allow submission
        if (form.dataset.modalConfirmed === 'true') {
            form.dataset.modalConfirmed = 'false';
            return;
        }

        event.preventDefault();

        // Build request details from form data
        const formData = new FormData(form);
        const requestDetails = {
            message: confirmMessage,
        };

        window.showConfirmation({
            title: form.getAttribute('data-confirm-title') || 'Confirm Action',
            message: confirmMessage,
            confirmText: form.getAttribute('data-confirm-confirm-text') || 'Confirm',
            cancelText: form.getAttribute('data-confirm-cancel-text') || 'Cancel',
            onConfirm: function () {
                form.dataset.modalConfirmed = 'true';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            },
        });
    });

    /**
     * LEGACY COMPATIBILITY
     * Support old API for gradual migration
     */
    window.showAppConfirm = function (message, confirmCallback, cancelCallback, options) {
        window.showConfirmation({
            message,
            confirmText: (options && options.confirmText) || 'Confirm',
            cancelText: (options && options.cancelText) || 'Cancel',
            title: (options && options.title) || 'Confirmation',
            onConfirm: confirmCallback,
            onCancel: cancelCallback,
        });
    };

    window.showAppAlert = function (message, options) {
        window.showAlert(message, {
            title: (options && options.title) || 'Notice',
            confirmText: (options && options.confirmText) || 'OK',
            ...options,
        });
    };

    // Export for testing
    window.ModalSystem = {
        Modal,
        ModalManager,
        CONFIG,
    };

})(window);
