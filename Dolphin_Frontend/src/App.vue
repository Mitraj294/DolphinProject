<template>
  <div id="app">
    <Toast />
    <component :is="layoutComponent">
      <router-view />
    </component>
  </div>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';

export default {
  name: 'App',
  computed: {
    layoutComponent() {
      const layout = this.$route.meta.layout;
      if (layout === 'main') return MainLayout;
      return 'div';
    },
  },
};
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
