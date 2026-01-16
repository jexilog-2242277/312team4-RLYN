document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("projectForm");
  const startDateInput = document.getElementById("startDate");
  const endDateInput = document.getElementById("endDate");

  if (!form || !startDateInput || !endDateInput) return;

  const today = new Date().toISOString().split("T")[0];

  startDateInput.min = today;
  endDateInput.min = today;

  startDateInput.addEventListener("change", () => {
    endDateInput.min = startDateInput.value;
    if (endDateInput.value && endDateInput.value < startDateInput.value) {
      endDateInput.value = "";
    }
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const startDate = startDateInput.value;
    const endDate = endDateInput.value;

    if (startDate < today) {
      alert("Start date cannot be in the past.");
      return;
    }

    if (endDate < startDate) {
      alert("End date cannot be earlier than the start date.");
      return;
    }

    const formData = new FormData(form);

    try {
      const response = await fetch("../php/submit_project.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        alert(result.message);
        form.reset();

        startDateInput.min = today;
        endDateInput.min = today;

        localStorage.setItem("activityAdded", "true");
      } else {
        alert(result.message || "An error occurred while submitting.");
      }
    } catch (error) {
      console.error("Submission error:", error);
      alert("Unable to submit project. Please try again later.");
    }
  });
});
