document.addEventListener("DOMContentLoaded", () => {
  const totalActivities = document.getElementById("totalActivities");
  const totalDocuments = document.getElementById("totalDocuments");
  const documentsElem = document.getElementById("documents");
  const recentActivities = document.getElementById("activities");
  const modal = document.getElementById("modal");
  const modalTitle = document.getElementById("modalTitle");
  const modalContent = document.getElementById("modalContent");
  const closeModal = document.getElementById("closeModal");
  const searchInput = document.getElementById("searchInput");

  // Close modal
  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Function to fetch dashboard data
  function loadDashboardData(searchTerm = "") {
    const url = searchTerm.trim() 
      ? `../php/fetch_dashboard_data.php?search=${encodeURIComponent(searchTerm.trim())}`
      : "../php/fetch_dashboard_data.php";

    fetch(url)
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
            <button class="delete-btn" data-type="activity" data-id="${act.activity_id}">Delete</button>
          `;

          // Delete button handler for activities
          const deleteBtn = div.querySelector(".delete-btn");
          deleteBtn.addEventListener("click", (e) => {
            e.stopPropagation();

            if (!confirm(`Are you sure you want to delete "${act.name}"?`)) return;

            fetch("../php/delete_activity.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ 
                type: "activity",
                activity_id: act.activity_id 
              })
            })
            .then(res => res.json())
            .then(result => {
              if (result.success) {
                alert("Activity deleted successfully.");
                loadDashboardData(searchInput.value);
              } else {
                alert("Delete failed: " + (result.error || "Unknown error"));
              }
            })
            .catch(err => {
              console.error("Delete error:", err);
              alert("An error occurred while deleting.");
            });
          });

          // Click to open modal
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
            <button class="delete-btn" data-type="document" data-id="${doc.document_id}">Delete</button>
          `;

          // FIXED: Add delete button handler for documents
          const deleteBtn = d.querySelector(".delete-btn");
          deleteBtn.addEventListener("click", (e) => {
            e.stopPropagation();

            if (!confirm(`Are you sure you want to delete "${doc.document_name}"?`)) return;

            fetch("../php/delete_activity.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ 
                type: "document",
                id: doc.document_id 
              })
            })
            .then(res => res.json())
            .then(result => {
              if (result.success) {
                alert("Document deleted successfully.");
                loadDashboardData(searchInput.value);
              } else {
                alert("Delete failed: " + (result.error || "Unknown error"));
              }
            })
            .catch(err => {
              console.error("Delete error:", err);
              alert("An error occurred while deleting.");
            });
          });

          documentsElem.appendChild(d);
        });

      })
      .catch(err => console.error("Fetch error:", err));
  }

  // Initial load
  loadDashboardData();

  // Search Input Handler with debouncing
  let searchTimeout;
  searchInput.addEventListener("input", (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      loadDashboardData(e.target.value);
    }, 300);
  });

  // Auto-refresh if a new activity was added 
  if (localStorage.getItem("activityAdded") === "true") {
    loadDashboardData();
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