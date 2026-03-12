<div class="container mx-auto p-6">
    <h2 class="text-3xl font-bold mb-6"><?= $title ?></h2>

    <!-- Folder Details -->
    <div class="max-w-2xl bg-white border border-gray-300 rounded p-6 mb-6">
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">File Code</label>
            <p class="text-lg font-semibold"><?= esc($folder['file_code']) ?></p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Company Name</label>
            <p><?= esc($folder['company_name']) ?></p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Location Code</label>
            <p><?= esc($folder['location_code']) ?></p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Issuance Date</label>
                <p><?= esc($folder['issuance_date']) ?></p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Expiry Date</label>
                <p><?= esc($folder['expiry_date']) ?></p>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Status</label>
            <?php
            if ($folder['status'] === 'Available') {
                $statusColor = 'bg-green-200 text-green-800';
            } elseif ($folder['status'] === 'Archived') {
                $statusColor = 'bg-gray-200 text-gray-800';
            } else {
                $statusColor = 'bg-yellow-200 text-yellow-800';
            }
            ?>
            <span class="px-3 py-1 rounded <?= $statusColor ?>">
                <?= esc($folder['status']) ?>
            </span>
        </div>

        <div class="flex gap-4">
            <a href="<?= route_to('records.edit', $folder['folder_id']) ?>" class="bg-yellow-600 text-white px-6 py-2 rounded">Edit</a>
            <a href="<?= route_to('records') ?>" class="bg-gray-400 text-white px-6 py-2 rounded">Back to Records</a>
        </div>
    </div>

    <!-- File Upload Section -->
    <div class="max-w-2xl bg-white border border-gray-300 rounded p-6 mb-6">
        <h3 class="text-2xl font-bold mb-4">Upload Document</h3>
        
        <form action="<?= route_to('file.upload', $folder['folder_id']) ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="mb-4">
                <label for="pdf_file" class="block text-gray-700 font-bold mb-2">PDF File *</label>
                <input type="file" id="pdf_file" name="pdf_file" 
                    accept=".pdf" 
                    class="w-full px-4 py-2 border border-gray-300 rounded" required>
                <p class="text-gray-600 text-sm mt-1">Maximum file size: 10MB</p>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Upload PDF</button>
        </form>
    </div>

    <!-- Uploaded Files List -->
    <div class="max-w-2xl bg-white border border-gray-300 rounded p-6">
        <h3 class="text-2xl font-bold mb-4">Uploaded Documents</h3>
        
        <?php if (empty($files)): ?>
            <p class="text-gray-600">No documents uploaded yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border px-4 py-2 text-left">File Name</th>
                            <th class="border px-4 py-2 text-left">Size</th>
                            <th class="border px-4 py-2 text-left">Uploaded</th>
                            <th class="border px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border px-4 py-2"><?= esc($file['file_name']) ?></td>
                                <td class="border px-4 py-2"><?= \App\Models\FolderFileModel::formatFileSize($file['file_size']) ?></td>
                                <td class="border px-4 py-2"><?= date('M d, Y H:i', strtotime($file['created_at'])) ?></td>
                                <td class="border px-4 py-2 space-x-2">
                                    <a href="<?= route_to('file.download', $file['file_id']) ?>" 
                                        class="text-blue-600 underline">Download</a>
                                    <form action="<?= route_to('file.delete', $file['file_id']) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Delete this file?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="text-red-600 underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

