<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Supman</title>

         <?php echo app('Illuminate\Foundation\Vite')(['resources/css/flatpickr.min.css', 'resources/css/app.css', 'resources/js/app.js']); ?>
         <?php echo \Livewire\Livewire::styles(); ?>

    </head>

    <body>
        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('question-search')->html();
} elseif ($_instance->childHasBeenRendered('P1tSYs1')) {
    $componentId = $_instance->getRenderedChildComponentId('P1tSYs1');
    $componentTag = $_instance->getRenderedChildComponentTagName('P1tSYs1');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('P1tSYs1');
} else {
    $response = \Livewire\Livewire::mount('question-search');
    $html = $response->html();
    $_instance->logRenderedChild('P1tSYs1', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>

        <?php echo \Livewire\Livewire::scripts(); ?>

    </body>

</html><?php /**PATH /home/md/dev/supman-docker/src/resources/views/welcome.blade.php ENDPATH**/ ?>