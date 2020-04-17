<template>
    <div id="page">
        <header>
            <h1 class="logo"><a href="http://www.helloweba.net" title="返回helloweba首页">helloweba</a></h1>
        </header>
        
        <div class="main">
            <h2><a href="http://www.helloweba.net/javascript/637.html">文件上传之暂停和断点续传和跨浏览器续传</a></h2>
            <uploader 
                ref="uploader"
                :options="options" 
                :file-status-text="fileStatusText"
                :autoStart="false"
                @file-added="onFileAdded"
                @file-progress="onFileProgress"
                @file-success="onFileSuccess"
                @file-error="onFileError"
                class="uploader">
                <uploader-unsupport></uploader-unsupport>
                <uploader-drop>
                    <uploader-btn class="upfile"><i class="iconfont icon-upload"></i> 上传文件</uploader-btn>
                    <uploader-btn class="updir" :directory="true"><i class="iconfont icon-dir"></i> 上传文件夹</uploader-btn>
                </uploader-drop>
                <uploader-list></uploader-list>
            </uploader>
        </div>
        
        <footer>
            <p>Powered by helloweba.net  允许转载、修改和使用本站的DEMO，但请注明出处：<a href="http://www.helloweba.net">www.helloweba.net</a></p>
            <p class="hidden"></p>
        </footer>
    </div>
</template>

<script>
import axios from 'axios';
import SparkMD5 from 'spark-md5';

export default {
    data () {
      return {
        options: {
            target: 'http://localhost:9999/up.php',
            chunkSize: 2097152,  //2MB
            simultaneousUploads: 1, //并发上传数
            headers: {
                'X-token': 'abcd123'
            },
            maxChunkRetries: 2, //最大自动失败重试上传次数
            parseTimeRemaining: function (timeRemaining, parsedTimeRemaining) { //格式化时间
                return parsedTimeRemaining
                    .replace(/\syears?/, '年')
                    .replace(/\days?/, '天')
                    .replace(/\shours?/, '小时')
                    .replace(/\sminutes?/, '分钟')
                    .replace(/\sseconds?/, '秒')
            },
            testChunks: true,   //开启服务端分片校验
            // 服务器分片校验函数
            checkChunkUploadedByResponse: (chunk, message) => {
                let obj = JSON.parse(message);
                if (obj.skip) {
                    this.statusTextMap.success = '秒传文件';
                    return true;
                }

                return (obj.uploaded || []).indexOf(chunk.offset + 1) >= 0
            },
        },

        statusTextMap: {
            success: '上传成功',
            error: '上传出错了',
            uploading: '上传中...',
            paused: '暂停',
            waiting: '等待中...',
            cmd5: '计算md5...'
        },

        fileStatusText: (status, response) => {
            return this.statusTextMap[status];
        },
      }
    },
    created() {
        //
    },
    methods: {
        onFileAdded(file) {
            // 计算MD5
            this.computeMD5(file);
        },
        
        //计算MD5
        computeMD5(file) {
            let blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice,
                chunkSize = 2097152,
                chunks = Math.ceil(file.size / chunkSize),
                currentChunk = 0,
                spark = new SparkMD5.ArrayBuffer(),
                fileReader = new FileReader();

            let time = new Date().getTime();

            file.cmd5 = true;

            fileReader.onload = (e) => {
                spark.append(e.target.result);   // Append array buffer
                currentChunk++;
         
                if (currentChunk < chunks) {
                    //console.log(`第${currentChunk}分片解析完成, 开始第${currentChunk +1} / ${chunks}分片解析`);
                    let percent = Math.floor(currentChunk / chunks * 100);
                    file.cmd5progress = percent;
                    loadNext();
                } else {
                    console.log('finished loading');
                    let md5 = spark.end();
                    console.log(`MD5计算完成：${file.name} \nMD5：${md5} \n分片：${chunks} 大小:${file.size} 用时：${new Date().getTime() - time} ms`);
                    spark.destroy(); //释放缓存
                    file.uniqueIdentifier = md5; //将文件md5赋值给文件唯一标识
                    file.cmd5 = false; //取消计算md5状态
                    file.resume(); //开始上传
                }
            };

            fileReader.onerror = () => {
                console.warn('oops, something went wrong.');
                file.cancel();
            };
         
            let loadNext = () =>　{
                let start = currentChunk * chunkSize,
                    end = ((start + chunkSize) >= file.size) ? file.size : start + chunkSize;
         
                fileReader.readAsArrayBuffer(blobSlice.call(file.file, start, end));
            };
         
            loadNext();
        },
        // 文件进度的回调
        onFileProgress(rootFile, file, chunk) {
            console.log(`上传中 ${file.name}，chunk：${chunk.startByte / 1024 / 1024} ~ ${chunk.endByte / 1024 / 1024}`)
        },
        onFileSuccess(rootFile, file, response, chunk) {
            let resp = JSON.parse(response);
            //合并分片
            if (resp.code === 0 && resp.merge === true) {
                axios.post('http://localhost:9999/up.php?action=merge', {
                    filename: file.name,
                    identifier: file.uniqueIdentifier,
                    totalSize: file.size,
                    totalChunks: chunk.offset + 1
                }).then(function(res){
                    if (res.code === 0) {
                        console.log('上传成功')
                    } else {
                        console.log(res.message);
                    }
                })
                .catch(function(error){
                    console.log(error);
                });
            }
        },

        onFileError(rootFile, file, response, chunk) {
            console.log('Error:', response)
        },
    }
}
</script>

<style scoped lang="less">
.main{
    max-width: 1000px;
    margin: 10px auto;
    
    background: #fff;
    padding: 10px;
    h2{
        padding: 30px 0;
        text-align: center;
        font-size: 20px;
    }
}

.uploader {
    width: 880px;
    padding: 15px;
    margin: 20px auto 0;
    font-size: 14px;
    box-shadow: 0 0 10px rgba(0, 0, 0, .4);
    .uploader-btn {
        margin-right: 4px;
        color: #fff;
        padding: 6px 16px;
    }
    .upfile{
        border: 1px solid #409eff;
        background: #409eff;
    }
    .updir{
        border: 1px solid #67c23a;
        background: #67c23a;
    }
    .uploader-list {
        max-height: 440px;
        overflow: auto;
        overflow-x: hidden;
        overflow-y: auto;
        
        height: 356px;
        /deep/.iconfont {
            font-size: 18px;
            color: #409eff;
        }
    }
    
}

//手机等小屏幕手持设备。当设备宽度  在  320px和768px之间时,执行当前的css
@media only screen and (min-width: 320px) and (max-width: 768px) {
    .uploader {
        width: 98%;
        padding: 0;
        box-shadow: none;
    }
}
</style>