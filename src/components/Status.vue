<template>
    <div id="page">
        <header>
            <h1 class="logo"><a href="http://www.helloweba.net" title="返回helloweba首页">helloweba</a></h1>
        </header>
        
        <div class="main">
            <h2><a href="http://www.helloweba.net/javascript/633.html">文件上传之失败自动重试重传文件</a></h2>
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
                <uploader-list>
                    <div class="file-panel" slot-scope="props">
                        <ul class="file-list">
                            <li v-for="file in props.fileList" :key="file.id">
                                <uploader-file :class="'file_' + file.id" ref="files" :file="file" :list="true">
                                    <template slot-scope="scope">
                                        <!-- <span ref="status">{{scope.status}}</span> -->
                                        <div class="uploader-file-info">
                                        <div class="uploader-file-name"><i class="uploader-file-icon"></i>{{file.name}}</div>
                                        <div class="uploader-file-size">{{scope.formatedSize}}</div>
                                        <div class="uploader-file-meta"></div>
                                        <div class="uploader-file-status">
                                          <span v-show="scope.status !== 'uploading'">{{scope.status}}</span>
                                          <span v-show="scope.status === 'uploading'">
                                            <!-- <span>{{progressStyle.progress}}</span> -->
                                            <em>{{scope.formatedAverageSpeed}}</em>
                                            <i>{{scope.formatedTimeRemaining}}</i>
                                          </span>
                                        </div>
                                        <div class="uploader-file-actions">
                                          <span class="uploader-file-pause" @click="pause"></span>
                                          <span class="uploader-file-resume" @click="resume"></span>
                                          <span class="uploader-file-retry" @click="retry"></span>
                                          <span class="uploader-file-remove" @click="remove"></span>
                                        </div>
                                      </div>

                                    </template>
                                </uploader-file>
                            </li>
                            <div class="no-file" v-if="!props.fileList.length"><i class="nucfont inuc-empty-file"></i> 暂无待上传文件</div>
                        </ul>
                    </div>
                </uploader-list>
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
            target: 'http://helloweba.test/demo/2020/uploader2.php',
            testChunks: false,
            chunkSize: 1024*1024*1,  //1MB
            simultaneousUploads: 2, //并发上传数
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
        //const uploaderInstance = this.$refs.uploader.uploader;
    },
    methods: {
        // 文件进度的回调
        onFileProgress(rootFile, file, chunk) {
            console.log(file);
            const uploaderInstance = this.$refs.uploader.uploader;
            let size = uploaderInstance.getSize();
            console.log('size:',size);
            console.log(`上传中 ${file.name}，chunk：${chunk.startByte / 1024 / 1024} ~ ${chunk.endByte / 1024 / 1024}`)
        },
        onFileSuccess(rootFile, file, response, chunk) {
            //let res = JSON.parse(response);
            const uploaderInstance = this.$refs.uploader.uploader;
            let size = uploaderInstance.getSize();
            console.log(size);
            //console.log(response);
            // 服务器自定义的错误，这种错误是Uploader无法拦截的
            // if (!res.result) {
            //     this.$message({ message: res.message, type: 'error' });
            //     return
            // }
        },

        onFileError(rootFile, file, response, chunk) {
            console.log('Error:', response)
            const uploaderInstance = this.$refs.uploader.uploader;
            let size = uploaderInstance.getSize();
            console.log(size);
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

@font-face {
    font-family: "iconfont";
    src: url('../assets/css/iconfont.woff?t=1585390393547') format('woff');
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
        position: absolute;
        top: 80px;
        height: 356px;
        width: 880px;
        /deep/.uploader-file-icon {
            font-family: "iconfont" !important;
            font-size: 16px;
            font-style: normal;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            color: #409eff;
            &[icon=image]:before {
                content: "\e60b";
                font-size: 20px;
            }
            &[icon=video]:before {
                content: "\e6ee";
            }
            &[icon=audio]:before {
                content: "\e6ed";
            }
            &[icon=document]:before {
                content: "\e62b";
            }
            &[icon=folder]:before {
                content: "\e686";
            }
            &[icon=unknown]:before {
                content: "\e760";
            }
            
        }
        .no-file {
            position: absolute;
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 16px;
            color: #ccc;
        }
    }
    /deep/.uploader-drop{
        height: 400px;
    }
}
</style>