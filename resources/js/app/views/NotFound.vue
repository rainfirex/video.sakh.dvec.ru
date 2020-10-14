<template>
    <div class="container">
            <div>
                <h2><b><i>ОШИБКА 404</i></b></h2>
                <p>Вы могли неправильно ввести адрес.</p>
                <p>Или страница была удалена.</p>
                <p>Собственно, смотреть здесь не на что ...</p>
                <p>Нажмите на ссылки ниже, чтобы перейти на главную, спасибо!</p>
                <div class="link-bottom">
                    <router-link class="link-icon" to="/">
                        <i class="icon ion-ios-home"></i> Главная</router-link>
                </div>
            </div>
    </div>
</template>

<script>
    export default {
        name: "NotFound"
    }
</script>

<style scoped>
    .container{
        display: flex;
        align-items: center;
        justify-content: space-around;
        height: 50vh;
        background: #f1f7fd;
        margin-top: 5vh;
    }
</style>
