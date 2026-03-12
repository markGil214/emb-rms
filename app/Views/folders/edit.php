<div class="container mx-auto p-6">
    <h2 class="text-3xl font-bold mb-6"><?= $title ?></h2>

    <form action="<?= route_to('records.update', $folder['folder_id']) ?>" method="POST" class="max-w-2xl">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">

        <div class="mb-4">
            <label for="file_code" class="block text-gray-700 font-bold mb-2">File Code</label>
            <input type="text" id="file_code" name="file_code" 
                value="<?= esc($folder['file_code']) ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded bg-gray-100" readonly>
        </div>

        <div class="mb-4">
            <label for="company_name" class="block text-gray-700 font-bold mb-2">Company Name *</label>
            <input type="text" id="company_name" name="company_name" 
                value="<?= old('company_name', $folder['company_name']) ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded" required>
            <?php if (isset($errors['company_name'])): ?>
                <span class="text-red-600 text-sm"><?= $errors['company_name'] ?></span>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="issuance_date" class="block text-gray-700 font-bold mb-2">Issuance Date *</label>
            <input type="date" id="issuance_date" name="issuance_date" 
                value="<?= old('issuance_date', $folder['issuance_date']) ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded" required>
            <?php if (isset($errors['issuance_date'])): ?>
                <span class="text-red-600 text-sm"><?= $errors['issuance_date'] ?></span>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="expiry_date" class="block text-gray-700 font-bold mb-2">Expiry Date *</label>
            <input type="date" id="expiry_date" name="expiry_date" 
                value="<?= old('expiry_date', $folder['expiry_date']) ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded" required>
            <?php if (isset($errors['expiry_date'])): ?>
                <span class="text-red-600 text-sm"><?= $errors['expiry_date'] ?></span>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="location_code" class="block text-gray-700 font-bold mb-2">Location Code</label>
            <input type="text" id="location_code" name="location_code" 
                value="<?= esc($folder['location_code']) ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded bg-gray-100" readonly>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="cabinet" class="block text-gray-700 font-bold mb-2">Cabinet</label>
                <input type="text" id="cabinet" name="cabinet" 
                    value="<?= old('cabinet') ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded"
                    placeholder="Change if needed">
            </div>

            <div>
                <label for="rack" class="block text-gray-700 font-bold mb-2">Rack</label>
                <input type="text" id="rack" name="rack" 
                    value="<?= old('rack') ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded"
                    placeholder="Change if needed">
            </div>
        </div>

        <div class="mb-4">
            <label for="status" class="block text-gray-700 font-bold mb-2">Status</label>
            <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded">
                <option value="Available" <?= $folder['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                <option value="Borrowed" <?= $folder['status'] === 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>
                <option value="Archived" <?= $folder['status'] === 'Archived' ? 'selected' : '' ?>>Archived</option>
                <option value="Disposed" <?= $folder['status'] === 'Disposed' ? 'selected' : '' ?>>Disposed</option>
            </select>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Update Record</button>
            <a href="<?= route_to('records.show', $folder['folder_id']) ?>" class="bg-gray-400 text-white px-6 py-2 rounded">Cancel</a>
        </div>
    </form>
</div>
