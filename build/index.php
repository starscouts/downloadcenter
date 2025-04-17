<?php

if (count(array_keys($_GET)) < 3) {
    header("Location: /") and die;
}

$projects = array_map(function ($id) {
    return json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/fetcher/projects/" . $id), true);
}, array_filter(scandir($_SERVER['DOCUMENT_ROOT'] . "/fetcher/projects"), function ($i) {
    return str_ends_with($i, ".json");
}));

$projectsList = array_map(function ($project) {
    return $project['id'];
}, $projects);

$sel = trim(array_keys($_GET)[0]);
if (!in_array($sel, $projectsList)) {
    header("Location: /") and die;
}

$project = array_filter($projects, function ($i) use ($sel) {
    return $i['id'] === $sel;
});
sort($project);
$project = $project[0];

$branchList = array_map(function ($i) {
    return $i['id'];
}, $project['channels']);

$selb = trim(array_keys($_GET)[1]);
if (!in_array($selb, $branchList)) {
    header("Location: /") and die;
}

$channel = array_filter($project['channels'], function ($i) use ($selb) {
    return $i['id'] === $selb;
});
sort($channel);
$channel = $channel[0];

$buildsList = array_map(function ($i) {
    return $i['id'];
}, $channel['builds']);

$selc = trim(array_keys($_GET)[2]);
if (!in_array($selc, $buildsList)) {
    header("Location: /") and die;
}

$build = array_filter($channel['builds'], function ($i) use ($selc) {
    return $i['id'] == $selc;
});
sort($build);
$build = $build[0];

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>#<?= $build['localId'] ?> | <?= $channel['slug'] ?> | <?= $project['name'] ?> | Minteck Download Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <br><br>
    <div class="container">
        <a class="small" href="/builds/?<?= $project['id'] ?>&<?= $channel['id'] ?>">← Go back to builds list</a>
        <h1>Minteck Download Center</h1>
        <h2>#<?= $build['localId'] ?> | <?= $channel['slug'] ?> | <?= $project['name'] ?></h2>

        <div class="list-group">
            <?php foreach ($build['artifacts'] as $artifact): ?>
            <a href="/download/?<?= $project['id'] ?>&<?= $channel['id'] ?>&<?= $build['id'] ?>&_=<?= $artifact['name'] ?>" class="list-group-item list-group-item-action"><?= $artifact['name'] ?><span class="text-muted"> · <?php

                    $size = $artifact['size'];

                    if ($size > 1024) {
                        if ($size > 1024**2) {
                            if ($size > 1024**3) {
                                echo(round($size/1024**3, 2) . " GiB");
                            } else {
                                echo(round($size/1024**2, 2) . " MiB");
                            }
                        } else {
                            echo(round($size/1024, 2) . " KiB");
                        }
                    } else {
                        echo(round($size, 2) . " bytes");
                    }

                    ?></span></a>
            <?php endforeach; ?>
            <?php if (count($build['artifacts']) === 0): ?>
            <p><i>This build doesn't have any output files.</i></p>
            <?php endif; ?>
        </div>
    </div>
    <br>
</body>
</html>