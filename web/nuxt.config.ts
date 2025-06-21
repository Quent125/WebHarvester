// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },

  
  // 配置 API 基礎路徑
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL
    }
  },
})
