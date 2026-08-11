<?php
/**
 * Action Button Component
 * 
 * @var string $label    Button text
 * @var string $type     'link' or 'submit'
 * @var string $style    'primary', 'secondary', or 'danger'
 * @var string $url      URL for link type
 * @var string $action   Form action for submit type
 * @var string $confirm  Optional confirmation message
 * @var string $class    Additional CSS classes
 * @var string $title    Title attribute
 * @var string $bulkAction Optional bulk-action key ('approve', 'reject',
 *                         'restore'), tagged onto the generated form so
 *                         bulk-selection toolbars can find and submit it
 */

$style = $style ?? 'secondary';
$type = $type ?? 'link';
$class = $class ?? '';
$title = $title ?? '';
$label = $label ?? 'Button';
$url = $url ?? '#';
$action = $action ?? '#';
$confirm = $confirm ?? null;
$bulkAction = $bulkAction ?? null;

$baseClass = "disposal-action disposal-action--{$style} {$class}";
$confirmAttr = !empty($confirm) ? 'data-confirm-message="' . esc($confirm, 'attr') . '"' : '';
$bulkActionAttr = !empty($bulkAction) ? 'data-bulk-action="' . esc($bulkAction, 'attr') . '"' : '';
?>

<?php if ($type === 'link'): ?>
    <a href="<?= $url ?? '#' ?>" class="<?= $baseClass ?>" <?= $confirmAttr ?> title="<?= esc($title, 'attr') ?>">
        <?= esc($label) ?>
    </a>
<?php else: ?>
    <form action="<?= $action ?? '#' ?>" method="POST" style="display:inline;" <?= $confirmAttr ?> <?= $bulkActionAttr ?>>
        <?= csrf_field() ?>
        <button type="submit" class="<?= $baseClass ?>" title="<?= esc($title, 'attr') ?>">
            <?= esc($label) ?>
        </button>
    </form>
<?php endif; ?>
