export default {
    state: {
        file: {},
        interval : {
            isChecked: false,
            start: '2020-08-18T10:10',
            end: '2020-08-19T10:10',
        },
    },

    getters: {
        getFile(state) {
            return state.file;
        },
        getInterval(state) {
            return state.interval;
        }
    },

    mutations: {
        setFile(state, payload) {
            state.file = payload;
        },
        setInterval(state, payload) {
            state.interval = payload;
        }
    },

    actions : {
        setFile({commit}, payload) {
            commit('setFile', payload);
        },

        setInterval({commit}, payload) {
            commit('setInterval', payload);
        }
    }
}