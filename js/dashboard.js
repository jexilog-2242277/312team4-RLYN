document.addEventListener("DOMContentLoaded", () => {
  const totalActivities = document.getElementById("totalActivities");
  const totalDocuments = document.getElementById("totalDocuments");
  const documentsElem = document.getElementById("documents");
  const recentActivities = document.getElementById("activities");
  const modal = document.getElementById("modal");
  const modalTitle = document.getElementById("modalTitle");
  const modalContent = document.getElementById("modalContent");
  const closeModal = document.getElementById("closeModal");

  // Close modal
  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Function to fetch dashboard data
  function loadDashboardData() {
    fetch("../php/fetch_dashboard_data.php")
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          console.error("Dashboard API error:", data.error);
          return;
        }

        totalActivities.textContent = data.totalActivities;
        totalDocuments.textContent = data.totalDocuments;

        documentsElem.innerHTML = "";
        recentActivities.innerHTML = "";

        // Render activities
        (data.activities || []).forEach(act => {
          const div = document.createElement("div");
          div.className = "activity-item";
          div.style.padding = "10px";
          div.style.borderBottom = "1px solid #ccc";
          div.style.cursor = "pointer";
          div.innerHTML = `
            <div class="activity-two-col">
              <div style="overflow:hidden; text-overflow:ellipsis;"><strong>Name:</strong> ${act.name}</div>
              <div><strong>Docs:</strong> ${act.doc_count || 0}</div>
            </div>
            <div class="activity-two-col">
              <div><strong>Academic Year:</strong> ${act.academic_year || "N/A"}</div>
              <div><strong>SDG:</strong> ${act.sdg_relation || "N/A"}</div>
            </div>
          `;

          div.addEventListener("click", () => {
            modal.style.display = "flex";
            modalTitle.textContent = act.name;
            modalContent.innerHTML = `
              <p><strong>Organization:</strong> ${act.org_name || "N/A"}</p>
              <p><strong>SDG:</strong> ${act.sdg_relation || "N/A"}</p>
              <p><strong>Academic Year:</strong> ${act.academic_year || "N/A"}</p>
              <p><strong>Semester:</strong> ${act.semester || "N/A"}</p>
              <p><strong>Date:</strong> ${act.date_started || "N/A"} to ${act.date_ended || "N/A"}</p>
              <p><strong>Description:</strong><br>${act.description || "No description provided."}</p>
            `;
          });

          recentActivities.appendChild(div);
        });

        // Render documents
        (data.documents || []).forEach(doc => {
          const d = document.createElement("div");
          d.className = "doc-item";
          d.innerHTML = `
            <div class="activity-two-col">
              <div style="overflow:hidden; text-overflow:ellipsis;"><strong>${doc.document_name}</strong></div>
              <div>${doc.document_type || ""}</div>
            </div>
            <div class="activity-two-col">
              <div><small>${doc.activity_name ? "Activity: " + doc.activity_name : ""}</small></div>
              <div><small>${doc.org_name ? doc.org_name : ""}</small></div>
            </div>
          `;
          documentsElem.appendChild(d);
        });

      })
      .catch(err => console.error("Fetch error:", err));
  }

  // Initial load
  loadDashboardData();

  // Auto-refresh if a new activity was added 
  if (localStorage.getItem("activityAdded") === "true") {
    loadDashboardData(); // Refresh data immediately
    localStorage.removeItem("activityAdded"); 
  }

  // Real-time refresh if another tab adds new activity
  window.addEventListener("storage", (event) => {
    if (event.key === "activityAdded" && event.newValue === "true") {
      loadDashboardData();
      localStorage.removeItem("activityAdded");
    }
  });
});
