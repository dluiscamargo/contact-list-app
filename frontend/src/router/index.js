import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ContactsView from '../views/ContactsView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { guest: true }
    },
    {
      path: '/contacts',
      name: 'contacts',
      component: ContactsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/',
      redirect: '/contacts'
    }
  ]
})

router.beforeEach((to, from, next) => {
  const loggedIn = localStorage.getItem('auth_token');

  if (to.matched.some(record => record.meta.requiresAuth) && !loggedIn) {
    next('/login')
  } else if (to.matched.some(record => record.meta.guest) && loggedIn) {
    next('/contacts')
  } else {
    next()
  }
})

export default router
