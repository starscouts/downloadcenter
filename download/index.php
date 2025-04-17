<?php

if (count(array_keys($_GET)) < 4 || !isset($_GET['_'])) {
    die("Missing operand");
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
    die("Invalid project");
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
    die("Invalid channel");
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
    die("Invalid build ID");
}

$build = array_filter($channel['builds'], function ($i) use ($selc) {
    return $i['id'] == $selc;
});
sort($build);
$build = $build[0];

$artifactsList = array_map(function ($i) {
    return $i['name'];
}, $build['artifacts']);

$sela = trim($_GET['_']);
if (!in_array($sela, $artifactsList)) {
    die("Invalid artifact name");
}

$artifact = array_filter($build['artifacts'], function ($i) use ($sela) {
    return $i['name'] == $sela;
});
sort($artifact);
$artifact = $artifact[0];

$url = $artifact["download"];
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [ "Authorization: Bearer " . trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/fetcher/token.txt")) ]);

$response = array_map(function ($i) {
    $p = explode(":", trim($i));
    $p1 = $p[0];
    array_shift($p);
    return [$p1, trim(implode(":", $p))];
}, array_filter(explode("\n", curl_exec($ch)), function ($i) {
    return trim($i) !== "" && str_contains(trim($i), ":") && str_starts_with(trim($i), "content-");
}));
$headers = [];
foreach ($response as $header) {
    $headers[$header[0]] = $header[1];
}
curl_close($ch);

header("Content-Type: " . $headers["content-type"]);
header("Content-Disposition: attachment; filename=\"" . $headers["content-disposition"] . "\"");
header("Content-Length: " . $headers["content-length"]);

$resource = curl_init();
curl_setopt($resource, CURLOPT_UNRESTRICTED_AUTH, 1);
curl_setopt($resource, CURLOPT_URL, $url);
curl_setopt($resource, CURLOPT_HTTPHEADER, [ "Authorization: Bearer " . trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/fetcher/token.txt")) ]);
curl_exec($resource);
curl_close($resource);