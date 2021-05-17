<!doctype html>
<html class="theme-<?=$config['theme']?>">
<!--
<?=$throwableString?>
-->
<head>
    <!-- Hide dumps asap -->
    <style>
        pre.sf-dump {
            display: none !important;
        }
    </style>

    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">

    <title><?= $title ?></title>

    <?php foreach ($styles as $script): ?>
        <link rel="stylesheet" href="<?=$housekeepingEndpoint?>/styles/<?=$script?>">
    <?php endforeach; ?>

</head>
<body class="scrollbar-lg">

<script>
    window.data = <?=
        $jsonEncode([
            'report' => $report,
            'config' => $config,
            'solutions' => $solutions,
            'telescopeUrl' => $telescopeUrl,
            'shareEndpoint' => $shareEndpoint,
            'defaultTab' => $defaultTab,
            'defaultTabProps' => $defaultTabProps,
            'appEnv' => $appEnv,
            'appDebug' => $appDebug,
        ])
    ?>;
</script>

<noscript><pre><?=$throwableString?></pre></noscript>

<div id="app"></div>

<style><?= $getAssetContents('ignition.css') ?></style>
<script><?= $getAssetContents('ignition.js') ?></script>
<script>
    window.ignite(window.data);
</script>
<!--
<?=$throwableString?>
-->
</body>
</html>
