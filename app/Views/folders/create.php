<div class="container mx-auto p-6">
    <h2 class="text-3xl font-bold mb-6"><?= $title ?></h2>

    <form action="<?= route_to('records.store') ?>" method="POST" class="max-w-2xl">
        <?= csrf_field() ?>

        <div class="mb-4">
            <label for="company_name" class="block text-gray-700 font-bold mb-2">Company Name *</label>
            <input type="text" id="company_name" name="company_name" 
                value="<?= old('company_name') ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded" required>
            <?php if (isset($errors['company_name'])): ?>
                <span class="text-red-600 text-sm"><?= $errors['company_name'] ?></span>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="issuance_date" class="block text-gray-700 font-bold mb-2">Issuance Date *</label>
            <input type="date" id="issuance_date" name="issuance_date" 
                value="<?= old('issuance_date') ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded" required>
            <?php if (isset($errors['issuance_date'])): ?>
                <span class="text-red-600 text-sm"><?= $errors['issuance_date'] ?></span>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="expiry_date" class="block text-gray-700 font-bold mb-2">Expiry Date *</label>
            <input type="date" id="expiry_date" name="expiry_date" 
                value="<?= old('expiry_date') ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded" required>
            <?php if (isset($errors['expiry_date'])): ?>
                <span class="text-red-600 text-sm"><?= $errors['expiry_date'] ?></span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="cabinet" class="block text-gray-700 font-bold mb-2">Cabinet *</label>
                <input type="text" id="cabinet" name="cabinet" 
                    value="<?= old('cabinet') ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded" required>
                <?php if (isset($errors['cabinet'])): ?>
                    <span class="text-red-600 text-sm"><?= $errors['cabinet'] ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="rack" class="block text-gray-700 font-bold mb-2">Rack *</label>
                <input type="text" id="rack" name="rack" 
                    value="<?= old('rack') ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded" required>
                <?php if (isset($errors['rack'])): ?>
                    <span class="text-red-600 text-sm"><?= $errors['rack'] ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-4">
            <label for="location_id" class="block text-gray-700 font-bold mb-2">Location *</label>
            <input type="number" id="location_id" name="location_id" 
                value="<?= old('location_id') ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded" required>
            <?php if (isset($errors['location_id'])): ?>
                <span class="text-red-600 text-sm"><?= $errors['location_id'] ?></span>
            <?php endif; ?>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Create Record</button>
            <a href="<?= route_to('records') ?>" class="bg-gray-400 text-white px-6 py-2 rounded">Cancel</a>
        </div>
    </form>
</div>
