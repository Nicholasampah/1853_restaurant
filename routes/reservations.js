const express = require('express');
const router = express.Router();
const { check, validationResult } = require('express-validator');
const Reservation = require('../models/Reservation');
const { ensureAuthenticated } = require('../middleware/auth');
const emailService = require('../services/email');

// Create a new reservation (step 1: select date and party size)
router.get('/', (req, res) => {
  const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
  
  res.render('reservations/step1', {
    title: 'Make a Reservation - 1853 Restaurant',
    today,
    user: req.session.user
  });
});

// Process step 1 and show available time slots
router.post('/time-slots', [
  check('date', 'Date is required').not().isEmpty(),
  check('numberOfGuests', 'Number of guests is required').isInt({ min: 1, max: 20 })
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    const today = new Date().toISOString().split('T')[0];
    return res.render('reservations/step1', {
      title: 'Make a Reservation - 1853 Restaurant',
      today,
      errors: errors.array(),
      user: req.session.user,
      input: req.body
    });
  }

  try {
    const { date, numberOfGuests } = req.body;
    
    // Get available time slots
    const availability = await Reservation.getAvailableTimeSlots(date, numberOfGuests);
    
    if (!availability.available) {
      return res.render('reservations/step1', {
        title: 'Make a Reservation - 1853 Restaurant',
        today: new Date().toISOString().split('T')[0],
        error_msg: 'No availability for the selected date and party size.',
        user: req.session.user,
        input: req.body
      });
    }
    
    // Store the selection in session for next step
    req.session.reservationData = {
      date,
      numberOfGuests: parseInt(numberOfGuests)
    };
    
    res.render('reservations/step2', {
      title: 'Select a Time - 1853 Restaurant',
      date,
      numberOfGuests,
      timeSlots: availability.timeSlots,
      formattedDate: new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }),
      user: req.session.user
    });
  } catch (error) {
    console.error('Time slot error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/reservations');
  }
});

// Step 3: Guest details
router.post('/details', [
  check('time', 'Time is required').not().isEmpty()
], (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    req.flash('error_msg', 'Please select a valid time');
    return res.redirect('/reservations');
  }

  // Ensure we have the session data from step 1
  if (!req.session.reservationData) {
    req.flash('error_msg', 'Session expired. Please start again.');
    return res.redirect('/reservations');
  }

  const { time } = req.body;
  
  // Update session data with selected time
  req.session.reservationData.time = time;
  
  // Format the time for display (e.g., 1800 -> 18:00)
  const formattedTime = time.substring(0, 2) + ':' + time.substring(2, 4);
  
  // If user is logged in, pre-fill the form
  const user = req.session.user || {};
  
  res.render('reservations/step3', {
    title: 'Guest Details - 1853 Restaurant',
    user,
    reservationData: req.session.reservationData,
    formattedDate: new Date(req.session.reservationData.date).toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    }),
    formattedTime
  });
});

// Process final reservation
router.post('/confirm', ensureAuthenticated, [
  check('occasion').optional(),
  check('dietaryRequirements').optional(),
  check('specialRequests').optional()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    req.flash('error_msg', 'Please correct the errors in the form.');
    return res.redirect('/reservations/details');
  }

  // Ensure we have the session data from previous steps
  if (!req.session.reservationData) {
    req.flash('error_msg', 'Session expired. Please start again.');
    return res.redirect('/reservations');
  }

  try {
    const { occasion, dietaryRequirements, specialRequests } = req.body;
    
    // Format the time correctly (from "1800" to "18:00")
    const formattedTime = req.session.reservationData.time.substring(0, 2) + ':' + 
                         req.session.reservationData.time.substring(2, 4);
    
    // Combine all reservation data
    const reservationData = {
      ...req.session.reservationData,
      userId: req.session.user.id,
      occasion,
      dietaryRequirements,
      specialRequests,
      time: formattedTime // Format HH:MM
    };
    
    console.log('Final reservation data:', reservationData);
    
    // Create the reservation
    const result = await Reservation.createReservation(reservationData);
    console.log('Reservation created with result:', result);
    
    if (!result || !result.id) {
      throw new Error('Failed to create reservation');
    }
    
    // Get full reservation details for email
    const reservation = await Reservation.getReservationById(result.id);
    console.log('Retrieved reservation for email:', reservation);
    
    if (!reservation) {
      throw new Error('Could not retrieve the created reservation');
    }
    
    // Send confirmation email
    try {
      await emailService.sendReservationConfirmation(reservation);
      console.log('Confirmation email sent successfully');
    } catch (emailError) {
      console.error('Error sending confirmation email:', emailError);
      // Continue even if email fails
    }
    
    // Clear session data
    delete req.session.reservationData;
    
    // Store the confirmation details directly in the session to avoid URL parameter issues
    req.session.confirmationDetails = {
      id: result.id,
      confirmationCode: result.confirmationCode
    };
    
    // Redirect to confirmation page - using a simpler URL format
    res.redirect('/reservations/confirmation');
  } catch (error) {
    console.error('Reservation confirmation error:', error);
    req.flash('error_msg', error.message || 'An error occurred. Please try again later.');
    res.redirect('/reservations');
  }
});

// New confirmation page route without parameters
router.get('/confirmation', async (req, res) => {
  try {
    // Get reservation details from session
    if (!req.session.confirmationDetails) {
      req.flash('error_msg', 'No confirmation details found. Please make a new reservation.');
      return res.redirect('/reservations');
    }
    
    const { id, confirmationCode } = req.session.confirmationDetails;
    console.log(`Loading confirmation page for reservation ID: ${id}, code: ${confirmationCode}`);
    
    // Get reservation details
    const reservation = await Reservation.getReservationById(id);
    console.log('Retrieved reservation for confirmation page:', reservation);
    
    if (!reservation) {
      req.flash('error_msg', 'Invalid reservation information');
      return res.redirect('/reservations');
    }
    
    // Clear the confirmation details from session after displaying them
    delete req.session.confirmationDetails;
    
    res.render('reservations/confirmation', {
      title: 'Reservation Confirmed - 1853 Restaurant',
      reservation,
      user: req.session.user,
      formattedDate: new Date(reservation.date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    });
  } catch (error) {
    console.error('Confirmation page error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/reservations');
  }
});

// Keep the existing confirmation route for backward compatibility
router.get('/confirmation/:id', async (req, res) => {
  try {
    const id = req.params.id;
    const confirmationCode = req.query.code;
    
    console.log(`Loading confirmation page for reservation ID: ${id}, code: ${confirmationCode}`);
    
    // Get reservation details
    const reservation = await Reservation.getReservationById(id);
    console.log('Retrieved reservation for confirmation page:', reservation);
    
    if (!reservation || reservation.confirmationCode !== confirmationCode) {
      req.flash('error_msg', 'Invalid reservation information');
      return res.redirect('/reservations');
    }
    
    res.render('reservations/confirmation', {
      title: 'Reservation Confirmed - 1853 Restaurant',
      reservation,
      user: req.session.user,
      formattedDate: new Date(reservation.date).toLocaleDateString('en-GB', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    });
  } catch (error) {
    console.error('Confirmation page error:', error);
    req.flash('error_msg', 'An error occurred. Please try again later.');
    res.redirect('/reservations');
  }
});

module.exports = router;