<?php

if (count(array_keys($_GET)) < 1) {
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

if (count($project['channels']) < 2) {
    header("Location: /builds/?" . $project['id'] . "&" . $project['channels'][0]['id']) and die;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $project['name'] ?> | Minteck Download Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <br><br>
    <div class="container">
        <a class="small" href="/">← Go back to projects list</a>
        <h1>Minteck Download Center</h1>
        <h2><?= $project['name'] ?></h2>
        <?php if (isset($project['description'])): ?>
        <p><?= $project['description'] ?></p>
        <?php endif; ?>
        <p>This project has multiple deployment channels, select the one you want to view builds.</p>
        <p><b>Available Deployment Channels:</b></p>

        <div class="list-group">
            <?php foreach ($project['channels'] as $branch): ?>
            <a href="/builds/?<?= $project['id'] ?>&<?= $branch['id'] ?>" class="list-group-item list-group-item-action"><?= $branch['slug'] ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <br>
</body>
</html>