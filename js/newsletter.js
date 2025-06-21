document.getElementById('myForm').addEventListener('submit', function (e) {
  e.preventDefault(); // Stop form redirecting

  const form = e.target;
  const formData = new FormData(form);

  fetch('https://api.web3forms.com/submit', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        form.reset(); // Clear the form silently
        // No alert, no popup, no redirect — just smooth!
      }
    })
    .catch((error) => {
      console.error(error); // Optional for debugging
    });
});
