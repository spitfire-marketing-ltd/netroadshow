// server.js

const express = require("express");
const path = require("path");
const history = require("connect-history-api-fallback");
// ^ middleware to redirect all URLs to index.html

const app = express();
const staticFileMiddleware = express.static(path.join(__dirname));

app.use(staticFileMiddleware);
app.use(history());
app.use(staticFileMiddleware);

app.get("/", function(req, res) {
  res.render(path.join(__dirname + "/index.html"));
});

app.listen(5000, function() {
  console.log("Express serving on 5000!");
});
