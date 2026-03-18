$(document).ready(function() {
	let selectedUserId = null;
	let originalPermissions = [];
	let originalRoleId = null;

	// User selection
	$(document).on('click', '.user-item', function() {
		selectedUserId = $(this).data('user-id');
		loadUserPermissions(selectedUserId);
		$('.user-item').removeClass('bg-blue-100 border-l-4 border-blue-500');
		$(this).addClass('bg-blue-100 border-l-4 border-blue-500');
	});

	// Load user permissions
	function loadUserPermissions(userId) {
		$('#loadingSpinner').show();
		$('#permissionsContent').hide();

		$.ajax({
			url: `/admin/permissions/load/${userId}`,
			type: 'POST',
			dataType: 'json',
			success: function(data) {
				populatePermissions(data);
				$('#loadingSpinner').hide();
				$('#permissionsContent').show();
				$('#noUserSelected').hide();
			},
			error: function() {
				alert('Error loading user permissions');
				$('#loadingSpinner').hide();
			}
		});
	}

	// Populate permissions form
	function populatePermissions(data) {
		const user = data.user;
		originalRoleId = data.roleId;
		originalPermissions = data.allPermissions;

		// Set user info
		$('#selectedUserName').text(user.username);
		$('#selectedUserEmail').text(user.email);
		$('#userRoleBadge').text(user.role);

		// Set role selection
		$('.role-radio').prop('checked', false);
		if (originalRoleId) {
			$(`#role_${originalRoleId}`).prop('checked', true);
		}

		// Set permission checkboxes
		$('.permission-checkbox').prop('checked', false).prop('disabled', false).removeClass('opacity-50');
		data.allPermissions.forEach(perm => {
			$(`#perm_${perm}`).prop('checked', true);
		});

		// Gray out inherited permissions
		data.rolePermissions.forEach(perm => {
			$(`#perm_${perm}`).prop('disabled', true).addClass('opacity-50');
		});
	}

	// Save permissions
	$('#saveBtn').click(function() {
		const roleId = $('input[name="roleId"]:checked').val();
		const permissions = $('.permission-checkbox:checked').map(function() {
			return $(this).val();
		}).get();

		if (confirm('Save permission changes?')) {
			$.ajax({
				url: `/admin/permissions/save/${selectedUserId}`,
				type: 'POST',
				data: {
					role_id: roleId,
					permissions: permissions
				},
				dataType: 'json',
				success: function(response) {
					alert('Permissions saved successfully!');
					loadUserPermissions(selectedUserId);
				},
				error: function() {
					alert('Error saving permissions');
				}
			});
		}
	});

	// Reset form
	$('#resetBtn').click(function() {
		loadUserPermissions(selectedUserId);
	});

	// Assign all permissions
	$('#assignAllBtn').click(function() {
		if (confirm('Assign ALL permissions to this user?')) {
			$('.permission-checkbox:not(:disabled)').prop('checked', true);
		}
	});

	// Remove all permissions
	$('#removeAllBtn').click(function() {
		if (confirm('Remove ALL permissions from this user? This will lock them out!')) {
			$('.permission-checkbox:not(:disabled)').prop('checked', false);
		}
	});

	// View history
	$('#viewHistoryBtn').click(function(e) {
		e.preventDefault();
		loadHistory(selectedUserId);
	});

	// Load permission history
	function loadHistory(userId) {
		$.ajax({
			url: `/admin/permissions/history/${userId}`,
			type: 'GET',
			dataType: 'json',
			success: function(data) {
				let html = '<table class="w-full"><thead><tr class="border-b"><th class="text-left p-2">Date</th><th class="text-left p-2">Admin</th><th class="text-left p-2">Action</th></tr></thead><tbody>';
				
				if (data.length === 0) {
					html += '<tr><td colspan="3" class="p-2">No history found</td></tr>';
				} else {
					data.forEach(entry => {
						const date = new Date(entry.created_at).toLocaleString();
						html += `<tr class="border-b"><td class="p-2">${date}</td><td class="p-2">${entry.admin_name || 'System'}</td><td class="p-2">${entry.action_type.replace('_', ' ')}</td></tr>`;
					});
				}
				
				html += '</tbody></table>';
				$('#historyContent').html(html);
				document.getElementById('historyModal').classList.remove('hidden');
			}
		});
	}

	// User search
	$('#userSearch').on('keyup', function() {
		const query = $(this).val();
		if (query.length < 2) {
			// Show all users
			$('.user-item').show();
			return;
		}

		$.ajax({
			url: '/admin/permissions/search',
			type: 'GET',
			data: { q: query },
			dataType: 'json',
			success: function(data) {
				$('#userList').html('');
				data.forEach(user => {
					const html = `
						<div class="user-item cursor-pointer p-3 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200" data-user-id="${user.user_id}">
							<div class="font-semibold text-gray-900">${user.username}</div>
							<div class="text-sm text-gray-600">${user.email}</div>
							<div class="mt-2">
								<span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
									${user.role}
								</span>
							</div>
						</div>
					`;
					$('#userList').append(html);
				});
			}
		});
	});
});
