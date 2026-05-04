<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $roles = $roles ?? []; ?>
<div class="mx-auto w-full max-w-8xl px-4 py-6 sm:px-6 lg:px-8 min-w-0">
	<!-- Header with Manage Roles -->
	<div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
		<div>
			<h1 class="text-3xl font-bold text-gray-900">Roles & Permissions</h1>
			<p class="text-gray-600 mt-2">Control access levels and assign capabilities to your team</p>
		</div>
		<button class="px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 font-medium inline-flex items-center whitespace-nowrap">
			<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
			</svg>
			Manage Roles
		</button>
	</div>

	<!-- Permission Matrix Tabs -->
	<div class="mb-6 border-b border-gray-200 overflow-x-auto">
		<div class="flex min-w-max gap-2 sm:gap-4 lg:gap-8">
			<button class="tab-button active px-4 py-3 border-b-2 border-blue-600 text-blue-600 font-medium" data-tab="document">
				Document Records
			</button>
			<button class="tab-button px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-gray-900" data-tab="borrow">
				Borrow Management
			</button>
			<button class="tab-button px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-gray-900" data-tab="relocation">
				Relocation Management
			</button>
			<button class="tab-button px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-gray-900" data-tab="archive">
				Archive & Disposal
			</button>
			<button class="tab-button px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-gray-900" data-tab="system">
				System Admin
			</button>
		</div>
	</div>

	<!-- Save/Reset Confirmation (Top Right) -->
	<div id="saveBar" class="hidden" style="
		position: fixed;
		top: 20px;
		right: 20px;
		z-index: 50;
		width: 340px;
		padding: 18px 18px 16px;
		border-radius: 16px;
		border: 1px solid rgba(148, 163, 184, 0.22);
		background: rgba(15, 23, 42, 0.94);
		backdrop-filter: blur(14px);
		-webkit-backdrop-filter: blur(14px);
		box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
		border-top: 3px solid #3b82f6;
	">
		<div id="savePendingState" style="display: block;">
			<div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px;">
			<div style="width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(59, 130, 246, 0.14); color: #93c5fd;">
				<svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 20 20">
					<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
				</svg>
			</div>
			<div style="min-width: 0;">
				<div style="font-size: 14px; font-weight: 700; color: #f8fafc; line-height: 1.2;">Unsaved changes</div>
				<div style="margin-top: 4px; font-size: 12px; color: rgba(226, 232, 240, 0.78); line-height: 1.45;">Review and confirm the permission updates before leaving this view.</div>
			</div>
			</div>
			<div id="saveBarActions" style="display: flex; gap: 10px;">
			<button id="resetBtn" style="
				flex: 1;
				padding: 10px 14px;
				background: rgba(255, 255, 255, 0.06);
				color: #e2e8f0;
				border: 1px solid rgba(148, 163, 184, 0.22);
				border-radius: 10px;
				font-weight: 600;
				font-size: 13px;
				letter-spacing: 0.01em;
				cursor: pointer;
				transition: all 0.2s ease;
			" onmouseover="this.style.backgroundColor='rgba(148, 163, 184, 0.12)'; this.style.borderColor='rgba(148, 163, 184, 0.35)';" onmouseout="this.style.backgroundColor='rgba(255, 255, 255, 0.06)'; this.style.borderColor='rgba(148, 163, 184, 0.22)';">
				Reset
			</button>
			<button id="saveBtn" style="
				flex: 1;
				padding: 10px 14px;
				background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
				color: #ffffff;
				border: none;
				border-radius: 10px;
				font-weight: 600;
				font-size: 13px;
				letter-spacing: 0.01em;
				cursor: pointer;
				transition: all 0.2s ease;
				box-shadow: 0 10px 24px rgba(37, 99, 235, 0.28);
			" onmouseover="this.style.boxShadow='0 12px 28px rgba(37, 99, 235, 0.35)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.boxShadow='0 10px 24px rgba(37, 99, 235, 0.28)'; this.style.transform='translateY(0)';">
				Save
			</button>
			</div>
		</div>
		<div id="saveSuccessState" class="hidden" style="display: none; align-items: center; gap: 12px; padding: 10px 2px 2px;">
			<div style="width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(16, 185, 129, 0.16); color: #6ee7b7;">
				<svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 20 20">
					<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
				</svg>
			</div>
			<div style="min-width: 0;">
				<div style="font-size: 14px; font-weight: 700; color: #f8fafc; line-height: 1.2;">Permissions updated successfully</div>
				<div style="margin-top: 4px; font-size: 12px; color: rgba(226, 232, 240, 0.78); line-height: 1.45;">The changes were saved and applied to the selected roles.</div>
			</div>
		</div>
	</div>

	<!-- Permission Matrix Table -->
	<div id="matrixContainer" class="bg-white rounded-lg shadow overflow-hidden mb-32">
		<div class="overflow-x-auto">
			<table class="min-w-max w-full border-collapse">
				<thead id="matrixHead">
					<!-- Headers will be generated by JavaScript -->
				</thead>
				<tbody id="matrixBody">
					<!-- Rows will be generated by JavaScript -->
				</tbody>
			</table>
		</div>
	</div>

