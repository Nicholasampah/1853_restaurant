require('dotenv').config();
const express = require('express');
const { engine } = require('express-handlebars');
const path = require('path');
const session = require('express-session');
const flash = require('connect-flash');
const moment = require('moment');

// Initialize Express app
const app = express();
const PORT = process.env.PORT || 3000;

const reservationsRouter = require("./routes/reservations");
const homeRouter = require("./routes/home");
const menuRouter = require("./routes/menu");
const searchRouter = require("./routes/search");
const authRouter = require('./routes/auth');
const profileRouter = require('./routes/profile');


// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

// Session configuration
app.use(session({
  secret: process.env.SESSION_SECRET || '1853RestaurantSecret',
  resave: false,
  saveUninitialized: true,
  cookie: { maxAge: 24 * 60 * 60 * 1000 } // 24 hours
}));

// Flash messages
app.use(flash());
app.use((req, res, next) => {
  res.locals.success_msg = req.flash('success_msg');
  res.locals.error_msg = req.flash('error_msg');
  res.locals.error = req.flash('error');
  res.locals.user = req.session.user || null;
  next();
});

// Set up for Handlebars help
app.engine('hbs', engine({
  extname: 'hbs',
  defaultLayout: 'main',
  layoutsDir: path.join(__dirname, 'views/layouts'),
  partialsDir: path.join(__dirname, 'views/partials'),
  helpers: {
    formatDate: (date, format) => moment(date).format(format || 'YYYY-MM-DD'),
    formatTime: (time, format) => moment(time, 'HH:mm:ss').format(format || 'h:mm A'),
    ifEquals: function(arg1, arg2, options) {
      return (arg1 === arg2) ? options.fn(this) : options.inverse(this);
    },
    ifCond: function(v1, operator, v2, options) {
      switch (operator) {
        case '==': return (v1 == v2) ? options.fn(this) : options.inverse(this);
        case '===': return (v1 === v2) ? options.fn(this) : options.inverse(this);
        case '!=': return (v1 != v2) ? options.fn(this) : options.inverse(this);
        case '!==': return (v1 !== v2) ? options.fn(this) : options.inverse(this);
        case '<': return (v1 < v2) ? options.fn(this) : options.inverse(this);
        case '<=': return (v1 <= v2) ? options.fn(this) : options.inverse(this);
        case '>': return (v1 > v2) ? options.fn(this) : options.inverse(this);
        case '>=': return (v1 >= v2) ? options.fn(this) : options.inverse(this);
        case '&&': return (v1 && v2) ? options.fn(this) : options.inverse(this);
        case '||': return (v1 || v2) ? options.fn(this) : options.inverse(this);
        default: return options.inverse(this);
      }
    },
    //
    array: function() {
      return Array.from(arguments).slice(0, -1);
    }
  }
}));
app.set('view engine', 'hbs');
app.set('views', path.join(__dirname, 'views'));

// Routes
app.use('/', homeRouter);
app.use('/reservations', reservationsRouter);
app.use('/auth',authRouter);
app.use('/profile', profileRouter);
app.use('/menu', menuRouter);
app.use('/search',searchRouter);

// Error handling
app.use((req, res) => {
  res.status(404).render('error', {
    title: '404 Not Found',
    message: 'The page you requested was not found.'
  });
});

app.use((err, req, res) => {
  console.error(err.stack);
  res.status(500).render('error', {
    title: '500 Server Error',
    message: 'Something went wrong on our end.'
  });
});

// Start server
  console.log(`Server running on port ${PORT}`);

module.exports = app;
