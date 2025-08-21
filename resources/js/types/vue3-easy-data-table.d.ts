/* simple shim for vue3-easy-data-table */
declare module 'vue3-easy-data-table' {
  import { DefineComponent } from 'vue'
  const EasyDataTable: DefineComponent<Record<string, unknown>, {}, any>
  export default EasyDataTable
}
