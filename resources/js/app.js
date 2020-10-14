import VueRouter from 'vue-router';
import App from './app/App';
import router from './app/router';
import store from './app/store';

import './app/assets/scss/index.scss';

require('./bootstrap');
window.Vue = require('vue');

Vue.use(VueRouter);

const app = new Vue({
    router,
    store,
    el: '#app',
    render: h => h(App)
});