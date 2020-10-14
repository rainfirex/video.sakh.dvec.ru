<template>
    <div class="form-group">

        <div style="font-size: 0.8em; line-height: 20px" class="text-center">
            <label>Загрузка файлов</label>
        </div>
        <div class="custom-file d-none">
            <input type="file" class="custom-file-input"
                   @change="selectFiles"
                   ref="fileInput"
                   accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
            <label class="custom-file-label">Файл...</label>
        </div>

        <div class="drop-container mt-2 form-control" ref="dropContainer" tabindex="7"
             @dragenter="dropEnter"
             @drop="dropFiles"
             @dragleave="dropLeave"
             @dragover="dropOver"
             @click="$refs.fileInput.click()">
            <p class="mb-0">Нажмите или перетащите в эту область файл формата: .xls, .xlsx</p>
        </div>

        <div class="mt-3 mb-3" v-if="file.name">
            <div class="text-center alert alert-primary">
                <p class="mb-0">Файл <b>"{{ file.name }}"</b> для обработки.
                    <i class="fa fa-trash-o ico-img-remove" @click="removeFile" title="Убрать файл"></i>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "DropFiles",
        computed: {
            file() {
                return this.$store.getters.getFile;
            }
        },
        methods: {

            setFile(file) {
                this.$store.dispatch('setFile', file);
            },

            initFile(file) {
                let extFile = file.name.substr(file.name.lastIndexOf(".") + 1, file.name.length).toLowerCase();
                if (extFile === 'xls' || extFile === 'xlsx') {
                    this.setFile(file);
                }
            },

            selectFiles(e) {
                e.preventDefault();

                if (e.target.files.length <= 0) return;

                const file = e.target.files[0];

                this.initFile(file);
            },

            dropFiles(e) {
                e.preventDefault();
                e.stopPropagation();

                const file = e.dataTransfer.files[0];

                this.initFile(file);

                this.$refs.dropContainer.classList.remove('drop-container-enter');
            },

            dropEnter(e) {
                e.preventDefault();
                e.stopPropagation();
                this.$refs.dropContainer.classList.add('drop-container-enter');
            },

            dropLeave(e) {
                e.preventDefault();
                e.stopPropagation();
                this.$refs.dropContainer.classList.remove('drop-container-enter');
            },

            dropOver(e) {
                e.preventDefault();
                e.stopPropagation();
            },

            removeFile(){
                this.file = {};
            }
        }
    }
</script>

<style lang="scss" scoped>
    .custom-file-label::after{content:"Обзор" !important;}
    .drop-container {
        background-color: #f0f1f2;
        height: 100px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #809a9e;
        font-size: 11px;
        cursor: pointer;
        transition: 1s;
        line-height: 35px;
        overflow: hidden;
        &:hover{
            background-color: #fdfeff;
        }
    }

    .drop-container-enter{
        border: solid 1px #7698c2;
        box-shadow: 0 0 0 0.2rem rgb(0 135 255 / 25%);
    }
</style>