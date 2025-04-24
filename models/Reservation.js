const db = require('../db');
const crypto = require('crypto');

class Reservation {
  // Get available time slots for a specific date
  static async getAvailableTimeSlots(date, partySize) {
    try {
      // Get all tables that can accommodate the party size
      const tables = await db.query(
        'SELECT id, capacity FROM dining_tables WHERE active = 1 AND capacity >= ? ORDER BY capacity',
        [partySize]
      );

      // MySQL returns [rows, fields]
      const tableRows = tables[0];

      if (!tableRows || tableRows.length === 0) {
        return { available: false, message: "No tables available for this party size." };
      }

      // Get all table IDs
      const tableIds = tableRows.map(table => table.id);

      // Get all reservations for the specified date with those tables
      const reservations = await db.query(
        'SELECT table_id, time FROM reservations_table WHERE date = ? AND table_id IN (?) AND status NOT IN ("CANCELLED", "NO_SHOW")',
        [date, tableIds]
      );

      // MySQL returns [rows, fields]
      const reservationRows = reservations[0];

      // Define available time slots (restaurant hours)
      const timeSlots = [
        '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', 
        '14:00', '17:00', '17:30', '18:00', '18:30', 
        '19:00', '19:30', '20:00', '20:30', '21:00'
      ];

      // Create a map to track availability
      const availabilityMap = {};
      timeSlots.forEach(time => {
        availabilityMap[time] = {
          available: true,
          tablesAvailable: [...tableIds]
        };
      });

      // Mark time slots as unavailable if booked
      if (reservationRows && reservationRows.length > 0) {
        reservationRows.forEach(reservation => {
          // Ensure the time property exists and is a string
          if (reservation.time && typeof reservation.time === 'string') {
            const resTime = reservation.time.substring(0, 5); // Convert HH:MM:SS to HH:MM
            if (availabilityMap[resTime]) {
              const tableIndex = availabilityMap[resTime].tablesAvailable.indexOf(reservation.table_id);
              if (tableIndex !== -1) {
                availabilityMap[resTime].tablesAvailable.splice(tableIndex, 1);
              }
              availabilityMap[resTime].available = availabilityMap[resTime].tablesAvailable.length > 0;
            }
          } else if (reservation.time instanceof Date) {
            // Handle if time is a Date object
            const hours = reservation.time.getHours().toString().padStart(2, '0');
            const minutes = reservation.time.getMinutes().toString().padStart(2, '0');
            const resTime = `${hours}:${minutes}`;
            
            if (availabilityMap[resTime]) {
              const tableIndex = availabilityMap[resTime].tablesAvailable.indexOf(reservation.table_id);
              if (tableIndex !== -1) {
                availabilityMap[resTime].tablesAvailable.splice(tableIndex, 1);
              }
              availabilityMap[resTime].available = availabilityMap[resTime].tablesAvailable.length > 0;
            }
          }
        });
      }

      // Convert to array for easier use in templates
      const availableTimeSlots = Object.keys(availabilityMap)
        .map(time => ({
          time,
          formatted: time.replace(':', ''),
          displayTime: this.formatTime(time),
          available: availabilityMap[time].available,
          tablesAvailable: availabilityMap[time].tablesAvailable
        }));

      return {
        available: availableTimeSlots.some(slot => slot.available),
        timeSlots: availableTimeSlots
      };
    } catch (error) {
      console.error('Error getting available time slots:', error);
      throw error;
    }
  }

