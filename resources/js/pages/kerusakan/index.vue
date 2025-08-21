<!--  resources/js/pages/kerusakan/index.vue  -->
<script setup lang="ts">
/* ───────── imports ───────── */
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import { ref, computed }               from 'vue'

import AppLayout          from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'

import EasyDataTable      from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import Button             from '@/components/ui/button/Button.vue'

import {
  Dialog, DialogTrigger, DialogContent,
  DialogHeader, DialogTitle, DialogDescription,
  DialogFooter, DialogClose,
}                         from '@/components/ui/dialog'

import { Eye, Pencil, Trash2, Search, Plus } from 'lucide-vue-next'
import { useToast }                     from 'vue-toastification'

/* ───────── misc state ───────── */
const toast        = useToast()
const auth         = usePage().props.auth
const breadcrumbs  = [
  { title:'Dashboard', href:'/' },
  { title:'Titik Kerusakan', href:'/kerusakan' },
] as BreadcrumbItem[]

/* ───────── props ───────── */
const props = defineProps<{
  markers: {
    id: number
    sta: string | null
    nm_ruas: string
    lat: number
    lon: number
  }[]
}>()

/* ───────── table data ───────── */
const headers = [
  { text:'No',        value:'no',       width:60,  sortable:true  },
  { text:'STA',       value:'sta',                  sortable:true  },
  { text:'Nama Ruas', value:'nm_ruas',              sortable:true  },
  { text:'Koordinat', value:'coord',                sortable:true  },
  { text:'Aksi',      value:'action',   width:110, sortable:false },
]

const rows = computed(() =>
  props.markers.map((m,i)=>({
    id      : m.id,
    no      : i+1,
    sta     : m.sta ?? '–',
    nm_ruas : m.nm_ruas,
    coord   : `${m.lat.toFixed(6)}, ${m.lon.toFixed(6)}`,
  })),
)

const globalSearch = ref('')

/* ───────── delete helper ───────── */
function destroyRow (id:number){
  router.delete(route('kerusakan.destroy', id),{
    onSuccess: () => toast.success('Titik kerusakan dihapus'),
    onError  : () => toast.error('Gagal menghapus'),
  })
}
</script>

<template>
  <Head title="Titik Kerusakan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 flex-col gap-4 p-4 rounded-xl">

      <!-- table card -->
      <div class="relative rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">

        <!-- top controls -->
        <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 flex-wrap">
          <!-- search -->
          <div class="relative w-full sm:w-1/2 lg:w-1/3">
            <input
              v-model="globalSearch"
              placeholder="Search..."
              class="rounded-lg border px-4 py-2 pl-10 shadow-sm
                     placeholder-gray-400 dark:bg-neutral-800 dark:border-neutral-600 dark:text-white"
            />
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          </div>

          <!-- add -->
          <div v-if="auth.user" class="w-full sm:w-auto">
            <Button as="a" href="/kerusakan/create" class="w-full sm:w-auto text-center">
                <Plus class="w-4 h-4" />
                Tambah
            </Button>
          </div>
        </div>

        <!-- data table -->
        <!-- @ts-expect-error vue3-easy-data-table types -->
        <EasyDataTable
          class="w-full"
          :headers="headers"
          :items="rows"
          :search-value="globalSearch"
          :rows-per-page="10"
          :sort-by="'no'"
          alternating
          header-text-direction="left"
          body-row-class="border-t border-gray-200 dark:border-gray-700"
          table-class="border-collapse text-base"
          style="
            --easy-table-body-row-height : 50px;
            --easy-table-header-height   : 50px;
            --easy-table-footer-height   : 50px;
          "
        >
          <!-- action column -->
          <!-- @vue-ignore -->
          <template #item-action="{ id, coord }">
            <div class="flex gap-2 text-neutral-600 dark:text-neutral-300">
              <Link :href="`/kerusakan/${id}`" class="hover:text-blue-600">
                <Eye :size="20"/>
              </Link>

              <Link
                v-if="auth.user"
                :href="`/kerusakan/${id}/edit`"
                class="hover:text-yellow-500"
              >
                <Pencil :size="20"/>
              </Link>

              <!-- delete with confirmation dialog -->
              <Dialog v-if="auth.user">
                <DialogTrigger as-child>
                  <button class="hover:text-red-600">
                    <Trash2 :size="20"/>
                  </button>
                </DialogTrigger>

                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Hapus Marker?</DialogTitle>
                    <DialogDescription>
                      Yakin ingin menghapus marker ID&nbsp;<strong>{{ coord }}</strong>?
                    </DialogDescription>
                  </DialogHeader>

                  <DialogFooter>
                    <DialogClose as-child>
                      <Button variant="secondary">Batal</Button>
                    </DialogClose>
                    <Button @click="destroyRow(id)">Ya</Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>
            </div>
          </template>
        </EasyDataTable>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* upscale table text */
::v-deep(.vue3-easy-data-table__body td),
::v-deep(.vue3-easy-data-table__header .header-text),
::v-deep(.vue3-easy-data-table__footer),
::v-deep(.vue3-easy-data-table__footer *) {
  font-size  : 1rem;
  line-height: 1.5rem;
}

</style>
