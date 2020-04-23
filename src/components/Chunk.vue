<template>
    <div id="page">
        <header>
            <h1 class="logo"><a href="http://www.helloweba.net" title="返回helloweba首页">helloweba</a></h1>
        </header>
        
        <div class="main">
            <h2><a href="http://www.helloweba.net/javascript/633.html">大文件上传之分片上传</a></h2>
            <uploader 
                ref="uploader"
                :options="options" 
                :fileStatusText="fileStatusText"
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

export default {
    data () {
      return {
        options: {
            target: 'up.php',
            testChunks: false,
            chunkSize: 1024*1024*2,  //2MB
            simultaneousUploads: 1, //并发上传数
            headers: {
                'X-token': 'abcd123'
            },
            maxChunkRetries: 1, //最大自动失败重试上传次数
            parseTimeRemaining: function (timeRemaining, parsedTimeRemaining) { //格式化时间
              return parsedTimeRemaining
                .replace(/\syears?/, '年')
                .replace(/\days?/, '天')
                .replace(/\shours?/, '小时')
                .replace(/\sminutes?/, '分钟')
                .replace(/\sseconds?/, '秒')
            }
        },
        fileStatusText: {
            success: '上传成功',
            error: '上传出错了',
            uploading: '上传中...',
            paused: '暂停',
            waiting: '等待中...'
        },
      }
    },
    created() {
        
    },
    methods: {
        // 文件进度的回调
        onFileProgress(rootFile, file, chunk) {
            console.log(`上传中 ${file.name}，chunk：${chunk.startByte / 1024 / 1024} ~ ${chunk.endByte / 1024 / 1024}`)
            if (file.size > 1024 * 1024 * 10) {
                alert('文件太大');
                file.cancel();
            }
        },
        onFileSuccess(rootFile, file, response, chunk) {
            let resp = JSON.parse(response);
            if (resp.code === 0 && resp.merge === false) {
                console.log('上传成功，不需要合并');
            } else {
                axios.post('up.php?action=merge', {
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
            alert(response);
            file.cancel();
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