import * as L from 'leaflet';
declare module 'leaflet' {
  namespace control {
    function browserPrint(opts?: any): L.Control;
  }
}
