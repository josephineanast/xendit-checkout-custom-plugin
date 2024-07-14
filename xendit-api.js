document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("chckout");
  let userData = {};
  const resultDiv = document.getElementById("payment-result");

  form.addEventListener("change", function (event) {
    if (event.target.name === "pengiriman" && event.target.value === "yes") {
      if (window.existingUserData) {
        userData = window.existingUserData;
      } else {
        userData = {};
      }
    }

    if (event.target.name === "pembayaran" && event.target.value === "xendit") {
      form.addEventListener("submit", handleXenditPayment);
    } else {
      form.removeEventListener("submit", handleXenditPayment);
    }
  });

  function handleXenditPayment(e) {
    e.preventDefault();

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    Object.assign(data, userData);

    fetch("/wp-json/xendit-checkout/v1/create-invoice", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.invoice_url) {
          window.location.href = data.invoice_url;
        } else {
          resultDiv.textContent = "Error: " + (data.error || "Unknown error");
        }
      })
      .catch((error) => {
        resultDiv.textContent = "Error: " + error.message;
      });
  }
});
