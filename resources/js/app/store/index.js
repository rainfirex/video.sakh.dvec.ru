import Vue from 'vue';
import Vuex from 'vuex';
import dataView from "./dataView";
import app from './app';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: { app, dataView }
})