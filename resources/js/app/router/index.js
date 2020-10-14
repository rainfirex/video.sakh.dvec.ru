import VueRouter from 'vue-router';
import Main from "../views/Main";
import DataView from "../views/DataView";
import NotFound from "../views/NotFound";

export default new VueRouter({
   routes: [
       {
           path: '/', component: Main, name: 'main', meta: {
               requestAuth: false
           }
       },
       {
           path: '/data-view', component: DataView, name: 'data-view', meta: {
               requestAuth: false
           }
       },
       {
           path: '*', component: NotFound, name: 'not-found', meta: {
               requestAuth: false
           }
       }
   ], mode : 'history'
});