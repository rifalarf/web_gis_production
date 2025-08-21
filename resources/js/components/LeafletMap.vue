<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import * as L from 'leaflet'
import type { FeatureCollection, Feature } from 'geojson'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'
import 'leaflet.markercluster'        // adds L.MarkerClusterGroup
import 'leaflet-gps/dist/leaflet-gps.min.css';
import 'leaflet-gps';                 // adds L.Control.Gps to the runtime
import 'leaflet.browser.print/dist/leaflet.browser.print.js'
import 'leaflet-search/dist/leaflet-search.min.css'
import 'leaflet-search'
import 'leaflet.fullscreen/Control.FullScreen.css'
import 'leaflet.fullscreen'
import 'leaflet-easybutton/src/easy-button.css'
import 'leaflet-easybutton'
/* ───────── damage-point popup  ───────── */
import { router } from '@inertiajs/vue3'   //  add this once at top of file
import Viewer       from 'viewerjs'
import 'viewerjs/dist/viewer.css'
import { useToast } from 'vue-toastification'
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose,} from '@/components/ui/dialog'

const toast = useToast()

const showDelDialog = ref(false)          // opened / closed flag
const idToDelete    = ref<number|null>(null)

/* actually delete after the user clicks “Ya” */
function doDelete () {
  if (!idToDelete.value) return
  router.delete(route('kerusakan.destroy', idToDelete.value), {
    preserveState: false,
    onSuccess    : () => {
      toast.success('Marker dihapus')
      map.closePopup()
    },
    onError      : () => toast.error('Gagal menghapus'),
  })
  showDelDialog.value = false
}

/* Inertia passes auth.user to every page; we just read it once */
import { usePage } from '@inertiajs/vue3'
const canDelete = !!usePage().props.auth?.user


/* ── props ─────────────────────────────────────────────── */
const props = defineProps<{
  geojson: FeatureCollection
  pointsGeojson?: FeatureCollection
  autoFit?: boolean
  showLegend?: boolean
  detailPopups?: boolean
  followPoints?: boolean
  formMode?: boolean
  hideDelete?: boolean
}>()

/* ── colour palette ────────────────────────────────────── */
const colour = {
  baik:         '#22c55e',
  sedang:       '#eab308',
  rusak_ringan: '#f97316',
  rusak_berat:  '#ef4444',
  default:      '#9ca3af',
} as const
type KondisiKey = keyof typeof colour

/* ── DOM ref ───────────────────────────────────────────── */
const mapEl = ref<HTMLElement | null>(null)
/* — map instance —*/
let map!: L.Map

