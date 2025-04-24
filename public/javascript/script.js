// Client-side validation
document
  .querySelector(".reservation-form")
  .addEventListener("submit", function (e) {
    const dateInput = document.getElementById("date");
    const timeInput = document.getElementById("time");
    const guestsInput = document.getElementById("guests");

    // Basic validation for field data entry
    if (!dateInput.value || !timeInput.value || !guestsInput.value) {
      e.preventDefault();
      alert("Please fill all required fields");
      return false;
    }

    // Date validation
    const selectedDate = new Date(dateInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (selectedDate < today) {
      e.preventDefault();
      alert("Please select a future date");
      return false;
    }

    return true;
  });

  
