// next.config.js
const TargetsPlugin = require("targets-webpack-plugin");

module.exports = {
  webpack: function (config, { dev }) {
    if (!dev) {
      config.plugins.push(new TargetsPlugin({
        browsers: ["last 2 versions", "chrome >= 41"]
      }))
    }
    return config
  }
}
