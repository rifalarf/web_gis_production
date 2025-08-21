<!--  resources/js/pages/kerusakan/Form.vue  -->
<script setup lang="ts">
/* ── imports ─────────────────────────────────────────── */
import { Head, useForm, router }      from '@inertiajs/vue3'
import AppLayout                      from '@/layouts/AppLayout.vue'
import LeafletMap                     from '@/components/LeafletMap.vue'
import vSelect                        from 'vue-select'
import 'vue-select/dist/vue-select.css'

import Button from '@/components/ui/button/Button.vue'
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose,} from '@/components/ui/dialog'

import { ref, watch, onMounted }      from 'vue'
import * as L                         from 'leaflet'
import 'leaflet/dist/leaflet.css'
import exifr                           from 'exifr'
import { useToast }                   from 'vue-toastification'
import type { BreadcrumbItem }        from '@/types'
import type { FeatureCollection }     from 'geojson'

/* ── runtime helpers ─────────────────────────────────── */
const toast = useToast()

/* ── props ────────────────────────────────────────────── */
const props = defineProps<{
  mode: 'create' | 'edit'
  marker?: {
    id: number
    ruas_code: string
    sta: string | null
    lat: number
    lon: number
    image: string | null
  }
  ruasOptions: { value: string; label: string }[]
}>()

/* ── form state ───────────────────────────────────────── */
const form = useForm({
  ruas_code: props.marker?.ruas_code ?? '',
  sta      : props.marker?.sta       ?? '',
  lat      : props.marker?.lat       ?? null,
  lon      : props.marker?.lon       ?? null,
  photo    : null as File | null,
})

/* ── preview image ───────────────────────────────────── */
const preview = ref<string | null>(props.marker?.image ?? null)
watch(() => form.photo, f => {
  preview.value = f ? URL.createObjectURL(f) : props.marker?.image ?? null
  if (f) extractGps(f)
})

/* ── auto-GPS from EXIF ──────────────────────────────── */
async function extractGps (file: File) {
  try {
    const { latitude, longitude } = await (exifr as any).gps(file, true)
    if (latitude && longitude) {
      form.lat = +latitude.toFixed(6)
      form.lon = +longitude.toFixed(6)
      placePin(form.lat, form.lon)
    }
  } catch {/* ignore */}
}

/* ── leaflet point preview ───────────────────────────── */
const pinGJ = ref<FeatureCollection>({ type:'FeatureCollection', features:[] })

function placePin (lat:number, lon:number) {
  form.lat = lat
  form.lon = lon
  pinGJ.value = {
    type:'FeatureCollection',
    features:[{
      type:'Feature',
      geometry  : { type:'Point', coordinates:[lon,lat] },
      properties: {},
    }],
  }
}
if (props.mode === 'edit' && props.marker?.lat) {
  placePin(props.marker.lat, props.marker.lon)
}
watch([() => form.lat, () => form.lon], ([la,lo]) => {
  if (la!==null && lo!==null && !isNaN(la) && !isNaN(lo)) placePin(la,lo)
})

/* ── submit helpers ──────────────────────────────────── */
function reallySubmit () {
    if (!form.ruas_code) return toast.error('Pilih Nama Ruas terlebih dahulu.')
    if (props.mode === 'create' && !form.photo) {return toast.error('Upload foto terlebih dahulu.')}
    if (!form.sta) return toast.error('Isi STA terlebih dahulu.')
    if (!form.lat || !form.lon) return toast.error('Masukkan koordinat yang valid.')

  const target = props.mode === 'create'
    ? route('kerusakan.store')
    : route('kerusakan.update', props.marker!.id)

  const req = props.mode === 'create'
    ? form.post(target, { onSuccess })
    : form.transform(d => ({ ...d, _method:'PUT' }))
          .post(target, { onSuccess })

  function onSuccess () {
    toast.success(`Marker ${props.mode==='create' ? 'ditambahkan' : 'diperbarui'}`)
    router.visit(route('kerusakan.index'))      // ← back to index, keeps toast
  }
}

