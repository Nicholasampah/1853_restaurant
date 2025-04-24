const db = require('../db');
const bcrypt = require('bcryptjs');

class User {
  // Register a new user
  static async register(userData) {
    try {
      // Check if email already exists
      const existingUser = await this.findByEmail(userData.email);
      if (existingUser) {
        throw new Error('Email already in use');
      }

      // Hash password
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash(userData.password, salt);

      // Insert user into database
      const result = await db.query(
        `INSERT INTO users 
         (firstName, lastName, email, phoneNo, password, address, active) 
         VALUES (?, ?, ?, ?, ?, ?, 1)`,
        [
          userData.firstName,
          userData.lastName,
          userData.email,
          userData.phoneNo || null,
          hashedPassword,
          userData.address || null
        ]
      );

      return {
        id: result.insertId,
        firstName: userData.firstName,
        lastName: userData.lastName,
        email: userData.email
      };
    } catch (error) {
      console.error('Error registering user:', error);
      throw error;
    }
  }

  // Find user by email
  static async findByEmail(email) {
    try {
      console.log(`Executing query: SELECT * FROM users WHERE email = '${email}'`);
      
      const result = await db.query('SELECT * FROM users WHERE email = ?', [email]);
      console.log('Raw DB result:', result);
      
      // The result is in format [rows, fields]
      const rows = result[0];
      
      if (rows && rows.length > 0) {
        const user = rows[0];
        console.log('Found user:', user);
        return user;
      }
      
      console.log('No user found with this email');
      return null;
    } catch (error) {
      console.error('Error finding user by email:', error);
      throw error;
    }
  }

  // Find user by ID
  static async findById(id) {
    try {
      const result = await db.query('SELECT * FROM users WHERE id = ?', [id]);
      
      // The result is in format [rows, fields]
      const rows = result[0];
      
      if (rows && rows.length > 0) {
        return rows[0];
      }
      
      return null;
    } catch (error) {
      console.error('Error finding user by ID:', error);
      throw error;
    }
  }

  // Authenticate user
  static async authenticate(email, password) {
    try {
      const user = await this.findByEmail(email);
      
      // Debug the user object to understand its structure
      console.log('User from database:', { 
        id: user?.id, 
        email: user?.email, 
        hasPassword: !!user?.password,
        active: user?.active
      });

      if (!user) {
        console.log('No user found with this email');
        return null;
      }

      // Check if password exists in the user record
      if (!user.password) {
        console.error('User has no password stored in database');
        return null;
      }

      // Verify password
      try {
        const isMatch = await bcrypt.compare(password, user.password);
        console.log('Password match result:', isMatch);
        
        if (!isMatch) {
          console.log('Password does not match');
          return null;
        }
      } catch (bcryptError) {
        console.error('bcrypt comparison error:', bcryptError);
        return null;
      }

      // Don't return the password
      const userCopy = {...user};
      delete userCopy.password;
      return userCopy;
    } catch (error) {
      console.error('Authentication error:', error);
      throw error;
    }
  }

  // Update user profile
  static async updateProfile(userId, userData) {
    try {
      const result = await db.query(
        `UPDATE users 
         SET firstName = ?, lastName = ?, phoneNo = ?, address = ? 
         WHERE id = ?`,
        [
          userData.firstName,
          userData.lastName,
          userData.phoneNo || null,
          userData.address || null,
          userId
        ]
      );

      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error updating profile:', error);
      throw error;
    }
  }

  // Change password
  static async changePassword(userId, currentPassword, newPassword) {
    try {
      // Get current user
      const user = await this.findById(userId);
      if (!user) {
        throw new Error('User not found');
      }

      // Verify current password
      if (!user.password) {
        throw new Error('User has no password set');
      }

      const isMatch = await bcrypt.compare(currentPassword, user.password);
      if (!isMatch) {
        throw new Error('Current password is incorrect');
      }

      // Hash new password
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash(newPassword, salt);

      // Update password
      const result = await db.query(
        'UPDATE users SET password = ? WHERE id = ?',
        [hashedPassword, userId]
      );

      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error changing password:', error);
      throw error;
    }
  }
}

module.exports = User;