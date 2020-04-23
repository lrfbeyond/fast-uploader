<?php

/**
 * @Author: Helloweba
 * @Date:   2020-04-08 20:21:06
 * @Last Modified by:   Helloweba
 * @Last Modified time: 2020-04-23 16:57:16
 */

class Uploader
{
    private static $tmpDir = 'D:\laragon\www\helloweba\demo\files_tmp'; //分片临时文件目录
    private static $saveDir = 'D:\laragon\www\helloweba\demo\files'; //文件最终保存目录
    private static $mysql = null;
    public $fileInfo = [
        'identifier' => '',  //文件的唯一标识
        'chunkNumber' => 1, //当前是第几个分片
        'totalChunks' => 1,  //总分片数
        'filename' => '',  //文件名称
        'totalSize' => 0  //文件总大小
    ];

    public static function mysql()
    {
        $conn = [
            'dbhost' => '127.0.0.1',//数据库服务器
            'dbport' => 3306,//端口
            'dbname' => 'demo',//数据库名称
            'dbuser' => 'root',//用户名
            'dbpass' => ''//密码
        ];

        // 连接
        try {
            $db = new PDO('mysql:host='.$conn['dbhost'].';port='.$conn['dbport'].';dbname='.$conn['dbname'], $conn['dbuser'], $conn['dbpass']);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //设置错误模式
            $db->query('SET NAMES utf8;');
            self::$mysql = $db;
        } catch (PDOException $e) {
            echo '连接数据库失败！'.PHP_EOL;
            exit;
        }
        return self::$mysql;
    }

    //检测md5表是否已存在该文件
    private function checkMd5($md5, $filesize)
    {
        $db = self::mysql();
        $sql = "SELECT count(*) as t,filepath FROM `hw_file` WHERE md5=:md5 AND filesize=:filesize";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':md5' => $md5,
            ':filesize' => $filesize
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['t'];
        if ($count > 0) {
            $res['isExist'] = true;
            $res['filepath'] = $row['filepath'];
        } else {
            $res['isExist'] = false;
        }
        return $res;
    }

    //检测断点和md5
    public function checkFile()
    {
        $identifier = $this->fileInfo['identifier'];
        $filePath = self::$tmpDir. DIRECTORY_SEPARATOR . $identifier; //临时分片文件路径
        $totalChunks = $this->fileInfo['totalChunks'];

        //检测文件md5是否已经存在
        $rs = $this->checkMd5($identifier, $this->fileInfo['totalSize']);
        if ($rs['isExist'] === true) {
            return $rs;
        }

        //检查分片是否存在
        $chunkExists = [];
        for ($index = 1; $index <= $totalChunks; $index++ ) {
            if (file_exists("{$filePath}_{$index}")) {
                array_push($chunkExists, $index);
            }
        }
        if (count($chunkExists) == $totalChunks) { //全部分片存在，则直接合成
            $this->merge();
        } else {
            $res['uploaded'] = $chunkExists;
            return $res;
        }
    }

    //上传分片
    public function upload()
    {
        if (!empty($_FILES)) {
            $in = @fopen($_FILES["file"]["tmp_name"], "rb");
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                return $this->message(1002, '打开临时文件失败');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                return $this->message(1003, '打开输入流失败');
            }
        }