/* ── init once mounted ─────────────────────────────────── */
onMounted(() => {
    /* 1. Map & basemap layers */
    const osm  = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '',
        maxZoom: 19,
    })
    const esri = L.tileLayer(
        "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
        {
        attribution:
            '',
        },
    )

    map = L.map(mapEl.value!, { layers: [osm], zoomControl: false }).setView([-7.3, 107.91], 10)


    L.control.zoom({
    position: 'bottomright' // topleft, topright, bottomleft, bottomright
    }).addTo(map);

    const cluster = L.markerClusterGroup({
    showCoverageOnHover: false,
    spiderfyOnMaxZoom:   true,
    })
    map.addLayer(cluster)

    /* 2. Basemap switcher (top-right) */
    const baseMaps = { "Open Street Map": osm, "Esri World Imagery": esri }
    const overlays = { "Titik Kerusakan": cluster }

    L.control.layers(
      baseMaps,
      overlays,
      {
        position:'topright',
        collapsed:true,

      },
    ).addTo(map)


    /* 3. GeoJSON layer with style + pop-ups */
    /* ───────── highlight state ───────── */
    let selectedGroup: string | null = null            // ruas CODE or segment-id

    /* group layers differently for the 2 modes */
    const groupLayers: Record<string, L.Path[]> = {}   // key ⇒ array of paths

    /* helper – make a unique id per segment */
    function segId(f: Feature) {
    // geometry index + STA is usually unique; fall back to stable string
    return `${(f.properties as any).sta}-${L.stamp(f)}`;
    }

    /* ───────── geojson layer ─────────── */
    const lineLayer = L.geoJSON(undefined, {
    style: (feat?: Feature) => {
        const k = (feat?.properties as any)?.kondisi as KondisiKey | undefined
        return { color: colour[k ?? 'default'], weight: 3, lineCap: 'round' }
    },

    onEachFeature: (feat?: Feature, layer?: L.Layer) => {
        if (!feat || !layer) return
        const p    = feat.properties as any
        const code = p.code as string
        const key  = props.detailPopups ? segId(feat) : code   // group key

        /* === build group map === */
        if (!groupLayers[key]) groupLayers[key] = []
        groupLayers[key].push(layer as L.Path)

        /* === popup content === */
        const html = props.formMode
    ? `<div class="space-y-1 text-sm">
        <div><strong>Nama Ruas:</strong> ${p.nm_ruas}</div>
        <div><strong>STA:</strong> ${p.sta ?? '−'}</div>
        </div>`
    : props.detailPopups
        ? /* existing segment-detail popup */
            `<div class="space-y-1 text-sm">
            <div><strong>STA:</strong> ${p.sta ?? '−'}</div>
            <div><strong>Jenis Permukaan:</strong> ${p.jens_perm ?? '−'}</div>
            <div><strong>Kondisi:</strong> ${p.kondisi ?? '−'}</div>
            </div>`
        : /* dashboard popup */
            `<div class="space-y-1 text-sm">
            <div><strong>CODE:</strong> ${code}</div>
            <div><strong>Nama Ruas:</strong> ${p.nm_ruas}</div>
            <div><strong>Panjang:</strong> ${Number(p.panjang ?? 0).toFixed(2)} km</div>
            <div><strong>Kecamatan:</strong> ${p.kecamatan ?? '−'}</div>
            <a href="/ruas-jalan/${code}"
             class="block w-full rounded-md bg-blue-600 !text-white hover:bg-blue-700 text-sm font-medium px-3 py-1 text-center transition-all">
            Detail
          </a>
            </div>`

        layer.bindPopup(html)

        /* === hover highlight === */
        layer.on('mouseover', () => toggleHighlight(key, true))
        layer.on('mouseout',  () => {
        if (selectedGroup !== key) toggleHighlight(key, false)
        })

        /* === click to lock highlight === */
        layer.on('click', () => {
        if (selectedGroup && selectedGroup !== key) toggleHighlight(selectedGroup, false)
        selectedGroup = key
        toggleHighlight(key, true)
        })

        /* clear highlight when popup closed */
        layer.on('popupclose', () => {
        toggleHighlight(key, false)
        selectedGroup = null
        })
    },
    }).addTo(map)


    const pointLayer = L.geoJSON(undefined, {
    pointToLayer: (feat, latlng) => {
        const street = `https://www.google.com/maps?q=&layer=c&cbll=${latlng.lat},${latlng.lng}`
        const marker = L.marker(latlng)
        marker.bindPopup(buildDamagePopup(feat, latlng, street))
        return marker
    },
    })
    cluster.addLayer(pointLayer)

    function buildDamagePopup(feat: Feature, ll: L.LatLng, streetUrl: string) {
        const p   = feat.properties as any
        const lat = ll.lat.toFixed(6)
        const lon = ll.lng.toFixed(6)

        const delBtn = (!props.hideDelete && canDelete)
        ? `<button data-del="${p.id}"
                    class="mt-2 inline-block text-red-600 hover:underline">
            Delete
            </button>`
        : ''



        const img = p.image
            ? `<img data-viewer class="mb-2 w-full rounded cursor-zoom-in object-cover" src="${p.image}">`
            : ''

        const del = canDelete
            ? `<button data-del="${p.id}"
                class="mt-2 inline-block text-red-600 hover:underline">
                Delete
            </button>`
            : ''

          /* ------ form page: show ONLY coord + Street-View ------ */
            if (props.formMode) {
                return `
                <div class="space-y-2 text-sm">
                    <div><strong>Koordinat:</strong> ${lat}, ${lon}</div>

                    <a href="${streetUrl}" target="_blank"
                    class="block w-full rounded-md bg-amber-500 !text-white hover:bg-amber-600
                            text-sm font-medium px-3 py-1 text-center transition-all">
                    Street View
                    </a>
                </div>`
            }

            /* ------ normal pages: full popup ------ */
            return `
                <div class="space-y-2 text-sm">
                    ${img}
                    <div><strong>STA:</strong> ${p.sta ?? '−'}</div>
                    <div><strong>Nama Ruas:</strong> ${p.nm_ruas}</div>
                    <div><strong>Koordinat:</strong> ${lat}, ${lon}</div>

                    <div class="flex pt-2 gap-2">
                    <a href="/kerusakan/${p.id}"
                        class="w-1/2 inline-flex justify-center items-center rounded-md
                                bg-blue-600 !text-white hover:bg-blue-700 text-sm font-medium
                                px-3 py-1 transition-all">
                        Detail
                    </a>
                    <a href="${streetUrl}" target="_blank"
                        class="w-1/2 inline-flex justify-center items-center rounded-md
                                bg-amber-500 !text-white hover:bg-amber-600 text-sm font-medium
                                px-3 py-1 transition-all">
                        Street View
                    </a>
                    </div>

                    ${delBtn}
                </div>`
            }






    /* click on empty map area clears selection */
    map.on('click', (e) => {
    if ((e as any).originalEvent.target.tagName === 'CANVAS') { // tile background
        if (selectedGroup) toggleHighlight(selectedGroup, false)
        selectedGroup = null
    }
    })

    /* ───────── helper: style on/off ───────── */
    function toggleHighlight(key: string, on: boolean) {
    (groupLayers[key] || []).forEach((l) =>
        l.setStyle({
        weight: on ? 6 : 3,
        dashArray: on ? '0' : undefined,   // ← here
        }).bringToFront(),
    )
    }




    /* 4. Reactive load / refresh */
    watch(
        () => props.geojson,
        (data) => {
        if (!data) return
        lineLayer.clearLayers().addData(data as any)

        if (props.autoFit && lineLayer.getBounds().isValid()) {
            map.fitBounds(lineLayer.getBounds(), { padding: [20, 20] })
        }
        },
        { immediate: true },
    )

    watch(
    () => props.pointsGeojson,
    (data) => {
        if (!data) return
        cluster.clearLayers()
        const gj = L.geoJSON(data as any, {
        pointToLayer: (feat, latlng) => {
            const street = `https://www.google.com/maps?q=&layer=c&cbll=${latlng.lat},${latlng.lng}`
            const marker = L.marker(latlng)            // ← default Leaflet pin
            marker.bindPopup(buildDamagePopup(feat, latlng, street))
            return marker
        },
        })
        cluster.addLayer(gj)                            // hand it to cluster

        if (props.followPoints &&
            data.features?.length === 1 &&
            data.features[0].geometry.type === 'Point') {
        const [lon, lat] = (data.features[0].geometry as any).coordinates
        map.setView([lat, lon], map.getZoom() < 14 ? 14 : map.getZoom())
        }
        },
        { immediate: true },
    )



    /* 5. Legend control (bottom-left) */
    if (props.showLegend) {
    /* ── build the inner HTML FIRST ── */
    const legendHtml = Object.entries(colour)
        .filter(([k]) => k !== 'default')
        .map(
        ([k, c]) => `
            <div class="flex items-center gap-2 mb-1 last:mb-0">
            <span style="background:${c};width:24px;height:10px;border-radius:2px;display:inline-block"></span>
            ${k.replace('_', ' ').replace(/\b\\w/g, (l) => l.toUpperCase())}
            </div>`,
        )
        .join('')

    /* ── extend L.Control AFTER legendHtml exists ── */
    const Legend = L.Control.extend({
        options: { position: 'bottomleft' },
        onAdd() {
        const div = L.DomUtil.create(
            'div',
            'rounded-lg bg-white/80 p-2 text-xs shadow backdrop-blur dark:bg-gray-800/80',
        )
        div.innerHTML = legendHtml
        return div
        },
    })

    new Legend().addTo(map)
    }

        // Search
    const search = new (L.control as any).search({
        layer        : lineLayer,
        propertyName : 'nm_ruas',
        marker       : false,
        position     : 'topleft',
        textPlaceholder: 'Cari Jalan…'
    }).addTo(map)

    const Gps = new (L as any).Control.Gps({
        position: 'topleft',
        autoCenter: true,
        accuracy: true,
    }).addTo(map)

    L.control.browserPrint({
        position : 'bottomright',
    }).addTo(map)

    // Full-screen
    const assTo = new (L.control as any).fullscreen({ position: 'bottomright' }).addTo(map)

    const easyButton = new (L as any).easyButton({
    states: [{
        icon      : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M575.8 255.5c0 18-15 32.1-32 32.1l-32 0 .7 160.2c0 2.7-.2 5.4-.5 8.1l0 16.2c0 22.1-17.9 40-40 40l-16 0c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1L416 512l-24 0c-22.1 0-40-17.9-40-40l0-24 0-64c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32l0 64 0 24c0 22.1-17.9 40-40 40l-24 0-31.9 0c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2l-16 0c-22.1 0-40-17.9-40-40l0-112c0-.9 0-1.9 .1-2.8l0-69.7-32 0c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>',
        onClick   : () => map.setView([-7.3, 107.91], 10)
    }],
    position : 'bottomright'
    }).addTo(map)

    /* initialise viewer / delete once popup opens */
    map.on('popupopen', ({ popup }) => {
  const root = popup.getElement() as HTMLElement

  /* zoomable image */
  const img = root.querySelector('[data-viewer]') as HTMLImageElement | null
  if (img) new Viewer(img, { navbar: false, title: false, toolbar: false })

  /* delete */
  const btn = root.querySelector('[data-del]') as HTMLButtonElement | null
  if (btn) {
    btn.onclick = () => {
      idToDelete.value     = Number(btn.dataset.del)
      showDelDialog.value  = true          // just **open** the dialog
    }
  }
})

})

