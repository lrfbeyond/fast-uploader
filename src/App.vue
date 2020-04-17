<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'App',

  mounted() {  
     // 创建cnzz统计js
    const script = document.createElement('script')  
    script.src = `https://s13.cnzz.com/z_stat.php?id=1271767266&web_id=1271767266`  
    script.language = 'JavaScript'  
    document.body.appendChild(script)  
  },  
  watch: {  
    '$route': {
      handler(to, from) {
        setTimeout(() => { //避免首次获取不到window._czc
          if (window._czc) {  
            let location = window.location; 
            let contentUrl = location.pathname + location.hash;  
            let refererUrl = '/';  
            // 用于发送某个URL的PV统计请求，
            window._czc.push(['_trackPageview', contentUrl, refererUrl])  
          }
        }, 300) 
      },
      immediate: true  // 首次进入页面即执行
    }  
  }
}
</script>

<style>
*{margin:0;padding:0;}
html,body,#app,.wrapper{
    width:100%;
    height:100%;
}
html{
    background: url('./assets/bg.png');
}
body{
    font-family:"Helvetica Neue",Helvetica, "microsoft yahei", arial, STHeiTi, sans-serif;
    background: url('./assets/body_bg.gif') repeat-x;
}
a {
    color: #369/*#424242*/;
    text-decoration: none;
}

header {
    box-sizing: border-box;
    width: 100%;
    height: 100px;
    padding: 10px;
    overflow: hidden;
}
.logo {
    width: 70px;
    height: 70px;
    margin: 0 auto;
    background: url(./assets/logo.png) no-repeat 0 10px;
    text-indent: -999em;
}
.logo a{
    display: block;
    width: 100%;
    height: 100%;
}

footer {
    padding-top: 20px;
    height: 60px;
    text-align: center;
}
</style>
