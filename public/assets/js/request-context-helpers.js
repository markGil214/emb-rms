/**
 * REQUEST CONTEXT HELPER FUNCTIONS
 * Provides utilities for enriching alerts/confirmations with full request context
 * Usage: across disposal, archive, borrow, relocation, and other approval workflows
 */

(function (window) {
    'use strict';

    /**
     * Format timestamp for display with consistent timezone handling
     */
    function formatRequestTimestamp(timestamp) {
        if (!timestamp) return 'N/A';
        const date = new Date(timestamp);
        if (isNaN(date.getTime())) return 'N/A';
        
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
    }

    /**
     * DISPOSAL WORKFLOW
     * Shows approval modal with disposal details
     */
    window.showDisposalApproval = function (disposalData) {
        const {
            disposalId,
            folderId,
            folderCode,
            companyName,
            disposalMethod,
            requestedBy,
            requestedDate,
            currentStatus = 'Pending',
            message = 'Please review and approve this disposal request.',
            approveCallback,
            rejectCallback,
        } = disposalData;

        const details = {
            'Disposal ID': disposalId || 'N/A',
            'File Code': folderCode || 'N/A',
            'Company': companyName || 'N/A',
            'Method': disposalMethod || 'N/A',
            'Requested By': requestedBy || 'Unknown',
            'Request Date': formatRequestTimestamp(requestedDate),
            'Status': currentStatus,
        };

        // Remove empty entries
        Object.keys(details).forEach((key) => {
            if (details[key] === 'N/A' || details[key] === 'Unknown') {
                delete details[key];
            }
        });

        window.showRequestApproval({
            title: 'Approve Disposal Request',
            requestDetails: {
                ...details,
                message: message,
            },
            requesterName: requestedBy || 'Unknown',
            timestamp: requestedDate,
            currentStatus: currentStatus,
            onApprove: approveCallback,
            onReject: rejectCallback,
        });
    };

    /**
     * ARCHIVE APPROVAL WORKFLOW
     * Shows approval modal with archive details
     */
    window.showArchiveApproval = function (archiveData) {
        const {
            archiveId,
            folderId,
            folderCode,
            companyName,
            requestedBy,
            requestedDate,
            reason,
            currentStatus = 'Pending',
            message = 'Please review and approve this archive request.',
            approveCallback,
            rejectCallback,
        } = archiveData;

        const details = {
            'Archive ID': archiveId || 'N/A',
            'File Code': folderCode || 'N/A',
            'Company': companyName || 'N/A',
            'Reason': reason || 'Routine maintenance',
            'Requested By': requestedBy || 'Unknown',
            'Request Date': formatRequestTimestamp(requestedDate),
            'Status': currentStatus,
        };

        window.showRequestApproval({
            title: 'Approve Archive Request',
            requestDetails: details,
            requesterName: requestedBy || 'Unknown',
            timestamp: requestedDate,
            currentStatus: currentStatus,
            onApprove: approveCallback,
            onReject: rejectCallback,
        });
    };

    /**
     * RESTORATION WORKFLOW
     * Shows approval modal for folder restoration
     */
    window.showRestorationApproval = function (restorationData) {
        const {
            restorationId,
            folderId,
            folderCode,
            companyName,
            requestedBy,
            requestedDate,
            reason,
            currentStatus = 'Pending',
            message = 'Please review and approve this restoration request.',
            approveCallback,
            rejectCallback,
        } = restorationData;

        const details = {
            'Restoration ID': restorationId || 'N/A',
            'File Code': folderCode || 'N/A',
            'Company': companyName || 'N/A',
            'Reason': reason || 'Business requirement',
            'Requested By': requestedBy || 'Unknown',
            'Request Date': formatRequestTimestamp(requestedDate),
            'Status': currentStatus,
        };

        window.showRequestApproval({
            title: 'Approve Restoration Request',
            requestDetails: details,
            requesterName: requestedBy || 'Unknown',
            timestamp: requestedDate,
            currentStatus: currentStatus,
            onApprove: approveCallback,
            onReject: rejectCallback,
        });
    };

    /**
     * BORROW REQUEST WORKFLOW
     * Shows approval/action modal for borrow requests
     */
    window.showBorrowApproval = function (borrowData) {
        const {
            borrowId,
            folderId,
            folderCode,
            companyName,
            borrowedBy,
            borrowDate,
            expectedReturnDate,
            reason,
            currentStatus = 'Pending',
            message = 'Please review this borrow request.',
            approveCallback,
            rejectCallback,
        } = borrowData;

        const details = {
            'Borrow ID': borrowId || 'N/A',
            'File Code': folderCode || 'N/A',
            'Company': companyName || 'N/A',
            'Reason': reason || 'Internal use',
            'Borrowed By': borrowedBy || 'Unknown',
            'Borrow Date': formatRequestTimestamp(borrowDate),
            'Expected Return': formatRequestTimestamp(expectedReturnDate),
            'Status': currentStatus,
        };

        window.showRequestApproval({
            title: 'Borrow Request Details',
            requestDetails: details,
            requesterName: borrowedBy || 'Unknown',
            timestamp: borrowDate,
            currentStatus: currentStatus,
            onApprove: approveCallback,
            onReject: rejectCallback,
        });
    };

    /**
     * RELOCATION REQUEST WORKFLOW
     * Shows approval modal for relocation requests
     */
    window.showRelocationApproval = function (relocationData) {
        const {
            relocationId,
            folderId,
            folderCode,
            companyName,
            requestedBy,
            requestedDate,
            currentLocation,
            newLocation,
            reason,
            currentStatus = 'Pending',
            message = 'Please review and approve this relocation request.',
            approveCallback,
            rejectCallback,
        } = relocationData;

        const details = {
            'Relocation ID': relocationId || 'N/A',
            'File Code': folderCode || 'N/A',
            'Company': companyName || 'N/A',
            'From': currentLocation || 'N/A',
            'To': newLocation || 'N/A',
            'Reason': reason || 'Shelf management',
            'Requested By': requestedBy || 'Unknown',
            'Request Date': formatRequestTimestamp(requestedDate),
            'Status': currentStatus,
        };

        window.showRequestApproval({
            title: 'Approve Relocation Request',
            requestDetails: details,
            requesterName: requestedBy || 'Unknown',
            timestamp: requestedDate,
            currentStatus: currentStatus,
            onApprove: approveCallback,
            onReject: rejectCallback,
        });
    };

    /**
     * PERMISSION CHANGE NOTIFICATION
     * Shows alert when permissions are changed
     */
    window.showPermissionChangeAlert = function (changeData) {
        const {
            userName,
            action, // 'granted', 'revoked'
            permissions = [],
            changedBy,
            timestamp,
        } = changeData;

        const permissionList = Array.isArray(permissions)
            ? permissions.join(', ')
            : String(permissions);

        const title = action === 'granted' ? 'Permissions Granted' : 'Permissions Revoked';
        const message = `${action === 'granted' ? 'Granted' : 'Revoked'} to ${userName}: ${permissionList}`;

        window.showAlert(message, {
            type: action === 'granted' ? 'success' : 'warning',
            title: title,
            details: {
                'User': userName,
                'Action': action === 'granted' ? 'Granted' : 'Revoked',
                'Permissions': permissionList,
                'Changed By': changedBy || 'System',
                'Timestamp': formatRequestTimestamp(timestamp),
            },
        });
    };

    /**
     * GENERIC REQUEST CONFIRMATION
     * Handles any pending request with full context
     */
    window.showRequestConfirmation = function (options) {
        const {
            type = 'confirm', // 'approve', 'reject', 'delete', 'confirm'
            title = 'Confirm Action',
            requestId,
            requestType,
            requestedBy,
            requestedDate,
            currentStatus = 'Pending',
            details = {},
            onConfirm,
            onCancel,
        } = options;

        const typeMap = {
            approve: { type: 'confirm', confirmText: 'Approve', title: 'Approve Request' },
            reject: { type: 'confirm', confirmText: 'Reject', title: 'Reject Request' },
            delete: { type: 'confirm', confirmText: 'Delete', title: 'Confirm Deletion' },
            confirm: { type: 'confirm', confirmText: 'Confirm', title: 'Confirm Action' },
        };

        const typeConfig = typeMap[type] || typeMap.confirm;

        const fullDetails = {
            'Request Type': requestType,
            'Request ID': requestId,
            'Requested By': requestedBy,
            'Date': formatRequestTimestamp(requestedDate),
            'Status': currentStatus,
            ...details,
        };

        window.showConfirmation({
            title: title || typeConfig.title,
            confirmText: typeConfig.confirmText,
            cancelText: 'Cancel',
            message: `Please confirm this action. This operation cannot be undone.`,
            details: fullDetails,
            onConfirm: onConfirm,
            onCancel: onCancel,
        });
    };

    /**
     * BULK ACTION CONFIRMATION
     * For multiple records
     */
    window.showBulkActionConfirmation = function (bulkData) {
        const {
            action, // 'approve', 'reject', 'delete', 'archive'
            count,
            recordType,
            affectedRecords = [],
            performedBy,
            timestamp,
            onConfirm,
            onCancel,
        } = bulkData;

        const actionMap = {
            approve: { verb: 'Approve', type: 'confirm' },
            reject: { verb: 'Reject', type: 'confirm' },
            delete: { verb: 'Delete', type: 'error' },
            archive: { verb: 'Archive', type: 'warning' },
        };

        const { verb } = actionMap[action] || { verb: 'Process' };

        const recordList = Array.isArray(affectedRecords)
            ? affectedRecords.slice(0, 3).join(', ') +
              (affectedRecords.length > 3 ? ` +${affectedRecords.length - 3} more` : '')
            : 'Selected records';

        const message = `${verb} ${count} ${recordType}? This will affect: ${recordList}`;

        window.showConfirmation({
            title: `${verb} ${count} ${recordType}`,
            message: message,
            confirmText: verb,
            cancelText: 'Cancel',
            onConfirm: onConfirm,
            onCancel: onCancel,
        });
    };

    /**
     * SUCCESS/ERROR NOTIFICATION WITH CONTEXT
     */
    window.showActionResult = function (resultData) {
        const {
            success,
            action, // 'approved', 'rejected', 'created', 'deleted', etc.
            recordType,
            recordId,
            message,
            timestamp,
            performedBy,
        } = resultData;

        const actionMap = {
            approved: 'Approved',
            rejected: 'Rejected',
            created: 'Created',
            deleted: 'Deleted',
            archived: 'Archived',
            restored: 'Restored',
            updated: 'Updated',
        };

        const actionLabel = actionMap[action] || action;
        const title = success ? 'Success' : 'Error';
        const type = success ? 'success' : 'error';

        const defaultMessage = success
            ? `Successfully ${actionLabel} ${recordType} #${recordId}`
            : `Failed to ${action} ${recordType}. Please try again.`;

        window.showAlert(message || defaultMessage, {
            type: type,
            title: title,
            confirmText: 'OK',
        });
    };

    /**
     * FORM FIELD VALIDATION ERROR
     */
    window.showValidationError = function (errorData) {
        const {
            field,
            message,
            validationType, // 'required', 'invalid', 'duplicate', etc.
        } = errorData;

        const typeMap = {
            required: 'This field is required',
            invalid: 'Invalid format',
            duplicate: 'This value already exists',
            minLength: 'Minimum length not met',
            maxLength: 'Maximum length exceeded',
            pattern: 'Invalid pattern',
        };

        const errorMessage = message || typeMap[validationType] || 'Validation failed';

        window.showAlert(`${field}: ${errorMessage}`, {
            type: 'error',
            title: 'Validation Error',
        });
    };

    // Export utilities for testing
    window.RequestContextHelpers = {
        formatRequestTimestamp,
    };

})(window);