</script>

<template>
  <div ref="mapEl" class="relative h-full w-full" />
   <!-- delete-confirmation dialog -->
  <Dialog v-model:open="showDelDialog">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Hapus Marker?</DialogTitle>
        <DialogDescription>
          Yakin ingin menghapus marker ID&nbsp;<strong>{{ idToDelete }}</strong>?
        </DialogDescription>
      </DialogHeader>

      <DialogFooter>
        <DialogClose as-child>
          <Button variant="secondary">Batal</Button>
        </DialogClose>
        <Button @click="doDelete">Ya</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>

.leaflet-container,
:host {                /* Vue 3 “host” selector */
  position: absolute;
  inset: 0;
  height: 100%;
  width: 100%;
  z-index: 0;
}

::v-deep .leaflet-control-gps {
  width: 34px;
  height: 34px;
  z-index: 0;
}

::v-deep .leaflet-control-gps .gps-button {
  width: 30px;
  height: 30px;

}

::v-deep .leaflet-bar.easy-button-container.leaflet-control .leaflet-control-easybutton-button {
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

::v-deep .leaflet-bar.easy-button-container.leaflet-control svg {
  width: 20px;
  height: 20px;
  display: block;
  margin: 4px auto 0 auto; /* top | horizontal | bottom */
}

::v-deep .leaflet-control-layers label {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

</style>
