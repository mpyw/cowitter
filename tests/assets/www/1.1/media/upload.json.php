<?php

require __DIR__ . '/../../../dummy_oauth.php';

verify_oauth_1a('https://upload.twitter.com');

if (isset($_POST['command'], $_POST['total_bytes'], $_POST['media_type']) && $_POST['command'] === 'INIT') {

    do {
        $media_id = 'cowitter_test_media_' . bin2hex(openssl_random_pseudo_bytes(32));
    } while (file_exists($path = "/tmp/$media_id"));
    header('Content-Type: application/json');
    $info = json_encode([
        'media_id_string' => $media_id,
        'media_category' => isset($_POST['media_category']) ? $_POST['media_category'] : null,
        'total_bytes' => $_POST['total_bytes'],
    ]);
    echo $info;
    file_put_contents($path, $info);
    exit;

}

if (isset($_POST['command'], $_POST['segment_index'], $_POST['media_id']) && $_POST['command'] === 'APPEND') {

    if (!file_exists("/tmp/$_POST[media_id]")) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['errors' => 'invalid media id']);
        exit;
    }

    $data = isset($_FILE['media']) ? file_get_contents($_FILE['media']['tmp_name']) : $_POST['media'];
    file_put_contents("/tmp/$_POST[media_id]--$_POST[segment_index]", $data);
    http_response_code(204);
    exit;

}

if (isset($_POST['command'], $_POST['segment_index'], $_POST['media_id']) && $_POST['command'] === 'APPEND') {

    if (!file_exists("/tmp/$_POST[media_id]")) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['errors' => 'invalid media id']);
        exit;
    }

    $data = isset($_FILE['media']) ? file_get_contents($_FILE['media']['tmp_name']) : $_POST['media'];
    file_put_contents("/tmp/$_POST[media_id]--$_POST[segment_index]", $data);
    http_response_code(204);
    exit;
}

if (isset($_POST['command'], $_POST['media_id']) && $_POST['command'] === 'FINALIZE') {

    if (!file_exists("/tmp/$_POST[media_id]")) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['errors' => 'invalid media id']);
        exit;
    }

    $data = implode(array_map('file_get_contents', glob("/tmp/$_POST[media_id]--*")));
    $info = json_decode(file_get_contents("/tmp/$_POST[media_id]"));

    if (strlen($data) != $info->total_bytes) {
        header('Content-Type: appliaction/json', true, 400);
        echo json_encode(['errors' => 'invalid byte length']);
        exit;
    }

    if (isset($info->media_category)) {
        $info->processing_info = (object)['state' => 'pending'];
        $info->processing_info->check_after_secs = 0.075;
        file_put_contents("/tmp/$_POST[media_id]", json_encode($info));
    }

    header('Content-Type: application/json');
    echo json_encode($info);
    exit;

}

if (isset($_GET['command'], $_GET['media_id']) && $_GET['command'] === 'STATUS') {

    if (!file_exists("/tmp/$_GET[media_id]")) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['errors' => 'invalid media id']);
        exit;
    }

    $info = json_decode(file_get_contents("/tmp/$_GET[media_id]"));

    if ($info->processing_info->state === 'pending') {
        $info->processing_info->state = 'in_progress';
        $info->processing_info->progress_percent = 53;
        $info->processing_info->check_after_secs = 0.075;
        file_put_contents("/tmp/$_GET[media_id]", json_encode($info));
    } elseif ($info->processing_info->state === 'in_progress') {
        $info->processing_info->state = 'done';
        $info->processing_info->progress_percent = 100;
        @unlink("/tmp/$_GET[media_id]");
        @array_map('unlink', glob("/tmp/$_GET[media_id]--*"));
        if ($info->media_category === 'tweet_gif') {
            $info->processing_info->state = 'failed';
            $info->processing_info->error = (object)[
                'message' => 'tweet_gif always fails in this test.',
                'code' => 114514,
            ];
        } elseif ($info->media_category === 'tweet_video') {
            $info->processing_info->state = 'failed';
            $info->processing_info->error = (object)[
                'name' => 'tweet_video always fails in this test.',
                'code' => 114514,
            ];
        }

    }

    header('Content-Type: application/json');
    echo json_encode($info);
    exit;

}


header('Content-Type: application/json', true, 400);
