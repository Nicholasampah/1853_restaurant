const nodemailer = require('nodemailer');
const moment = require('moment');

// Create reusable transporter
const transporter = nodemailer.createTransport({
  host: process.env.MAIL_HOST,
  port: process.env.MAIL_PORT,
  secure: false,
  auth: {
    user: process.env.MAIL_USER,
    pass: process.env.MAIL_PASSWORD
  }
});

// Send reservation confirmation email
const sendReservationConfirmation = async (reservation) => {
  try {
    const from = process.env.MAIL_FROM;
    const restaurantEmail = process.env.RESTAURANT_EMAIL;
    const restaurantPhone = process.env.RESTAURANT_PHONE;
    
    // Format date and time
    const formattedDate = moment(reservation.date).format('dddd, MMMM D, YYYY');
    const formattedTime = moment(reservation.time, 'HH:mm:ss').format('h:mm A');
    
    // Email content
    const message = `Dear ${reservation.firstName},

Your reservation has been confirmed:

Date: ${formattedDate}
Time: ${formattedTime}
Number of Guests: ${reservation.numberOfGuests}
Confirmation Code: ${reservation.confirmationCode}

${reservation.specialRequests ? `Special Requests: ${reservation.specialRequests}\n\n` : ''}

If you need to modify or cancel your reservation, please contact us at:
Phone: ${restaurantPhone}
Email: ${restaurantEmail}

You can also log in to your account on our website to manage your reservation.

We look forward to serving you!

Best regards,
1853 Restaurant Team`;

    // Send email
    const info = await transporter.sendMail({
      from,
      to: reservation.email,
      subject: 'Reservation Confirmation - 1853 Restaurant',
      text: message
    });

    console.log('Confirmation email sent:', info.messageId);
    return true;
  } catch (error) {
    console.error('Error sending confirmation email:', error);
    return false;
  }
};

// Send password reset email
const sendPasswordReset = async (user, resetToken, resetUrl) => {
  try {
    const from = process.env.MAIL_FROM;
    
    const message = `Dear ${user.firstName},

You recently requested to reset your password for your 1853 Restaurant account.

Please use the following link to reset your password:
${resetUrl}

This link will expire in 1 hour.

If you did not request a password reset, please ignore this email or contact us if you have questions.

Best regards,
1853 Restaurant Team`;

    const info = await transporter.sendMail({
      from,
      to: user.email,
      subject: 'Password Reset - 1853 Restaurant',
      text: message
    });

    console.log('Password reset email sent:', info.messageId);
    return true;
  } catch (error) {
    console.error('Error sending password reset email:', error);
    return false;
  }
};

module.exports = {
  sendReservationConfirmation,
  sendPasswordReset
};