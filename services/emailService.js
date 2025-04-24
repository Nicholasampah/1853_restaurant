const nodemailer = require('nodemailer');

// Configure mail transport
const transporter = nodemailer.createTransport({
  host: process.env.MAIL_HOST,
  port: process.env.MAIL_PORT,
  secure: process.env.MAIL_SECURE === 'true',
  auth: {
    user: process.env.MAIL_USER,
    pass: process.env.MAIL_PASSWORD 
  }
});

// Send reservation confirmation
async function sendReservationConfirmation(reservation) {
  try {
    const message = {
      to: reservation.email,
      from: 'reservations@1853restaurant.com',
      subject: 'Reservation Confirmation - 1853 Restaurant',
      text: `
Dear ${reservation.firstName},

Your reservation has been confirmed:

Date: ${new Date(reservation.date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}
Time: ${new Date(`2000-01-01T${reservation.time}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}
Number of Guests: ${reservation.numberOfGuests}
Confirmation Code: ${reservation.confirmationCode}
${reservation.specialRequests ? `Special Requests: ${reservation.specialRequests}` : ''}

If you need to modify or cancel your reservation, please contact us at:
Phone: (+44) 123-4567-890
Email: reservations@1853restaurant.com

We look forward to serving you!

Best regards,
1853 Restaurant Team
      `
    };
    
    await transporter.sendMail(message);
    return true;
  } catch (error) {
    console.error('Email error:', error);
    return false;
  }
}

module.exports = {
  sendReservationConfirmation
};