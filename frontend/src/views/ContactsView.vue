<template>
  <v-container fluid>
    <v-app-bar app>
      <v-toolbar-title>Contact List</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn @click="deleteAccountDialog = true" color="error" class="mr-2">Delete Account</v-btn>
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
                  <v-card
                    class="mt-2"
                    @click="selectContact(contact)"
                    :color="selectedContactForMap && selectedContactForMap.id === contact.id ? 'primary' : ''"
                  >
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

    <v-dialog v-model="deleteAccountDialog" persistent max-width="500px">
      <v-card>
        <v-card-title>
          <span class="headline">Delete Account</span>
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete your account? This action cannot be undone.
          All your contacts will be permanently deleted.
          <v-text-field
            v-model="password"
            label="Please enter your password to confirm"
            type="password"
            class="mt-4"
            :error-messages="deleteError"
          ></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="closeDeleteDialog">Cancel</v-btn>
          <v-btn color="red darken-1" text @click="deleteAccount">Delete My Account</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
const deleteAccountDialog = ref(false)
const password = ref('')
const deleteError = ref('')

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
      if (selectedContactForMap.value && selectedContactForMap.value.id === id) {
        selectedContactForMap.value = null; // Limpa o pino do mapa se o contato deletado estava selecionado
      }
    } catch (error) {
      console.error('Failed to delete contact:', error);
    }
  }
}

const logout = async () => {
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Logout failed, but proceeding with client-side cleanup:', error);
  } finally {
    // Esta parte será executada independentemente de sucesso ou falha
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.push('/login');
  }
}

const closeDeleteDialog = () => {
  deleteAccountDialog.value = false;
  password.value = '';
  deleteError.value = '';
}

const deleteAccount = async () => {
  if (!password.value) {
    deleteError.value = 'Password is required.';
    return;
  }
  try {
    await api.delete('/user', {
      data: {
        password: password.value
      }
    });
    // Limpa o local storage e redireciona como no logout
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.push('/login');
    closeDeleteDialog();
  } catch (error) {
    if (error.response && error.response.status === 401) {
      deleteError.value = 'Invalid password.';
    } else {
      deleteError.value = 'An error occurred. Please try again.';
    }
    console.error('Failed to delete account:', error);
  }
}

onMounted(fetchContacts)
</script>
