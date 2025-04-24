// Check if user is authenticated
const ensureAuthenticated = (req, res, next) => {
  if (req.session.user) {
    return next();
  }
  req.flash('error_msg', 'Please log in to view this page');
  res.redirect('/auth/login');
};

// Check if user is not authenticated (for login/register pages)
const ensureGuest = (req, res, next) => {
  if (!req.session.user) {
    return next();
  }
  res.redirect('/profile');
};

module.exports = {
  ensureAuthenticated,
  ensureGuest
};