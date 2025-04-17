<?php

function timeAgo($time): string {
    if (!is_numeric($time)) {
        $time = strtotime($time);
    }

    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "age");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "100");

    $now = time();

    $difference = $now - $time;
    if ($difference <= 10 && $difference >= 0) {
        return $tense = 'just now';
    } elseif ($difference > 0) {
        $tense = 'ago';
    } else {
        $tense = 'later';
    }

    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }

    $difference = round($difference);

    $period =  $periods[$j] . ($difference >1 ? 's' :'');
    return "{$difference} {$period} {$tense} ";
}

if (count(array_keys($_GET)) < 2) {
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

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $channel['slug'] ?> | <?= $project['name'] ?> | Minteck Download Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <br><br>
    <div class="container">
        <?php if (count($project['channels']) < 2): ?>
        <a class="small" href="/">← Go back to projects list</a>
        <?php else: ?>
        <a class="small" href="/channels/?<?= $project['id'] ?>">← Go back to channels list</a>
        <?php endif; ?>
        <h1>Minteck Download Center</h1>
        <h2><?= $channel['slug'] ?> | <?= $project['name'] ?></h2>

        <div class="list-group">
            <?php foreach ($channel['builds'] as $build): ?>
            <a href="/build/?<?= $project['id'] ?>&<?= $channel['id'] ?>&<?= $build['id'] ?>" class="list-group-item list-group-item-action">#<?= $build['localId'] ?><span class="text-muted"> · deployed <?= timeAgo($build['date']) ?> · <?= count($build['artifacts']) ?> file<?= count($build['artifacts']) === 1 ? "" : "s" ?></span></a>
            <?php endforeach; ?>
        </div>
    </div>
    <br>
</body>
</html>