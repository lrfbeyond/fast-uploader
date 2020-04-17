<template>
    <div>
        <uploader
            ref="uploader"
            :options="options"
            :autoStart="false"
            @file-added="onFileAdded"
            @file-progress="onFileProgress"
            @file-success="onFileSuccess"
            @file-error="onFileError"
            >
            <uploader-unsupport></uploader-unsupport>
            <uploader-drop>
                <p>Drop files here to upload or</p>
                <uploader-btn>select files</uploader-btn>
                <uploader-btn :attrs="attrs">select images</uploader-btn>
                <uploader-btn :directory="true">select folder</uploader-btn>
            </uploader-drop>
            <uploader-list v-show="panelShow">
                <div class="file-panel" slot-scope="props">
                    <div class="file-title">
                        <h2>文件列表</h2>
                    </div>

                    <ul class="file-list">
                        <li v-for="file in props.fileList" :key="file.id">
                            <uploader-file :class="'file_' + file.id" ref="files" :file="file" :list="true"></uploader-file>
                        </li>
                        <div class="no-file" v-if="!props.fileList.length"><i class="nucfont inuc-empty-file"></i> 暂无待上传文件</div>
                    </ul>
                </div>
            </uploader-list>
        </uploader>
    </div>
</template>

<script>
import SparkMD5 from 'spark-md5';

export default {
    data () {
      return {
        options: {
            target: 'http://helloweba.test/demo/2020/uploaderMd5.php',
            chunkSize: 1024*1024*1,  //1MB
            simultaneousUploads: 1, //并发上传数
            headers: {
                'X-token': 'abcd123'
            },
            maxChunkRetries: 3, //最大自动失败重试上传次数
            testChunks: true,     //是否开启服务器分片校验
            // 服务器分片校验函数，秒传及断点续传基础
            checkChunkUploadedByResponse: function (chunk, message) {
                //console.log(message);
                //console.log(chunk);
                let objMessage = JSON.parse(message);
                if (objMessage.skipUpload) {
                    return true;
                }

                return (objMessage.uploaded || []).indexOf(chunk.offset + 1) >= 0
            },
        },
        attrs: {
            accept: 'image/*'
        },
        panelShow: false,
      }
    },
    methods: {
        onFileAdded(file) {
            this.panelShow = true;
            // 计算MD5
            this.computeMD5(file);
        },
        // 文件进度的回调
        onFileProgress(rootFile, file, chunk) {
            console.log(`上传中 ${file.name}，chunk：${chunk.startByte / 1024 / 1024} ~ ${chunk.endByte / 1024 / 1024}`)
        },
        onFileSuccess(rootFile, file, response, chunk) {
            let res = JSON.parse(response);
            console.log(response);
            // 服务器自定义的错误，这种错误是Uploader无法拦截的
            // if (!res.result) {
            //     this.$message({ message: res.message, type: 'error' });
            //     return
            // }
            
            // 如果服务端返回需要合并
            // if (res.needMerge) {
            //     api.mergeSimpleUpload({
            //         tempName: res.tempName,
            //         fileName: file.name,
            //         ...this.params,
            //     }).then(data => {
            //         // 文件合并成功
            //         Bus.$emit('fileSuccess', data);
            //     }).catch(e => {});
            // // 不需要合并    
            // } else {
            //     Bus.$emit('fileSuccess', res);
            //     console.log('上传成功');
            // }
        },

        onFileError(rootFile, file, response, chunk) {
            console.log('Error:', response)
        },

        computeMD5(file) {
            let fileReader = new FileReader();
            let time = new Date().getTime();
            let blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;
            let currentChunk = 0;
            const chunkSize = 10 * 1024 * 1000;
            let chunks = Math.ceil(file.size / chunkSize);
            let spark = new SparkMD5.ArrayBuffer();
            
            // 文件状态设为"计算MD5"
            //this.statusSet(file.id, 'md5');
            console.log('计算MD5...')
            
            file.pause();
            
            loadNext();
            
            fileReader.onload = (e => {
                spark.append(e.target.result);
                if (currentChunk < chunks) {
                    currentChunk++;
                    loadNext();
                    // 实时展示MD5的计算进度
                    // this.$nextTick(() => {
                    //     $(`.myStatus_${file.id}`).text('校验MD5 '+ ((currentChunk/chunks)*100).toFixed(0)+'%')
                    // })
                } else {
                    let md5 = spark.end();
                    this.computeMD5Success(md5, file);
                    console.log(`MD5计算完毕：${file.name} \nMD5：${md5} \n分片：${chunks} 大小:${file.size} 用时：${new Date().getTime() - time} ms`);
                }
            });
            fileReader.onerror = function () {
                this.error(`文件${file.name}读取出错，请检查该文件`)
                file.cancel();
            };
            function loadNext() {
                let start = currentChunk * chunkSize;
                let end = ((start + chunkSize) >= file.size) ? file.size : start + chunkSize;
                fileReader.readAsArrayBuffer(blobSlice.call(file.file, start, end));
            }
        },

        computeMD5Success(md5, file) {
            // 将自定义参数直接加载uploader实例的opts上
            // Object.assign(this.uploader.opts, {
            //     query: {
            //         ...this.params,
            //     }
            // })
            file.uniqueIdentifier = md5;
            file.resume();
            //this.statusRemove(file.id);
        },
    }
}
</script>

<style lang="less">
.uploader {
    width: 880px;
    padding: 15px;
    margin: 40px auto 0;
    font-size: 12px;
    box-shadow: 0 0 10px rgba(0, 0, 0, .4);
    .uploader-btn {
        margin-right: 4px;
    }
    .uploader-list {
        max-height: 440px;
        overflow: auto;
        overflow-x: hidden;
        overflow-y: auto;
    }
}
</style>