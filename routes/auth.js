const express = require('express');
const router = express.Router();
const { check, validationResult } = require('express-validator');
const User = require('../models/User');
const { ensureGuest, ensureAuthenticated } = require('../middleware/auth');

// Login page
router.get('/login', ensureGuest, (req, res) => {
  res.render('auth/login', {
    title: 'Login - 1853 Restaurant'
  });
});

// Register page
router.get('/register', ensureGuest, (req, res) => {
  res.render('auth/register', {
    title: 'Register - 1853 Restaurant'
  });
});

// Process login
router.post('/login', ensureGuest, [
  check('email', 'Please include a valid email').isEmail(),
  check('password', 'Password is required').not().isEmpty()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.render('auth/login', {
      title: 'Login - 1853 Restaurant',
      errors: errors.array(),
      email: req.body.email
    });
  }

  try {
    const { email, password } = req.body;
    
    // Authenticate user
    const user = await User.authenticate(email, password);
    
    if (!user) {
      return res.render('auth/login', {
        title: 'Login - 1853 Restaurant',
        error_msg: 'Invalid email or password',
        email
      });
    }

    // Set user session
    req.session.user = user;
    
    // Redirect based on intention
    const redirectUrl = req.session.returnTo || '/profile';
    delete req.session.returnTo;
    
    res.redirect(redirectUrl);
  } catch (error) {
    console.error('Login error:', error);
    res.render('auth/login', {
      title: 'Login - 1853 Restaurant',
      error_msg: error.message,
      email: req.body.email
    });
  }
});

// Process registration
router.post('/register', ensureGuest, [
  check('firstName', 'First name is required').not().isEmpty(),
  check('lastName', 'Last name is required').not().isEmpty(),
  check('email', 'Please include a valid email').isEmail(),
  check('password', 'Password must be at least 6 characters').isLength({ min: 6 }),
  check('confirmPassword').custom((value, { req }) => {
    if (value !== req.body.password) {
      throw new Error('Passwords do not match');
    }
    return true;
  })
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.render('auth/register', {
      title: 'Register - 1853 Restaurant',
      errors: errors.array(),
      firstName: req.body.firstName,
      lastName: req.body.lastName,
      email: req.body.email,
      phoneNo: req.body.phoneNo,
      address: req.body.address
    });
  }

  try {
    const { firstName, lastName, email, password, phoneNo, address } = req.body;
    
    // Register the user
    const user = await User.register({
      firstName,
      lastName,
      email,
      password,
      phoneNo,
      address
    });

    req.flash('success_msg', 'You are now registered and can log in');
    res.redirect('/auth/login');
  } catch (error) {
    console.error('Registration error:', error);
    res.render('auth/register', {
      title: 'Register - 1853 Restaurant',
      error_msg: error.message,
      firstName: req.body.firstName,
      lastName: req.body.lastName,
      email: req.body.email,
      phoneNo: req.body.phoneNo,
      address: req.body.address
    });
  }
});

// Logout
router.get('/logout', ensureAuthenticated, (req, res) => {
  req.session.destroy(() => {
    res.redirect('/');
  });
});

// Forgot password page
router.get('/forgot-password', ensureGuest, (req, res) => {
  res.render('auth/forgot-password', {
    title: 'Forgot Password - 1853 Restaurant'
  });
});

// Process forgot password
router.post('/forgot-password', ensureGuest, [
  check('email', 'Please include a valid email').isEmail()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.render('auth/forgot-password', {
      title: 'Forgot Password - 1853 Restaurant',
      errors: errors.array(),
      email: req.body.email
    });
  }

  try {
    const { email } = req.body;
    
    // Check if user exists
    const user = await User.findByEmail(email);
    
    if (!user) {
      // Don't reveal user existence, but show success message
      req.flash('success_msg', 'If your email is registered, you will receive password reset instructions');
      return res.redirect('/auth/login');
    }

    // TODO: Implement password reset token logic
    // This would create a token, save it to the database with an expiry time,
    // and send an email with a reset link
    
    req.flash('success_msg', 'If your email is registered, you will receive password reset instructions');
    res.redirect('/auth/login');
  } catch (error) {
    console.error('Forgot password error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/auth/forgot-password');
  }
});

module.exports = router;