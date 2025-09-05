<template>
  <div style="height:100vh; width:100%">
    <l-map :zoom="zoom" :center="center" @ready="onMapReady">
      <l-tile-layer
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        layer-type="base"
        name="OpenStreetMap"
      ></l-tile-layer>
      <l-marker v-if="markerLatLng" :lat-lng="markerLatLng">
        <l-popup>{{ markerPopupContent }}</l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

// Corrige o problema do ícone padrão do Leaflet com o Vite
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: iconRetinaUrl,
  iconUrl: iconUrl,
  shadowUrl: shadowUrl,
});
// Fim da correção

const props = defineProps({
  selectedContact: Object
})

const zoom = ref(13)
const center = ref([-23.550520, -46.633308]) // Posição inicial (São Paulo)
const markerLatLng = ref(null)
const markerPopupContent = ref('')
const leafletMap = ref(null)

const onMapReady = (mapObject) => {
  leafletMap.value = mapObject;
  // If a contact was selected before the map was ready, fly to it now.
  if (props.selectedContact) {
      flyToSelectedContact(props.selectedContact);
  }
};

const flyToSelectedContact = (contact) => {
    if (contact && contact.latitude && contact.longitude && leafletMap.value) {
        const latLng = [parseFloat(contact.latitude), parseFloat(contact.longitude)];
        markerLatLng.value = latLng;
        markerPopupContent.value = contact.name;
        leafletMap.value.flyTo(latLng, 15);
    }
}

watch(() => props.selectedContact, (newContact) => {
  flyToSelectedContact(newContact);
})
</script>
