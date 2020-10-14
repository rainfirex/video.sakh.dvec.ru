<template>
    <div class="data-view">
        <section>
            <div class="mb-4 text-center">
                <h2 class="mb-3">Данные</h2>
                <p v-if="search.length < 10">Страница <i>{{ page.current}}</i> из <i>{{ page.count }}</i></p>

                <div class="text-center navigator-panel mb-4">
                    <div class="navigator-control" v-if="search.length < 10" @click="prevPage"><span>&#8592;</span></div>
                    <button class="btn btn-info" @click="openMain">Вернуться</button>
                    <div class="navigator-control" v-if="search.length < 10" @click="nextPage"><span>&#8594;</span></div>
                </div>

                <div class="form-group row">
                    <label class="offset-1 col-sm-4 col-form-label">Поиск</label>
                    <div class="col-sm-5">
                        <input class="form-control" type="text" v-model="search" placeholder="02-08-2020 21:36:55 - 05-08-2020 08:38:21; 10-08-2020 08:06:14 - 10-08-2020 17:41:20;">
                    </div>
                </div>

            </div>

            <table class="table" v-if="dataSort.length > 0">
                <thead>
                <tr>
                    <th>АДРЕС</th>
                    <th>IP РЕКОРДЕРА</th>
                    <th>ПЕРИОДЫ ЗАПИСИ</th>
                    <th>Время доступной записи</th>
                    <th>Время отсутствующей записи</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(r, index) in dataSort" :key="index">
                    <td>{{ r.address }}</td>
                    <td>{{ r.ip }}</td>
                    <td :title="r.sourceMax">{{ r.sourceMin }}</td>
                    <td>{{ r.timeCommon }}</td>
                    <td>{{ r.timeStop }}</td>
                </tr>
                </tbody>
            </table>

            <div class="text-center navigator-panel" v-if="dataSort.length > 0">
                <div class="navigator-control" v-if="search.length < 10" @click="prevPage"><span>&#8592;</span></div>
                <button class="btn btn-info" @click="openMain">Вернуться</button>
                <div class="navigator-control" v-if="search.length < 10" @click="nextPage"><span>&#8594;</span></div>
            </div>
        </section>
    </div>
</template>

<script>
    export default {
        name: "TableData",
        data() {
            return {
                search: '',
                page: {
                    current: 1, //тек. страница,
                    count: 0,
                    length: 20 // Элементов на странице
                }
            }
        },
        computed: {
            dataSort() {
                if (this.search.length >= 10) {
                    return this.$store.getters.getData.filter((item) => (item.sourceMax.indexOf(this.search) !== -1));
                } else {
                    return this.$store.getters.getData.filter((row, index) => {
                        let start = (this.page.current - 1) * this.page.length;
                        let end = this.page.current * this.page.length;
                        if (index >= start && index < end) return true;
                    });
                }
            }
        },
        methods: {
            prevPage() {
                if (this.page.current > 1) {
                    this.page.current -= 1
                }
            },
            nextPage() {
                if ((this.page.current * this.page.length) < this.$store.getters.getData.length) {
                    this.page.current += 1;
                }
            },
            openMain() {
                this.$router.push({ name: 'main' })
            }
        },
        created() {
            this.page.count = Math.ceil(this.$store.getters.getData.length / this.page.length);

            if (this.dataSort.length < 1) this.$router.push({ name: 'main' });
        }
    }
</script>

<style lang="scss" scoped>
    .data-view{
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
    }
    .navigator-panel{
        display: flex;
        justify-content: center;
        .navigator-control{
            cursor: pointer;
            width: 100px;
            background: #17a2b8;
            color: #ffffff;
            margin: 0 15px;
            border-radius: 10px;
            font-size: 20px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            align-items: center;
            justify-content: center;
            display: flex;
            transition: 1s;
        }
    }

    .table thead th {
        text-align: center;
        vertical-align: middle;
    }
</style>