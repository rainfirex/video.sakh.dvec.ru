export default {
    state: {
        dataView: [],
        dataInfo: { isSuccess : false, countXLS : 0, countResult: 0 },
        linkXls: ''
    },

    getters: {
        getDataInfo(state) {
            return state.dataInfo;
        },
        getData(state) {
            return state.dataView;
        },
        getLinkXls(state, payload) {
            return state.linkXls;
        }
    },

    mutations: {
        setLinkXls(state, payload) {
            state.linkXls = payload;
        },
        setDataInfo(state, payload) {
            state.dataInfo = payload;
        },
        setData(state, payload) {
            state.dataView = payload;
        }
    },

    actions: {
        setLinkXls({commit}, payload) {
            commit('setLinkXls', payload);
        },
        setDataInfo({commit}, payload) {
            commit('setDataInfo', payload);
        },
        setData({commit}, payload) {
            commit('setData', payload);
        }
    }
}