/* ── load all line segments for background map ───────── */
const lines = ref<FeatureCollection>()
onMounted(async () => {
  lines.value = await fetch('/api/segments.geojson').then(r=>r.json())
})

/* ── breadcrumbs ─────────────────────────────────────── */
const breadcrumbs:BreadcrumbItem[] = [
  { title:'Dashboard',        href:'/' },
  { title:'Titik Kerusakan',  href:'/kerusakan' },
  { title: props.mode==='create' ? 'Tambah' : `Edit #${props.marker?.id}`, href:'#' },
]

function ruasValue(o:{value:string}){ return o.value }
</script>

<template>
  <Head :title="props.mode === 'create' ? 'Tambah Marker' : 'Edit Marker'" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-12">
      <div class="mx-auto max-w-7xl flex flex-col gap-6 lg:flex-row lg:gap-8 sm:px-6">

        <!-- ◀ Form card -->
        <div class="w-full lg:w-1/3">
          <div class="rounded-xl border p-6 dark:border-sidebar-border space-y-4 text-sm">

            <!-- Nama Ruas -->
            <div>
              <label class="block mb-1 font-medium">Nama Ruas</label>
              <v-select
                :options="props.ruasOptions"
                v-model="form.ruas_code"
                :reduce="ruasValue"
                placeholder="Pilih ruas…"
              />
            </div>

            <!-- STA -->
            <div>
              <label class="block mb-1 font-medium">STA</label>
              <input v-model="form.sta" type="text"
                     class="w-full rounded border px-3 py-2
                            dark:border-neutral-600 dark:bg-neutral-800" />
            </div>

            <!-- Photo -->
            <div>
              <label class="block mb-1 font-medium">Foto</label>
              <input
                type="file"
                accept="image/*"
                class="file:mr-2 file:rounded-lg file:border file:border-gray-300
                       file:bg-white file:px-3 file:py-1 block w-full rounded border px-3 py-2
                       text-sm dark:border-neutral-600 dark:bg-neutral-800"
                @change="e=>{
                  const f=(e.target as HTMLInputElement).files?.[0]||null
                  form.photo=f
                }"
              />
              <img v-if="preview" :src="preview"
                   class="mt-3 w-full max-h-64 rounded object-contain" />
            </div>

            <!-- Koordinat -->
            <div>
              <label class="block mb-1">Latitude</label>
              <input v-model.number="form.lat" type="number" step="0.000001"
                     class="w-full rounded border px-3 py-2
                            dark:border-neutral-600 dark:bg-neutral-800" />
            </div>
            <div>
              <label class="block mb-1">Longitude</label>
              <input v-model.number="form.lon" type="number" step="0.000001"
                     class="w-full rounded border px-3 py-2
                            dark:border-neutral-600 dark:bg-neutral-800" />
            </div>

            <!-- Save button with confirmation dialog -->
            <Dialog>
              <DialogTrigger as-child>
                <Button class="w-full">
                  {{ props.mode === 'create' ? 'Simpan' : 'Perbarui' }}
                </Button>
              </DialogTrigger>

              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Konfirmasi</DialogTitle>
                  <DialogDescription>
                    Yakin ingin
                    {{ props.mode === 'create' ? 'menyimpan marker baru?' : 'memperbarui marker ini?' }}
                  </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                  <DialogClose as-child>
                    <Button variant="secondary">Batal</Button>
                  </DialogClose>
                  <Button @click="reallySubmit">Ya</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>

          </div>
        </div>

        <!-- ▶ Map panel -->
        <div class="w-full lg:w-2/3">
          <div class="relative h-[300px] sm:h-[400px] lg:h-[600px]
                      rounded-xl border dark:border-sidebar-border">
            <LeafletMap
              v-if="lines"
              :geojson="lines"
              :points-geojson="pinGJ"
              :followPoints="true"
              :formMode="true"
              :showLegend="true"
              :detailPopups="true"
              :hideDelete="true"
              class="absolute inset-0 rounded-xl"
            />
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