        if ($this->fileInfo['totalChunks'] === 1) {
            //如果只有1片，则不需要合并，直接将临时文件转存到保存目录下
            $filename = $this->fileInfo['filename'];
            $saveDir = self::$saveDir . DIRECTORY_SEPARATOR . date('Y-m-d');
            if (!is_dir($saveDir)) {
                @mkdir($saveDir);
            }

            $uploadPath = $saveDir . DIRECTORY_SEPARATOR .$filename;
            $res['merge'] = false;
        } else { //需要合并
            $filePath = self::$tmpDir. DIRECTORY_SEPARATOR . $this->fileInfo['identifier']; //临时分片文件路径
            $uploadPath = $filePath . '_' . $this->fileInfo['chunkNumber']; //临时分片文件名
            $res['merge'] = true;
        }
        if (!$out = @fopen($uploadPath, "wb")) {
            return $this->message(1004, '文件不可写');
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($in);
        @fclose($out);

        if ($this->fileInfo['totalChunks'] === 1) { //数据入库
            $fileData = [
                'filename' => $filename,
                'filesize' => $this->fileInfo['totalSize'],
                'md5' => $this->fileInfo['identifier'],
                'filepath' => date('Y-m-d') . '/' . $filename
            ];
            $url = $this->insertData($fileData);
            $res['url'] = $url;
        }
        
        $res['code'] = 0;
        return $res;
    }

    //合并文件
    public function merge()
    {        
        $filePath = self::$tmpDir. DIRECTORY_SEPARATOR . $this->fileInfo['identifier'];

        $totalChunks = $this->fileInfo['totalChunks'];
        $filename = $this->fileInfo['filename'];

        $done = true;
        //检查所有分片是否都存在
        for ($index = 1; $index <= $totalChunks; $index++ ) {
            if (!file_exists("{$filePath}_{$index}")) {
                $done = false;
                break;
            }
        }
        if ($done === false) {
            return $this->message(1005, '分片信息错误');
        }
        //如果所有文件分片都上传完毕，开始合并
        $timeStart = $this->getmicrotime(); //合并开始时间
        $saveDir = self::$saveDir . DIRECTORY_SEPARATOR . date('Y-m-d');
        if (!is_dir($saveDir)) {
            @mkdir($saveDir);
        }

        $uploadPath = $saveDir . DIRECTORY_SEPARATOR .$filename;

        if (!$out = @fopen($uploadPath, "wb")) {
            return $this->message(1004, '文件不可写');
        }
        if (flock($out, LOCK_EX) ) { // 进行排他型锁定
            for($index = 1; $index <= $totalChunks; $index++ ) {
                if (!$in = @fopen("{$filePath}_{$index}", "rb")) {
                    break;
                }
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }
                @fclose($in);
                @unlink("{$filePath}_{$index}"); //删除分片
            }
           
            flock($out, LOCK_UN); // 释放锁定
        }
        @fclose($out);
        $timeEnd = $this->getmicrotime(); //合并完成时间

        $fileData = [
            'filename' => $filename,
            'filesize' => $this->fileInfo['totalSize'],
            'md5' => $this->fileInfo['identifier'],
            'filepath' => date('Y-m-d') . '/' . $filename
        ];
        $url = $this->insertData($fileData);
        
        $res['code'] = 0;
        $res['url'] = $url;
        $res['time'] = $timeEnd - $timeStart; //合并总耗时

        return $res;
    }

    //将文件信息写入数据表
    private function insertData($data)
    {
        if (empty($data['filename'])) {
            return false;
        }
        $pathInfo = pathinfo($data['filename']);
        $ext = '0';
        if (array_key_exists('extension', $pathInfo)) {
            $ext = $pathInfo['extension'];
        }
        $db = self::mysql();
        $sql = "INSERT INTO `hw_file` (filename,filesize,md5,type,filepath,created_at) VALUES (:filename,:filesize,:md5,:type,:filepath,:created_at)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':filename' => $data['filename'],
            ':filesize' => $data['filesize'],
            ':md5' => $data['md5'],
            ':type' => $ext,
            ':filepath' => $data['filepath'],
            ':created_at' => date('Y-m-d H:i:s'),
        ]);

        $insertId = $db->lastInsertId(); //返回插入成功后的id
        if ($insertId > 0) {
            return $data['filepath']; //返回文件路径
        } else {
            return $this->message(1008, '数据写入出错了');
        }
    }

    private function getmicrotime()
    {
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

    private function message($code, $msg)
    {
        $res = [
            'code' => $code,
            'message' => $msg
        ];
        return $res;
    }
}