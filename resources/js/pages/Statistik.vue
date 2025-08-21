<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head }  from '@inertiajs/vue3'
import PieCard   from '@/components/PieCard.vue'
import StatCard  from '@/components/StatCard.vue'

const props = defineProps<{
  totals:{
    baik:number, sedang:number, rgn:number, rusak:number,
    mntp:number, t_mntp:number, ruas:number, panjang:number
  }
}>()

/* pie-chart data ------------------------------------------------ */
const kondisiLabels = ['Baik','Sedang','Rusak Ringan','Rusak Berat']
const kondisiData   = [
  props.totals.baik, props.totals.sedang,
  props.totals.rgn,  props.totals.rusak,
]
const kondisiColors = ['#22c55e','#eab308','#f97316','#ef4444']

const mntpLabels = ['Mantap','Tidak Mantap']
const mntpData   = [props.totals.mntp, props.totals.t_mntp]
const mntpColors = ['#22c55e','#ef4444']

/* icons (simple paths) ----------------------------------------- */
const mapPath   = 'M4 3H20V5H4V3ZM4 7H20V9H4V7ZM4 11H20V13H4V11ZM4 15H20V17H4V15ZM4 19H20V21H4V19Z'
const rulerPath = 'M4 3H20V5H4V3ZM4 7H20V9H4V7ZM4 11H20V13H4V11ZM4 15H20V17H4V15ZM4 19H20V21H4V19Z'
</script>

<template>
  <Head title="Statistik" />

  <AppLayout :breadcrumbs="[{ title:'Dashboard',           href:'/' },{ title:'Statistik', href:'/statistik' }]">
    <!-- two-col grid on md, single col on mobile -->
    <div class="flex flex-col items-center gap-6 p-6 md:grid md:grid-cols-2">

      <!-- stat cards -->
      <StatCard
        :icon="mapPath"
        label="Total Ruas Jalan"
        :value="props.totals.ruas"
      />
      <StatCard
        :icon="rulerPath"
        label="Total Panjang"
        :value="`${Number(props.totals.panjang ?? 0)
                    .toFixed(2)             // 7.10
                    .replace(/\\.00$/, '')  // 7.1 → 7
                    } km`"
        />

      <!-- pie cards -->
      <PieCard
        title="Proporsi Kondisi (km)"
        :labels="kondisiLabels"
        :data="kondisiData"
        :colors="kondisiColors"
      />

      <PieCard
        title="Kemantapan (km)"
        :labels="mntpLabels"
        :data="mntpData"
        :colors="mntpColors"
      />
    </div>
  </AppLayout>
</template>