  // Create a new reservation
  static async createReservation(reservationData) {
    try {
      console.log('Creating reservation with data:', reservationData);
      
      // Generate a confirmation code
      const confirmationCode = crypto.randomBytes(4).toString('hex').toUpperCase();

      // Find an available table for this time
      const tables = await db.query(
        'SELECT id FROM dining_tables WHERE active = 1 AND capacity >= ? ORDER BY capacity ASC',
        [reservationData.numberOfGuests]
      );

      // MySQL returns [rows, fields]
      const tableRows = tables[0];
      console.log('Available tables:', tableRows);

      if (!tableRows || tableRows.length === 0) {
        throw new Error('No tables available for this party size.');
      }

      // Get already booked tables at this time
      const bookedTables = await db.query(
        'SELECT table_id FROM reservations_table WHERE date = ? AND time = ? AND status NOT IN ("CANCELLED", "NO_SHOW")',
        [reservationData.date, reservationData.time]
      );

      // MySQL returns [rows, fields]
      const bookedTableRows = bookedTables[0];
      console.log('Booked tables:', bookedTableRows);

      const bookedTableIds = bookedTableRows ? bookedTableRows.map(table => table.table_id) : [];
      const availableTables = tableRows.filter(table => !bookedTableIds.includes(table.id));
      console.log('Filtered available tables:', availableTables);

      if (availableTables.length === 0) {
        throw new Error('No tables available for this time slot.');
      }

      // Choose the first available table (smallest that fits the party)
      const tableId = availableTables[0].id;
      console.log('Selected table ID:', tableId);

      // Prepare the SQL insert statement
      const sql = `INSERT INTO reservations_table 
                  (user_id, table_id, date, time, numberOfGuests, status, occasion, 
                   dietaryRequirements, specialRequests, confirmationCode)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`;
      
      const params = [
        reservationData.userId,
        tableId,
        reservationData.date,
        reservationData.time,
        reservationData.numberOfGuests,
        'PENDING',
        reservationData.occasion || null,
        reservationData.dietaryRequirements || null,
        reservationData.specialRequests || null,
        confirmationCode
      ];
      
      // console.log('SQL query:', sql);
      // console.log('Query parameters:', params);

      // Insert reservation
      const result = await db.query(sql, params);
      // console.log('Query result:', result);

      // MySQL returns [result, fields] where result contains insertId
      const insertResult = result[0];
      
      if (!insertResult || !insertResult.insertId) {
        throw new Error('Failed to insert reservation into database');
      }

      return {
        id: insertResult.insertId,
        confirmationCode,
        tableId
      };
    } catch (error) {
      console.error('Error creating reservation:', error);
      throw error;
    }
  }

  // Get reservations for a user
  static async getUserReservations(userId) {
    try {
      const result = await db.query(
        `SELECT r.*, t.tableNumber 
         FROM reservations_table r
         JOIN dining_tables t ON r.table_id = t.id
         WHERE r.user_id = ?
         ORDER BY r.date DESC, r.time DESC`,
        [userId]
      );
      
      // MySQL returns [rows, fields]
      return result[0];
    } catch (error) {
      console.error('Error getting user reservations:', error);
      throw error;
    }
  }

  // Get a specific reservation by ID
  static async getReservationById(id, userId = null) {
    try {
      let query = `
        SELECT r.*, t.tableNumber, t.capacity, t.location,
               u.firstName, u.lastName, u.email, u.phoneNo
        FROM reservations_table r
        JOIN dining_tables t ON r.table_id = t.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
      `;
      
      const params = [id];
      
      // If userId is provided, ensure the reservation belongs to this user
      if (userId) {
        query += ' AND r.user_id = ?';
        params.push(userId);
      }
      
      const result = await db.query(query, params);
      
      // MySQL returns [rows, fields]
      const rows = result[0];
      return rows.length ? rows[0] : null;
    } catch (error) {
      console.error('Error getting reservation:', error);
      throw error;
    }
  }

  // Cancel a reservation
  static async cancelReservation(id, userId) {
    try {
      const result = await db.query(
        'UPDATE reservations_table SET status = "CANCELLED" WHERE id = ? AND user_id = ?',
        [id, userId]
      );
      
      // MySQL returns [result, fields]
      return result[0].affectedRows > 0;
    } catch (error) {
      console.error('Error cancelling reservation:', error);
      throw error;
    }
  }

  // Helper method to format time
  static formatTime(time) {
    const [hours, minutes] = time.split(':');
    const hoursNum = parseInt(hours);
    const period = hoursNum >= 12 ? 'PM' : 'AM';
    const displayHours = hoursNum > 12 ? hoursNum - 12 : (hoursNum === 0 ? 12 : hoursNum);
    return `${displayHours}:${minutes} ${period}`;
  }
}

module.exports = Reservation;