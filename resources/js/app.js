window.Vue = require('vue').default;

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('map-component', require('./components/MapComponent.vue').default);
Vue.component('measure-component', require('./components/MeasureComponent.vue').default);

const app = new Vue({
    el: '#app',
});
