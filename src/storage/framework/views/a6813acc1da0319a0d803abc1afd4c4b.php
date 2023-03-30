<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Supman</title>
            <link rel="icon" type="image/png" href="<?php echo e(asset('favicon.png')); ?>" sizes="32x32">
            <?php echo app('Illuminate\Foundation\Vite')(['resources/css/flatpickr.min.css', 'resources/css/app.css', 'resources/js/app.js']); ?>
            <?php echo \Livewire\Livewire::styles(); ?>

    </head>

    <body>
        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('question-search')->html();
} elseif ($_instance->childHasBeenRendered('sE1omui')) {
    $componentId = $_instance->getRenderedChildComponentId('sE1omui');
    $componentTag = $_instance->getRenderedChildComponentTagName('sE1omui');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('sE1omui');
} else {
    $response = \Livewire\Livewire::mount('question-search');
    $html = $response->html();
    $_instance->logRenderedChild('sE1omui', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>

        <?php echo \Livewire\Livewire::scripts(); ?>

    </body>

</html><?php /**PATH /home/md/dev/supman-docker/src/resources/views/welcome.blade.php ENDPATH**/ ?>