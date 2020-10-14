<template>
    <div class="wrapper">
        <Header></Header>
        <Body></Body>
        <Footer></Footer>
    </div>
</template>

<script>
    import Header from "./components/structures/Header";
    import Body from "./components/structures/Body";
    import Footer from "./components/structures/Footer";
    export default {
        name: "App",
        components: {
            Header,
            Body,
            Footer
        }
    }
</script>

<style scoped>

</style>