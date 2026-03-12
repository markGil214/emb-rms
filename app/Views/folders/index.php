<div class="container mx-auto p-6">
    <h2 class="text-3xl font-bold mb-6">Folders</h2>

    <a href="<?= route_to('records.create') ?>" class="bg-blue-600 text-white px-4 py-2 rounded mb-6 inline-block">
        + Create Folder
    </a>

    <?php if (empty($folders)): ?>
        <p class="text-gray-600">No folders found.</p>
    <?php else: ?>
        <table class="w-full border border-gray-300 border-collapse">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-4 py-2">File Code</th>
                    <th class="border px-4 py-2">Location Code</th>
                    <th class="border px-4 py-2">Company</th>
                    <th class="border px-4 py-2">Status</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($folders as $folder): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="border px-4 py-2 font-semibold"><?= esc($folder['file_code']) ?></td>
                        <td class="border px-4 py-2"><?= esc($folder['location_code']) ?></td>
                        <td class="border px-4 py-2"><?= esc($folder['company_name']) ?></td>
                        <td class="border px-4 py-2">
                            <?php
                            if ($folder['status'] === 'Available') {
                                $statusColor = 'bg-green-200 text-green-800';
                            } elseif ($folder['status'] === 'Archived') {
                                $statusColor = 'bg-gray-200 text-gray-800';
                            } else {
                                $statusColor = 'bg-yellow-200 text-yellow-800';
                            }
                            ?>
                            <span class="px-2 py-1 rounded <?= $statusColor ?>">
                                <?= esc($folder['status']) ?>
                            </span>
                        </td>
                        <td class="border px-4 py-2 space-x-2">
                            <a href="<?= route_to('records.show', $folder['folder_id']) ?>"
                                class="text-blue-600 underline">View</a>
                            <a href="<?= route_to('records.edit', $folder['folder_id']) ?>"
                                class="text-yellow-600 underline">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>