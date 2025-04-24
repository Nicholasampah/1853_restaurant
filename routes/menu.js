var express = require('express');
var app = express.Router();
const fs = require('fs');
const path = require('path');
// const menuData = require('.data/menuItems.json');

app.get("/", (req, res) => {
  const fileData = fs.readFileSync(path.resolve(__dirname, '../data/menuItems.json'));
  const menuData = JSON.parse(fileData);
  res.render("menu", menuData);
});

module.exports = app; 