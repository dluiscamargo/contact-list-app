<template>
  <v-container class="fill-height d-flex justify-center align-center">
    <v-card width="400">
      <v-card-title class="text-center">Register</v-card-title>
      <v-card-text>
        <v-form @submit.prevent="register">
          <v-text-field
            v-model="form.name"
            label="Name"
            prepend-icon="mdi-account"
            type="text"
            required
            :error-messages="errors.name"
          ></v-text-field>
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
          <v-text-field
            v-model="form.password_confirmation"
            label="Confirm Password"
            prepend-icon="mdi-lock"
            type="password"
            required
          ></v-text-field>
          <v-btn type="submit" color="primary" block>Register</v-btn>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-btn text to="/login" small block>Already have an account? Sign In</v-btn>
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
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})
const errors = ref({})

const register = async () => {
  errors.value = {} // Limpa erros antigos
  try {
    const response = await api.post('/register', form.value);
    localStorage.setItem('auth_token', response.data.access_token);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    router.push('/contacts');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Registration failed:', error);
    }
  }
}
</script>