</div>

<script>
	// Permission definitions with CRUD structure and descriptions
	const permissionGroups = {
		document: {
			name: 'Document Records',
			permissions: {
				'view_documents': {
					label: 'View',
					description: 'View folders and documents'
				},
				'search_documents': {
					label: 'Search',
					description: 'Search & filter documents across system'
				},
				'create_documents': {
					label: 'Create',
					description: 'Create new folders and upload documents'
				},
				'update_documents': {
					label: 'Update',
					description: 'Edit folder details and document metadata'
				},
				'delete_documents': {
					label: 'Delete',
					description: 'Permanently delete documents'
				},
				'view_shelf_map': {
					label: 'Shelf Map',
					description: 'View interactive shelf map and location visualization'
				},
				'manage_racks': {
					label: 'Manage Racks',
					description: 'Create, edit, and delete racks and shelves'
				},
				'approve_create_documents': {
					label: 'Approve Create',
					description: 'Approve new folder creation requests'
				},
				'approve_archive': {
					label: 'Approve Archive',
					description: 'Approve folder archival requests'
				}
			}
		},
		borrow: {
			name: 'Borrow Management',
			permissions: {
				'view_borrow': {
					label: 'View',
					description: 'View borrow records and requests'
				},
				'create_borrow': {
					label: 'Create',
					description: 'Create new borrow requests'
				},
				'update_borrow': {
					label: 'Update',
					description: 'Update borrow details and process returns'
				},
				'approve_borrow': {
					label: 'Approve',
					description: 'Approve or reject borrow requests'
				}
			}
		},
		relocation: {
			name: 'Relocation Management',
			permissions: {
				'view_relocation': {
					label: 'View',
					description: 'View relocation requests and status'
				},
				'create_relocation': {
					label: 'Create',
					description: 'Create new relocation requests'
				},
				'update_relocation': {
					label: 'Update',
					description: 'Update relocation details'
				},
				'approve_relocation': {
					label: 'Approve',
					description: 'Approve or reject relocation requests'
				}
			}
		},
		archive: {
			name: 'Archive & Disposal',
			permissions: {
				'view_archive': {
					label: 'View',
					description: 'View archived documents and disposal requests'
				},
				'create_archive': {
					label: 'Create Archive',
					description: 'Create archive records for documents'
				},
				'update_archive': {
					label: 'Update',
					description: 'Edit archive records'
				},
				'request_disposal': {
					label: 'Request Disposal',
					description: 'Request disposal for files that reached expiration'
				},
				'approve_disposal': {
					label: 'Approve Dispose',
					description: 'Approve document disposal requests'
				},
				'request_restore': {
					label: 'Request Restore',
					description: 'Request restoration of archived folders'
				},
				'approve_restore': {
					label: 'Approve Restore',
					description: 'Approve restoration requests for archived folders'
				}
			}
		},
		system: {
			name: 'System Admin',
			permissions: {
				'view_users': {
					label: 'View Users',
					description: 'View user accounts and roles'
				},
				'create_users': {
					label: 'Create Users',
					description: 'Create new user accounts'
				},
				'update_users': {
					label: 'Update Users',
					description: 'Edit user details and role assignments'
				},
				'delete_users': {
					label: 'Delete Users',
					description: 'Deactivate or remove user accounts'
				},
				'manage_system_config': {
					label: 'Config',
					description: 'Configure system settings and parameters'
				},
				'view_audit_logs': {
					label: 'Audit Logs',
					description: 'View system activity and change logs'
				},
				'override': {
					label: 'Override',
					description: 'Override locked workflows and user restrictions'
				}
			}
		}
	};

	const roles = <?= json_encode($roles) ?>;
	let currentTab = 'document';
	let matrixData = {};
	let hasUnsavedChanges = false;

	// Initialize matrix data structure
	function initializeMatrixData() {
		<?php foreach ($roles as $role): ?>
		matrixData[<?= $role['role_id'] ?>] = {};
		<?php endforeach; ?>
	}

	// Load role permissions from server
	function loadRolePermissions() {
		const promises = roles.map(role => {
			return $.ajax({
				url: `/permissions/load-role/${role.role_id}`,
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					if (data.error) {
						console.error(`Error loading role ${role.role_id}: ${data.error}`);
						matrixData[role.role_id] = {};
					} else {
						matrixData[role.role_id] = data || {};
						console.log(`Loaded permissions for role ${role.role_id}:`, matrixData[role.role_id]);
					}
				},
				error: function(xhr, status, error) {
					console.error(`Failed to load role ${role.role_id}: ${status} ${error}`, xhr.responseJSON);
					matrixData[role.role_id] = {};
				}
			});
		});

		// Wait for all AJAX calls to complete, then render
		$.when(...promises).done(function() {
			console.log('All permissions loaded, rendering matrix:', matrixData);
			renderMatrix(currentTab);
			hasUnsavedChanges = false;
			$('#saveBar').addClass('hidden');
		});
	}

	// Show save bar when changes detected
	function onCheckboxChange() {
		hasUnsavedChanges = true;
		$('#saveBar').removeClass('hidden');
	}

	// Toggle all checkboxes in a column
	function toggleColumnCheckboxes(permKey, newState) {
		const isSuperAdmin = roles.some(r => r.role_name === 'super_admin');
		$(`input.perm-checkbox[data-perm="${permKey}"]:not(:disabled)`).each(function() {
			this.checked = newState;
			onCheckboxChange();
		});
	}

	// Render matrix table
	function renderMatrix(tab) {
		// Check if matrix needs initialization
		if ($('#matrixBody').find('tr').length === 0) {
			let headerHtml = '<tr class="bg-gray-50 border-b border-gray-300"><th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 w-1/5 sticky left-0 bg-gray-50 z-10">Role</th>';
			let bodyHtml = '';

			// Generate header columns for ALL tabs at once with tooltips and select-all
			Object.entries(permissionGroups).forEach(([tabKey, tabGroup]) => {
				Object.entries(tabGroup.permissions).forEach(([key, perm]) => {
					const tooltipText = perm.description.replace(/"/g, '&quot;');
					headerHtml += `<th class="px-4 py-4 text-center text-sm font-semibold text-gray-900 whitespace-nowrap relative group" 
						data-tab-group="${tabKey}" data-perm="${key}" 
						style="display: ${tabKey === 'document' ? 'table-cell' : 'none'};">
						<div class="flex items-center justify-center gap-2">
							<span class="text-xs font-medium text-gray-900">${perm.label}</span>
							<svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<div class="hidden group-hover:block absolute left-0 z-50 w-64 px-3 py-2 text-xs text-white bg-gray-900 rounded-lg bottom-full mb-2 pointer-events-none">
							${tooltipText}
							<div class="absolute left-4 top-full w-2 h-2 bg-gray-900 transform rotate-45"></div>
						</div>
					</th>`;
				});
			});
			headerHtml += '</tr>';

			// Generate body rows for ALL permissions at once
			roles.forEach(role => {
				const isSuperAdmin = role.role_name === 'super_admin';
				bodyHtml += `<tr class="border-b border-gray-200 hover:bg-gray-50">
					<td class="px-6 py-4 font-medium text-gray-900 sticky left-0 bg-white group-hover:bg-gray-50 z-10">
						${role.role_name}
						${isSuperAdmin ? '<span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-2">All Access</span>' : ''}
					</td>`;
				
				Object.entries(permissionGroups).forEach(([tabKey, tabGroup]) => {
					Object.entries(tabGroup.permissions).forEach(([key, perm]) => {
						const isChecked = isSuperAdmin || (matrixData[role.role_id] && matrixData[role.role_id][key]);
						const disabledAttr = isSuperAdmin ? 'disabled' : '';
						const cursorClass = isSuperAdmin ? 'cursor-not-allowed opacity-60' : 'cursor-pointer';
						
						bodyHtml += `<td class="px-4 py-4 text-center tab-cell bg-white" data-tab-group="${tabKey}" style="display: ${tabKey === 'document' ? 'table-cell' : 'none'};">
							<input type="checkbox" class="perm-checkbox w-5 h-5 text-blue-600 rounded ${cursorClass}" 
								data-role="${role.role_id}" data-perm="${key}" ${isChecked ? 'checked' : ''} ${disabledAttr} 
								title="${isSuperAdmin ? 'Super admin has all permissions' : ''}"
								${!isSuperAdmin ? 'onchange="onCheckboxChange()"' : ''}>
						</td>`;
					});
				});
				
				bodyHtml += '</tr>';
			});

			$('#matrixHead').html(headerHtml);
			$('#matrixBody').html(bodyHtml);
		}

		// Show/hide columns based on selected tab
		$('#matrixHead th[data-tab-group], #matrixBody .tab-cell').hide();
		$('#matrixHead th:first-child').show(); // Always show role column
		$('#matrixHead th[data-tab-group="' + tab + '"]').show(); // Show only current tab headers
		$('#matrixBody .tab-cell[data-tab-group="' + tab + '"]').show(); // Show only current tab cells
	}

	// Handle tab switching
	$(document).on('click', '.tab-button', function() {
		$('.tab-button').removeClass('border-blue-600 text-blue-600').addClass('border-transparent text-gray-600');
		$(this).removeClass('border-transparent text-gray-600').addClass('border-blue-600 text-blue-600');
		currentTab = $(this).data('tab');
		renderMatrix(currentTab);
	});

	// Save changes
	$('#saveBtn').click(function() {
		const changes = {};
		
		// Get all roles from table
		const roles = <?= json_encode(array_column($roles, 'role_id')) ?>;
		const superAdminCheck = <?= json_encode(array_column($roles, 'role_id', 'role_name')) ?>;
		
		// Collect checked permissions for each role (skip super_admin)
		roles.forEach(roleId => {
			// Skip super_admin role
			const isSuperAdmin = Object.keys(superAdminCheck).some(name => name === 'super_admin' && superAdminCheck[name] === roleId);
			if (!isSuperAdmin) {
				changes[roleId] = [];
			}
		});
		
		// Get all checked permissions (exclude super_admin)
		$('.perm-checkbox:checked').each(function() {
			const roleId = $(this).data('role');
			const perm = $(this).data('perm');
			// Only collect from non-super_admin roles
			if (changes.hasOwnProperty(roleId)) {
				changes[roleId].push(perm);
			}
		});

		console.log('Sending save request with data:', changes);

		showAppConfirm('Save permission changes?', function() {
			$.ajax({
				url: '/permissions/save-role-perms',
				type: 'POST',
				data: JSON.stringify(changes),
				contentType: 'application/json',
				dataType: 'json',
				statusCode: {
					400: function(xhr) {
						console.error('Bad request (400):', xhr.responseJSON);
						showAppAlert('Error: ' + (xhr.responseJSON?.error || 'Invalid data'));
					},
					403: function(xhr) {
						console.error('Forbidden (403):', xhr.responseJSON);
						showAppAlert('Error: You do not have permission to manage users');
					},
					500: function(xhr) {
						console.error('Server error (500):', xhr.responseJSON);
						showAppAlert('Error: ' + (xhr.responseJSON?.error || 'Server error'));
					}
				},
				success: function(response) {
					console.log('Response received:', response);
					
					if (response.success) {
						hasUnsavedChanges = false;
						$('#savePendingState').hide();
						$('#saveSuccessState').removeClass('hidden').css('display', 'flex');
						$('#saveBar').removeClass('hidden');

						// Wait 500ms for database to fully commit before reloading
						setTimeout(() => {
							console.log('Refreshing role permissions...');
							matrixData = {}; // Reset cache
							initializeMatrixData();
							loadRolePermissions(); // This will render the matrix once all data is loaded
						}, 500);

						setTimeout(() => {
							$('#saveBar').addClass('hidden');
							$('#saveSuccessState').hide();
							$('#savePendingState').show();
						}, 3000);
					} else {
						showAppAlert('Error: ' + (response.error || 'Failed to save permissions'));
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX error - status:', xhr.status, 'error:', error);
					console.error('Response:', xhr.responseJSON || xhr.responseText);
					
					// If statusCode handlers didn't catch it
					if (!xhr.responseJSON || !xhr.responseJSON.error) {
						showAppAlert('Error: ' + (error || 'Failed to save permissions'));
					}
				}
			});
		});
	});

	// Reset
	$('#resetBtn').click(function() {
		hasUnsavedChanges = false;
		$('#saveBar').addClass('hidden');
		$('#saveSuccessState').hide();
		$('#savePendingState').show();
		matrixData = {}; // Clear cache
		initializeMatrixData();
		loadRolePermissions(); // This now waits for all data before rendering
	});

	// Initialize on page load
	$(document).ready(function() {
		console.log('Initializing permission matrix...');
		initializeMatrixData();
		loadRolePermissions(); // This will render the matrix once all data is loaded
	});
</script>
<?= $this->endSection() ?>
