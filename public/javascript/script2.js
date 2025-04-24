// Wait for the document to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
  // Auto-dismiss alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
  alerts.forEach((alert) => {
    setTimeout(() => {
      // Create a Bootstrap alert instance
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  // Reservation date validation
  const dateInput = document.getElementById("date");
  if (dateInput) {
    // Set min date to today
    const today = new Date();
    const formattedDate = today.toISOString().split("T")[0];
    dateInput.setAttribute("min", formattedDate);

    // Calculate max date (3 months from now)
    const maxDate = new Date();
    maxDate.setMonth(maxDate.getMonth() + 3);
    const formattedMaxDate = maxDate.toISOString().split("T")[0];
    dateInput.setAttribute("max", formattedMaxDate);
  }

  // Phone number formatting
  const phoneInput = document.getElementById("phoneNo");
  if (phoneInput) {
    phoneInput.addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, "");
      if (value.length > 0) {
        if (value.length <= 3) {
          value = value;
        } else if (value.length <= 6) {
          value = value.slice(0, 3) + "-" + value.slice(3);
        } else {
          value =
            value.slice(0, 3) +
            "-" +
            value.slice(3, 6) +
            "-" +
            value.slice(6, 10);
        }
      }
      e.target.value = value;
    });
  }

  // Password confirmation validation
  const passwordForm = document.querySelector('form[action="/auth/register"]');
  if (passwordForm) {
    passwordForm.addEventListener("submit", function (e) {
      const password = document.getElementById("password");
      const confirmPassword = document.getElementById("confirmPassword");

      if (password.value !== confirmPassword.value) {
        e.preventDefault();
        const errorDiv = document.createElement("div");
        errorDiv.className = "alert alert-danger alert-dismissible fade show";
        errorDiv.innerHTML = `
            Passwords do not match.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          `;

        // Insert error at the top of the form
        this.insertBefore(errorDiv, this.firstChild);

        // Scroll to the top of the form
        window.scrollTo({
          top: this.offsetTop - 20,
          behavior: "smooth",
        });
      }
    });
  }

  // Initialize tooltips everywhere
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
