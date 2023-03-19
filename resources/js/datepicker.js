import flatpickr from 'flatpickr';

window.addEventListener('DOMContentLoaded', () => {
  const fromDatePickerIcon = document.querySelector('#from-datepicker-icon');
  const toDatePickerIcon = document.querySelector('#to-datepicker-icon');
  const fromDateInput = document.getElementById('fromDate');
  const toDateInput = document.getElementById('toDate');
  const fromDatePickerReset = document.querySelector('#from-datepicker-reset');
  const toDatePickerReset = document.querySelector('#to-datepicker-reset');

  let fromDatePickerIsOpen = false;
  let toDatePickerIsOpen = false;

  // Event listeners to clear selection when reset icon is clicked
  fromDatePickerReset.addEventListener('click', () => {
    fromDateInput.value = ''; // Clear the input value
    // fromDatePicker.clear(); // Clear the flatpickr selection
    Livewire.emit('updateFromDate', null); // Reset fromDate on the server-side
  });

  toDatePickerReset.addEventListener('click', () => {
    toDateInput.value = ''; // Clear the input value
    // toDatePicker.clear(); // Clear the flatpickr selection
    Livewire.emit('updateToDate', null); // Reset toDate on the server-side
  });

  const fromDatePicker = flatpickr("#fromDate", {
    dateFormat: "Y-m-d",
    clickOpens: false, // Disable auto-open on input click
    onClose: function (selectedDates, dateStr, instance) {
      if (selectedDates.length === 1) {
        const fromDate = selectedDates[0].toISOString().slice(0, 10);
        Livewire.emit("updateFromDate", fromDate);
      }
    },
  });

  const toDatePicker = flatpickr("#toDate", {
    dateFormat: "Y-m-d",
    clickOpens: false, // Disable auto-open on input click
    onClose: function (selectedDates, dateStr, instance) {
      if (selectedDates.length === 1) {
        const toDate = selectedDates[0].toISOString().slice(0, 10);
        Livewire.emit("updateToDate", toDate);
      }
    },
  });

  // If clicked, open calendar, if clicked again, close it

  fromDateInput.addEventListener("click", () => {
    if (fromDatePicker.isOpen) {
      fromDatePicker.close();
    } else {
      fromDatePicker.open();
    }
  });

  toDateInput.addEventListener("click", () => {
    if (toDatePicker.isOpen) {
      toDatePicker.close();
    } else {
      toDatePicker.open();
    }
  });

  fromDatePickerIcon.addEventListener("click", () => {
    if (fromDatePickerIsOpen) {
      fromDatePicker.close();
      fromDatePickerIsOpen = false;
    } else {
      fromDatePicker.open();
      fromDatePickerIsOpen = true;
    }
  });

  toDatePickerIcon.addEventListener("click", () => {
    if (toDatePickerIsOpen) {
      toDatePicker.close();
      toDatePickerIsOpen = false;
    } else {
      toDatePicker.open();
      toDatePickerIsOpen = true;
    }
  });

});