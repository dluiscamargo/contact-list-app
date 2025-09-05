<template>
  <v-dialog v-model="dialog" persistent max-width="600px">
    <v-card>
      <v-card-title>
        <span class="headline">{{ formTitle }}</span>
      </v-card-title>
      <v-card-text>
        <v-container>
          <v-row>
            <v-col cols="12">
              <v-text-field label="Name*" v-model="contact.name" required></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="CPF*" v-model="contact.cpf" required></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Phone*" v-model="contact.phone" required></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="CEP*" v-model="contact.cep" @blur="searchCep" required></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Street*" v-model="contact.street" required></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Number*" v-model="contact.number" required></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Complement" v-model="contact.complement"></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Neighborhood*" v-model="contact.neighborhood" required></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="City*" v-model="contact.city" required></v-text-field>
            </v-col>
             <v-col cols="12" sm="6">
              <v-text-field label="State*" v-model="contact.state" required></v-text-field>
            </v-col>
          </v-row>
        </v-container>
        <small>*indicates required field</small>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
        <v-btn color="blue darken-1" text @click="save">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import api from '@/api'

const props = defineProps({
  modelValue: Boolean,
  contactData: Object,
  isEdit: Boolean
})

const emit = defineEmits(['update:modelValue', 'saved'])

const dialog = ref(props.modelValue)
const contact = ref({})

const formTitle = computed(() => (props.isEdit ? 'Edit Contact' : 'New Contact'))

watch(() => props.modelValue, (val) => {
  dialog.value = val
  if (val) {
    contact.value = { ...props.contactData }
  } else {
    contact.value = {}
  }
})

const searchCep = async () => {
  const cep = contact.value.cep?.replace(/\D/g, '');
  if (cep && cep.length === 8) {
    try {
      const response = await api.get(`/viacep/${cep}`)
      const data = response.data
      contact.value.street = data.logradouro
      contact.value.neighborhood = data.bairro
      contact.value.city = data.localidade
      contact.value.state = data.uf
    } catch (error) {
      console.error('Failed to search CEP:', error)
      // TODO: show error to user
    }
  }
}

const save = async () => {
  try {
    if (props.isEdit) {
      await api.put(`/contacts/${contact.value.id}`, contact.value);
    } else {
      await api.post('/contacts', contact.value);
    }
    emit('saved');
    close();
  } catch (error) {
    console.error('Failed to save contact:', error);
    // TODO: Show validation errors to user
  }
}

const close = () => {
  emit('update:modelValue', false)
}
</script>
