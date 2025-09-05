<template>
  <v-container class="fill-height d-flex justify-center align-center">
    <v-card width="400">
      <v-card-title class="text-center">Login</v-card-title>
      <v-card-text>
        <v-alert v-if="errorMessage" type="error" dense class="mb-4">{{ errorMessage }}</v-alert>
        <v-form @submit.prevent="login">
          <v-text-field
            v-model="form.email"
            label="Email"
            prepend-icon="mdi-email"
            type="email"
            required
            :error-messages="errors.email"
          ></v-text-field>
          <v-text-field
            v-model="form.password"
            label="Password"
            prepend-icon="mdi-lock"
            type="password"
            required
            :error-messages="errors.password"
          ></v-text-field>
          <v-btn type="submit" color="primary" block>Login</v-btn>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-btn text to="/register" small block>Don't have an account? Sign Up</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'

const router = useRouter()
const form = ref({
  email: '',
  password: '',
})
const errors = ref({})
const errorMessage = ref(null)

const login = async () => {
  errors.value = {}
  errorMessage.value = null
  try {
    const response = await api.post('/login', form.value);
    localStorage.setItem('auth_token', response.data.access_token);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    router.push('/contacts');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else if (error.response && error.response.status === 401) {
        errorMessage.value = error.response.data.message;
    } else {
      console.error('Login failed:', error);
    }
  }
}
</script>
