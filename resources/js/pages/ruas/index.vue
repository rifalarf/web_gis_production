<!--  resources/js/pages/ruas/index.vue  -->
<script setup lang="ts">
/* ───── imports ───── */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

import Button from '@/components/ui/button/Button.vue';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Eye, Plus, Search, Trash2, Upload } from 'lucide-vue-next';
import { useToast } from 'vue-toastification';

/* ───── props ───── */
const props = defineProps<{
    ruas: {
        code: string;
        nm_ruas: string;
        panjang: number;
    }[];
}>();

const toast = useToast();

/* ───── misc state ───── */
const auth = usePage().props.auth;
const breadcrumbs = [
    { title: 'Dashboard', href: '/' },
    { title: 'Ruas', href: '/ruas-jalan' },
] as BreadcrumbItem[];

/* ───── table spec ───── */
const headers = [
    { text: 'No', value: 'no', width: 60, sortable: true },
    { text: 'Nama Ruas', value: 'nm_ruas', sortable: true },
    { text: 'Panjang', value: 'panjang', sortable: true },
    { text: 'Aksi', value: 'action', width: 110, sortable: false },
];

const rows = computed(() =>
    props.ruas.map((r, i) => ({
        no: i + 1,
        nm_ruas: r.nm_ruas,
        panjang: `${Number(r.panjang ?? 0).toFixed(2)} km`,
        code: r.code,
    })),
);

const search = ref('');

/* ─── delete with soft refresh ─── */
function destroyRow(code: string) {
    router.delete(route('ruas.destroy', code), {
        preserveState: true, // keep current page, only props update
        only: ['ruas'], // controller returns the fresh list
        onSuccess: () => {
            toast.success(`Ruas ${code} dihapus.`);
            /* soft-navigate back to index so toast survives */
            router.visit('/ruas-jalan', { replace: true });
        },
        onError: () => toast.error('Gagal menghapus.'),
    });
}
</script>

<template>
    <Head title="Daftar Jalan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- main panel -->
            <div class="relative rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <!-- Top controls -->
                <div class="mb-6 flex flex-col flex-wrap items-center justify-between gap-4 sm:flex-row">
                    <!-- search -->
                    <div class="relative w-full sm:w-1/2 lg:w-1/3">
                        <input
                            v-model="search"
                            placeholder="Search..."
                            class="rounded-lg border px-4 py-2 pl-10 placeholder-gray-400 shadow-sm dark:border-neutral-600 dark:bg-neutral-800 dark:text-white"
                        />
                        <Search class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    </div>

                    <!-- upload buttons -->
                    <div v-if="auth.user" class="flex gap-2">
                        <Button as="a" :href="route('geojson.form', 'insert')">
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah
                        </Button>
                        <Button as="a" variant="secondary" :href="route('geojson.form', 'update')">
                            <Upload class="mr-2 h-4 w-4" />
                            Update
                        </Button>
                    </div>
                </div>

                <!-- data table -->
                <!-- @ts-expect-error Vue3-Easy-Data-Table types -->
                <EasyDataTable
                    class="w-full"
                    :headers="headers"
                    :items="rows"
                    :search-value="search"
                    :rows-per-page="10"
                    :sort-by="'no'"
                    alternating
                    header-text-direction="left"
                    body-row-class="border-t border-gray-200 dark:border-gray-700"
                    table-class="border-collapse text-base"
                    style="--easy-table-body-row-height: 50px; --easy-table-header-height: 50px; --easy-table-footer-height: 50px"
                >
                    <!-- action column -->
                    <!-- @vue-ignore -->
                    <template #item-action="{ code, nm_ruas }">
                        <div class="flex gap-2 text-neutral-600 dark:text-neutral-300">
                            <Link :href="`/ruas-jalan/${code}`" class="hover:text-blue-600">
                                <Eye :size="20" />
                            </Link>

                            <!-- delete with confirmation dialog -->
                            <Dialog>
                                <DialogTrigger as-child>
                                    <div v-if="auth.user">
                                        <button class="hover:text-red-600">
                                            <Trash2 :size="20" />
                                        </button>
                                    </div>
                                </DialogTrigger>

                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Hapus Ruas?</DialogTitle>
                                        <DialogDescription>
                                            Yakin ingin menghapus ruas <strong>{{ nm_ruas }}</strong
                                            >?
                                        </DialogDescription>
                                    </DialogHeader>

                                    <DialogFooter>
                                        <DialogClose as-child>
                                            <Button variant="secondary">Batal</Button>
                                        </DialogClose>
                                        <Button @click="destroyRow(code)">Ya</Button>
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
    font-size: 1rem; /* Tailwind text-base */
    line-height: 1.5rem;
}
</style>
