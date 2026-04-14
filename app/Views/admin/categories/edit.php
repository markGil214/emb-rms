<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
</head>
<body>
    <?php $session = session(); $errors = $session->getFlashdata('errors') ?? []; ?>

    <h1>Edit Category</h1>

    <?php if ($session->getFlashdata('error')): ?>
        <p><?= esc($session->getFlashdata('error')) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $message): ?>
                <li><?= esc($message) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="<?= route_to('admin.categories.update', $category['category_id']) ?>" method="POST">
        <?= csrf_field() ?>

        <p>
            <label for="category_name">Category Name</label><br>
            <input type="text" id="category_name" name="category_name" value="<?= esc(old('category_name', $category['category_name'])) ?>">
        </p>

        <p>
            <button type="submit">Update Category</button>
            <a href="<?= route_to('admin.categories') ?>">Cancel</a>
        </p>
    </form>
</body>
</html>
