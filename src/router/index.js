import Vue from 'vue'
import Router from 'vue-router'
import Uploader from '@/components/Uploader'
import Chunk from '@/components/Chunk'
import Md5 from '@/components/Md5'
import Skip from '@/components/Skip'
import Breakpoint from '@/components/Breakpoint'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'uploader',
      component: Uploader
    },
    {
      path: '/chunk',
      name: 'chunk',
      component: Chunk
    },
    {
      path: '/skip',
      name: 'skip',
      component: Skip
    },
    {
      path: '/md5',
      name: 'md5',
      component: Md5
    },
    {
      path: '/breakpoint',
      name: 'breakpoint',
      component: Breakpoint
    },
  ]
})
