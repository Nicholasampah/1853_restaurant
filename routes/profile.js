const express = require('express');
const router = express.Router();
const { check, validationResult } = require('express-validator');
const User = require('../models/User');
const Reservation = require('../models/Reservation');
const { ensureAuthenticated } = require('../middleware/auth');

// User profile page
router.get('/', ensureAuthenticated, async (req, res) => {
  try {
    console.log('Loading profile for user ID:', req.session.user.id);
    
    const user = await User.findById(req.session.user.id);
    const reservations = await Reservation.getUserReservations(req.session.user.id);
    
    console.log('User found:', user ? 'Yes' : 'No');
    console.log('Reservations found:', reservations.length);
    
    // Print all reservations for debugging
    console.log('All reservations:', reservations.map(r => ({
      id: r.id,
      date: r.date,
      time: r.time,
      status: r.status
    })));
    
    // Group reservations by status - Use simpler logic that doesn't depend on date comparisons
    const upcomingReservations = reservations.filter(r => 
      ['PENDING', 'CONFIRMED'].includes(r.status)
    );
    
    const pastReservations = reservations.filter(r => 
      ['COMPLETED', 'CANCELLED', 'NO_SHOW'].includes(r.status)
    );
    
    // console.log('Upcoming reservations:', upcomingReservations.length);
    // console.log('Past reservations:', pastReservations.length);
    
    res.render('profile/index', {
      title: 'My Profile - 1853 Restaurant',
      user,
      upcomingReservations,
      pastReservations
    });
  } catch (error) {
    console.error('Profile error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/');
  }
});

// Edit profile page
router.get('/edit', ensureAuthenticated, async (req, res) => {
  try {
    const user = await User.findById(req.session.user.id);
    
    res.render('profile/edit', {
      title: 'Edit Profile - 1853 Restaurant',
      user
    });
  } catch (error) {
    console.error('Edit profile error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/profile');
  }
});

// Process profile update
router.post('/edit', ensureAuthenticated, [
  check('firstName', 'First name is required').not().isEmpty(),
  check('lastName', 'Last name is required').not().isEmpty(),
  check('phoneNo', 'Phone number is required').not().isEmpty()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.render('profile/edit', {
      title: 'Edit Profile - 1853 Restaurant',
      user: req.body,
      errors: errors.array()
    });
  }

  try {
    const { firstName, lastName, phoneNo, address } = req.body;
    
    // Update user profile
    await User.updateProfile(req.session.user.id, {
      firstName,
      lastName,
      phoneNo,
      address
    });
    
    // Update session data
    req.session.user.firstName = firstName;
    req.session.user.lastName = lastName;
    
    req.flash('success_msg', 'Your profile has been updated');
    res.redirect('/profile');
  } catch (error) {
    console.error('Update profile error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/profile/edit');
  }
});

// Change password page
router.get('/change-password', ensureAuthenticated, (req, res) => {
  res.render('profile/change-password', {
    title: 'Change Password - 1853 Restaurant'
  });
});

// Process password change
router.post('/change-password', ensureAuthenticated, [
  check('currentPassword', 'Current password is required').not().isEmpty(),
  check('newPassword', 'New password must be at least 6 characters').isLength({ min: 6 }),
  check('confirmPassword').custom((value, { req }) => {
    if (value !== req.body.newPassword) {
      throw new Error('Passwords do not match');
    }
    return true;
  })
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.render('profile/change-password', {
      title: 'Change Password - 1853 Restaurant',
      errors: errors.array()
    });
  }

  try {
    const { currentPassword, newPassword } = req.body;
    
    // Change password
    await User.changePassword(req.session.user.id, currentPassword, newPassword);
    
    req.flash('success_msg', 'Your password has been changed');
    res.redirect('/profile');
  } catch (error) {
    console.error('Change password error:', error);
    req.flash('error_msg', error.message);
    res.redirect('/profile/change-password');
  }
});

// View reservation details
router.get('/reservations/:id', ensureAuthenticated, async (req, res) => {
  try {
    const reservation = await Reservation.getReservationById(req.params.id, req.session.user.id);
    
    if (!reservation) {
      req.flash('error_msg', 'Reservation not found');
      return res.redirect('/profile');
    }
    
    res.render('profile/reservation-details', {
      title: 'Reservation Details - 1853 Restaurant',
      reservation
    });
  } catch (error) {
    console.error('Reservation details error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/profile');
  }
});

// Cancel reservation
router.post('/reservations/:id/cancel', ensureAuthenticated, async (req, res) => {
  try {
    const success = await Reservation.cancelReservation(req.params.id, req.session.user.id);
    
    if (success) {
      req.flash('success_msg', 'Your reservation has been cancelled');
    } else {
      req.flash('error_msg', 'Failed to cancel reservation');
    }
    
    res.redirect('/profile');
  } catch (error) {
    console.error('Cancel reservation error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/profile');
  }
});

module.exports = router;