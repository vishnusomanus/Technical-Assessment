import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import Tasks from './components/Tasks.vue'
import './style.css'

const routes = [
  { path: '/', component: Tasks },
  { path: '/tasks', component: Tasks },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

createApp(App).use(router).mount('#app')

