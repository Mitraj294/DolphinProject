const webpack = require('webpack');
const API_BASE_URL = process.env.VUE_APP_API_BASE_URL 
module.exports = {
  configureWebpack: {
    plugins: [
      new webpack.DefinePlugin({
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false
      })
    ]
  }
  ,
  devServer: {
    proxy: {
      '/api': {
        target: API_BASE_URL,
        changeOrigin: true,
        pathRewrite: { '^/api': '/api' },
      }
    }
  }
}
