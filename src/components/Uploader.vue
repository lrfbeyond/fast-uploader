<template>
    <div id="page">
        <header>
            <h1 class="logo"><a href="http://www.helloweba.net" title="返回helloweba首页">helloweba</a></h1>
        </header>
        
        <div class="main">
            <h2><a href="http://www.helloweba.net/javascript/632.html">使用vue-simple-uploader上传文件和文件夹</a></h2>
            <p style="text-align:center;color: #999">温馨提示：可将文件拖动到灰色区域上传</p>
            <uploader :options="options" :fileStatusText="fileStatusText" class="uploader">
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
export default {
    data () {
      return {
        options: {
            target: 'uploader.php',
            testChunks: false,
            chunkSize: 1024*1024*2,  //1MB
            simultaneousUploads: 1, //并发上传数
            headers: {
                'X-token': 'abcd123'
            },
            maxChunkRetries: 3, //最大自动失败重试上传次数
        },
        fileStatusText: {
            success: '上传成功',
            error: '上传出错了',
            uploading: '上传中...',
            paused: '暂停',
            waiting: '等待中...'
        },
        attrs: {
            accept: 'image/*'
        }
      }
    },
    // mounted () {
    //   this.$nextTick(() => {
    //     window.uploader = this.$refs.uploader.uploader
    //   })
    // }
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
    }
    // /deep/.uploader-drop{
    //     height: 400px;
    // }
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