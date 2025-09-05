<template>
  <v-container fluid>
    <v-app-bar app>
      <v-toolbar-title>Contact List</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn @click="logout">Logout</v-btn>
    </v-app-bar>
    <v-main>
      <v-container fluid>
        <v-row>
          <v-col cols="12" md="5">
             <!-- Coluna da Lista de Contatos -->
             <v-row>
                <v-col cols="12" md="6">
                  <h1>My Contacts</h1>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="search"
                    label="Search by name or CPF"
                    prepend-inner-icon="mdi-magnify"
                    variant="outlined"
                    dense
                    hide-details
                  ></v-text-field>
                </v-col>
              </v-row>
              <v-row class="mt-4">
                <v-col cols="12" class="d-flex justify-end">
                  <v-btn color="primary" @click="openNewContactDialog">New Contact</v-btn>
                </v-col>
              </v-row>
              <v-row style="max-height: 80vh; overflow-y: auto;">
                <v-col v-for="contact in contacts" :key="contact.id" cols="12">
                  <v-card class="mt-2" @click="selectContact(contact)">
                    <v-card-title>{{ contact.name }}</v-card-title>
                    <v-card-subtitle>{{ contact.cpf }}</v-card-subtitle>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn icon @click.stop="openEditContactDialog(contact)">
                        <v-icon>mdi-pencil</v-icon>
                      </v-btn>
                      <v-btn icon @click.stop="deleteContact(contact.id)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-col>
              </v-row>
          </v-col>
          <v-col cols="12" md="7">
            <!-- Coluna do Mapa -->
            <MapView :selected-contact="selectedContactForMap" />
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <ContactForm 
      v-model="dialog" 
      :contact-data="selectedContact" 
      :is-edit="isEdit"
      @saved="handleSave"
    />

  </v-container>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import ContactForm from '@/components/ContactForm.vue'
import MapView from '@/components/MapView.vue'

const router = useRouter()
const contacts = ref([])
const dialog = ref(false)
const isEdit = ref(false)
const selectedContact = ref({})
const search = ref('')
const selectedContactForMap = ref(null)

const fetchContacts = async () => {
  try {
    const response = await api.get('/contacts', {
      params: {
        search: search.value
      }
    });
    contacts.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch contacts:', error);
  }
}

watch(search, () => {
  fetchContacts();
});

const selectContact = (contact) => {
  selectedContactForMap.value = contact
}

const openNewContactDialog = () => {
  isEdit.value = false
  selectedContact.value = {}
  dialog.value = true
}

const openEditContactDialog = (contact) => {
  isEdit.value = true
  selectedContact.value = { ...contact }
  dialog.value = true
}

const handleSave = () => {
  fetchContacts()
}

const deleteContact = async (id) => {
  if (confirm('Are you sure you want to delete this contact?')) {
    try {
      await api.delete(`/contacts/${id}`);
      fetchContacts();
    } catch (error) {
      console.error('Failed to delete contact:', error);
    }
  }
}

const logout = async () => {
  try {
    await api.post('/logout');
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
  }
}

onMounted(fetchContacts)
</script>
