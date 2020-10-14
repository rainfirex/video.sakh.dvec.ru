<template>
    <div class="main">
        <h3 class="title text-center mb-4">Формирование данных</h3>

        <DropFiles @sendFile=" resetData(); setFile($event);  "/>

        <div class="form text-center p-3">
            <form @submit.prevent="submit">
<!--                <div class="form-group form-check row">-->
<!--                    <label class="form-check-label">-->
<!--                        <input type="checkbox" class="form-check-input" @change="checkedInterval" v-model="interval.isChecked" :disabled="isLoad">-->
<!--                        Использовать интервал дат-->
<!--                    </label>-->
<!--                </div>-->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Начальная</label>
                    <div class="col-sm-10">
                        <input type="datetime-local" class="form-control" @change="changeDateStart" v-model="interval.start" :disabled="isLoad">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Конечная</label>
                    <div class="col-sm-10">
                        <input type="datetime-local" class="form-control" @change="changeDateEnd" v-model="interval.end" :disabled="isLoad">
                    </div>
                </div>
                <div>
                    <button class="btn btn-dark" :disabled="isLoad" >Формировать</button>
                </div>
            </form>
        </div>

        <div v-if="isError" class="alert alert-danger m-4 text-center">
            <p>{{ message }}</p>
        </div>

        <div v-if="dataInfo.isSuccess" class="alert alert-success m-4">
            <h3 class="text-center p-2">Данные готовы</h3>
            <p>Строк в файле: {{ dataInfo.countXLS }}</p>
            <p>Кол-во элементов в массиве: {{ dataInfo.countResult }}</p>
            <p>Скачать <a :href="linkXls">xls</a> файл.</p>
            <div class="text-center">
                <button class="btn btn-info" @click="openDataView">Просмотреть данные</button>
            </div>
        </div>

    </div>
</template>

<script>
    import DropFiles from '../components/DropFiles';
    export default {
        name: "Main",
        components: {
            DropFiles
        },
        data() {
            return {
                isLoad: false,
                isError: false,
                message: '',
                intervalTmp: {
                    isChecked: false,
                    start: 0,
                    end: 0
                }
            }
        },
        computed: {
            linkXls() {
                return this.$store.getters.getLinkXls;
            },
            dataInfo() {
                return this.$store.getters.getDataInfo;
            },
            file() {
                return this.$store.getters.getFile;
            },
            interval() {
                return this.$store.getters.getInterval;
            }
        },
        methods: {

            checkedInterval(e) {
                this.intervalTmp.isChecked = e.target.checked;
            },

            changeDateStart(e) {
                this.intervalTmp.start = e.target.value;
            },

            changeDateEnd(e) {
                this.intervalTmp.end = e.target.value;
            },

            submit() {
                if (!this.file.name) {
                    alert('Файл не выбран!');
                    return false;
                }

                this.isError   = false;
                this.message   = '';
                this.$store.dispatch('setDataInfo', { countXLS: 0, isSuccess: false });
                this.$store.dispatch('setData', []);

                const frmData = new FormData();
                frmData.append('file', this.file);
                frmData.append('isInterval', this.interval.isChecked);
                frmData.append('startInterval', this.interval.start);
                frmData.append('endInterval', this.interval.end);

                this.isLoad = true;
                axios.post(`/api/format-time`, frmData)
                    .then(response => {
                        if (response.data.success) {
                            const isSuccess = true;
                            const countXLS  = response.data.countXLS;
                            const linkXls = response.data.$linkXls;
                            const countResult = response.data.countResult;

                            this.$store.dispatch('setData',  response.data.resultArray);
                            this.$store.dispatch('setInterval',  this.intervalTmp);
                            this.$store.dispatch('setDataInfo',  {isSuccess, countXLS, countResult});
                            this.$store.dispatch('setLinkXls',  linkXls);
                        } else {
                            this.isError = true;
                            if (response.data.message && response.data.line)
                                this.message = 'Ошибка: ' + response.data.message + '. ' + response.data.line;
                            else
                                this.message = 'Ошибка:';
                        }
                    })
                    .catch(e => {
                        this.isError = true;
                        this.message = e.response.data.message;
                    })
                    .finally(() => {this.isLoad = false;});
            },

            resetData() {
                this.isError   = false;
                this.message   = '';
                this.$store.dispatch('setDataInfo', { countXLS: 0, countResult: 0, isSuccess: false });
                this.$store.dispatch('setData', []);
                this.$store.dispatch('setFile', {});
            },

            openDataView() {
                this.$router.push({ name: 'data-view' })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .main {
        max-width: 650px;
        margin: 0 auto;
    }
    .form {
        border: solid 1px #d2d2d2;
        background: white;
    }
</style>