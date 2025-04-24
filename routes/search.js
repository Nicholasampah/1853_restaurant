var express = require("express");
var app = express.Router();
const axios = require("axios");


// app.get("/", (req, res) => {
//   res.render("search"); // Render the search page
// });

app.get("/", async (req, res) => {
  const mealName = req.query.meal; // Get the meal name from the query string

  if (!mealName) {
    return res.render("search", { meals: null }); // No meal name provided
  }

  try {
    // Fetch data from TheMealDB API
    const response = await axios.get(
      `https://www.themealdb.com/api/json/v1/1/search.php?s=${mealName}`
    );

    const meals = response.data.meals; // Extract meals from the API response

    // Render the search page with the results
    res.render("search", { meals });
  } catch (error) {
    console.error("Error fetching data:", error);
    res.status(500).render("search", {
      meals: null,
      error: "Error fetching data from TheMealDB API",
    });
  }
});

module.exports = app;
