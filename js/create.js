document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("projectForm");

  if (!form) return; // safeguard in case it's not found

  form.addEventListener("submit", async (e) => {
    e.preventDefault(); // prevent normal form submission

    const formData = new FormData(form);

    try {
      const response = await fetch("../php/submit_project.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        alert(result.message); // show success message
        form.reset(); 

        // Mark that a new activity was added
        localStorage.setItem("activityAdded", "true");
      } else {
        alert(" " + (result.message || "An error occurred while submitting."));
      }
    } catch (error) {
      console.error("Submission error:", error);
      alert(" Unable to submit project. Please try again later.");
    }
  });
});
