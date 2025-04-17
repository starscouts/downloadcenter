<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Minteck Download Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <br><br>
    <div class="container">
        <h1>Minteck Download Center</h1>
        <p>Welcome to the Minteck Download Center! This website is where you can download compiled versions of all the projects I work on. Note that these builds are compiled automatically and may be non-functional or buggy. Use at your own risk!</p>
        <p><b>Available Projects:</b></p>

        <div class="list-group">
            <?php
            $projects = array_map(function ($id) {
                return json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/fetcher/projects/" . $id), true);
            }, array_filter(scandir($_SERVER['DOCUMENT_ROOT'] . "/fetcher/projects"), function ($i) {
                return str_ends_with($i, ".json");
            }));
            foreach ($projects as $project): ?>
            <a href="/channels/?<?= $project['id'] ?>" class="list-group-item list-group-item-action"><?= $project['name'] ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <br>
</body>
</html>