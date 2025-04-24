var express = require('express');
var app = express.Router();

app.get('/', (req, res) => {
  res.render('home', {
    title: '1853 Restaurant',
    hero: {
      title: '1853',
      subtitle: 'Where tradition meets innovation in culinary excellence'
    },
    features: [
      {
        title: 'Michelin Starred',
        description: 'Experience culinary excellence at its finest'
      },
      {
        title: 'Open Hours',
        description: 'Monday - Thursday: 11:00 AM - 10:00 PM Friday - Saturday: 11:00 AM - Midnight Sunday: 12:00 AM - 9:00 PM'
      },
      {
        title: 'Location',
        description: '1 Great Ducie Street, Manchester'
      }
    ],
    specialties: [
      {
        title: 'Spring Collection',
        description: 'Fresh, vibrant flavours of the season',
        image: '/images/spring-collection.jpeg'
      },
      {
        title: "Chef's Selection",
        description: 'Carefully curated signature dishes',
        image: '/images/chefs-selection.jpg'
      },
      {
        title: 'Wine Pairing',
        description: 'Expertly matched wines for each course',
        image: "/images/wine-pairing.jpg"
      }
    ]
  });
});

module.exports = app;
