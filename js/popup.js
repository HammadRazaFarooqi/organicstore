window.addEventListener('load', function () {
  document.getElementById('popup').style.display = 'flex';
});

// Close popup when close button is clicked
document.getElementById('closePopupBtn').addEventListener('click', function () {
  document.getElementById('popup').style.display = 'none';
});
document.getElementById('form').addEventListener('submit', function (e) {
  e.preventDefault(); // Stop form from reloading page

  const form = e.target;
  const formData = new FormData(form);

  fetch('https://api.web3forms.com/submit', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        document.getElementById('popup').style.display = 'none'; // Just close popup
        form.reset(); // Clear form inputs
      }
    })
    .catch((error) => {
      console.error(error); // You can log the error if needed
    });
});
