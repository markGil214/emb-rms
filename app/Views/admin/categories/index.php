<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>
</head>
<body>
    <?php $session = session(); ?>

    <?php if ($session->getFlashdata('success')): ?>
        <p><?= esc($session->getFlashdata('success')) ?></p>
    <?php endif; ?>

    <?php if ($session->getFlashdata('error')): ?>
        <p><?= esc($session->getFlashdata('error')) ?></p>
    <?php endif; ?>

    <h1>Category Management</h1>
    <p>Manage document categories.</p>

    <p><a href="<?= route_to('admin.categories.create') ?>">Add Category</a></p>
    <p><a href="<?= base_url('document-records') ?>">Back to Document Records</a></p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Category Name</th>
                <th>Created By</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="4">No categories found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= esc($category['category_name']) ?></td>
                        <td><?= esc($category['created_by_name'] ?? '-') ?></td>
                        <td><?= !empty($category['created_at']) ? date('M d, Y', strtotime($category['created_at'])) : '-' ?></td>
                        <td>
                            <a href="<?= route_to('admin.categories.edit', $category['category_id']) ?>">Edit</a>
                            |
                            <form action="<?= route_to('admin.categories.delete', $category['category_id']) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                <?= csrf_field() ?>
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
