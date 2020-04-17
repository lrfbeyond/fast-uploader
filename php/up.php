<?php 
header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Credentials: true");//支持cookie跨域
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-token");
// header('Content-Type:application/json; charset=utf-8');
date_default_timezone_set('PRC');

require_once('Uploader.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

$up = new Uploader();
if ($action == 'merge') { //合并
    $post = file_get_contents('php://input');
    $data = json_decode($post, true);
    $up->fileInfo = [
        'filename' => htmlentities($data['filename']), //文件名称
        'identifier' => htmlentities($data['identifier']), //文件唯一标识
        'totalSize' => intval($data['totalSize']), //文件总大小
        'totalChunks' => intval($data['totalChunks']) //总分片数
    ];
    $res = $up->merge();
} else {
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'POST') { //上传
        $up->fileInfo = [
            'identifier' => htmlentities($_POST['identifier']), //每个文件的唯一标识
            'filename' => htmlentities($_POST['filename']), //文件名称
            'totalSize' => intval($_POST['totalSize']), //文件总大小
            'chunkNumber' => intval($_POST['chunkNumber']), //当前是第几个分片
            'totalChunks' => intval($_POST['totalChunks']) //总分片数
        ];
        $res = $up->upload(); 
    } else { //上传前检测文件md5和分片
        $up->fileInfo = [
            'identifier' => htmlentities($_GET['identifier']), //每个文件的唯一标识
            'filename' => htmlentities($_GET['filename']), //文件名称
            'totalSize' => intval($_GET['totalSize']), //文件总大小
            'totalChunks' => intval($_GET['totalChunks']) //总分片数
        ];
        $res = $up->checkFile();
    }
}

echo json_encode($res);
