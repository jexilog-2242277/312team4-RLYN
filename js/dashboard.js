document.addEventListener("DOMContentLoaded", () => {
    // --- UI Element Selectors ---
    const totalActivities = document.getElementById("totalActivities");
    const totalDocuments = document.getElementById("totalDocuments");
    const documentsElem = document.getElementById("documents");
    const activitiesElem = document.getElementById("activities");
    const searchInput = document.getElementById("searchInput");

    // Modal Elements
    const modal = document.getElementById("modal");
    const modalTitle = document.getElementById("modalTitle");
    const modalContent = document.getElementById("modalContent");
    const closeModal = document.getElementById("closeModal");

    // Tab Elements
    const tabBtnActivities = document.getElementById("tabBtnActivities");
    const tabBtnDocuments = document.getElementById("tabBtnDocuments");
    const panelActivities = document.getElementById("panelActivities");
    const panelDocuments = document.getElementById("panelDocuments");

    // Filter Elements
    const btnAll = document.getElementById("btnAll");
    const btnYear = document.getElementById("btnYear");
    const btnSDG = document.getElementById("btnSDG");
    const panelYear = document.getElementById("panelYear");
    const panelSDG = document.getElementById("panelSDG");
    const yearSelect = document.getElementById("yearSelect");
    const sdgCheckboxes = document.querySelectorAll(".sdg-item input");

    // --- State Management ---
    let currentFilters = {
        search: "",
        year: "",
        sdgs: []
    };

    // --- Tab Switching Logic ---
    const switchTab = (activeTab) => {
        if (activeTab === 'activities') {
            tabBtnActivities.classList.add("active");
            tabBtnDocuments.classList.remove("active");
            panelActivities.style.display = "block";
            panelDocuments.style.display = "none";
        } else {
            tabBtnDocuments.classList.add("active");
            tabBtnActivities.classList.remove("active");
            panelDocuments.style.display = "block";
            panelActivities.style.display = "none";
        }
    };

    tabBtnActivities.addEventListener("click", () => switchTab('activities'));
    tabBtnDocuments.addEventListener("click", () => switchTab('documents'));

    // --- Filter UI Logic ---
    const resetFilterPanels = () => {
        panelYear.style.display = "none";
        panelSDG.style.display = "none";
        [btnAll, btnYear, btnSDG].forEach(b => b.classList.remove("active"));
    };

    btnAll.addEventListener("click", () => {
        resetFilterPanels();
        btnAll.classList.add("active");
        currentFilters.year = "";
        currentFilters.sdgs = [];
        yearSelect.value = "";
        sdgCheckboxes.forEach(cb => cb.checked = false);
        loadDashboardData();
    });

    btnYear.addEventListener("click", () => {
        resetFilterPanels();
        btnYear.classList.add("active");
        panelYear.style.display = "block";
    });

    btnSDG.addEventListener("click", () => {
        resetFilterPanels();
        btnSDG.classList.add("active");
        panelSDG.style.display = "block";
    });

    yearSelect.addEventListener("change", (e) => {
        currentFilters.year = e.target.value;
        loadDashboardData();
    });

    sdgCheckboxes.forEach(cb => {
        cb.addEventListener("change", () => {
            currentFilters.sdgs = Array.from(sdgCheckboxes)
                .filter(i => i.checked)
                .map(i => i.value);
            loadDashboardData();
        });
    });

    // --- Data Fetching ---
    function loadDashboardData() {
        const params = new URLSearchParams();
        if (currentFilters.search) params.append("search", currentFilters.search);
        if (currentFilters.year) params.append("year", currentFilters.year);
        if (currentFilters.sdgs.length > 0) params.append("sdgs", currentFilters.sdgs.join(","));

        fetch(`../php/fetch_dashboard_data.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) return console.error("API Error:", data.error);
                
                totalActivities.textContent = data.totalActivities || 0;
                totalDocuments.textContent = data.totalDocuments || 0;

                renderActivities(data.activities || []);
                renderDocuments(data.documents || []);
            })
            .catch(err => console.error("Fetch error:", err));
    }

    // --- Rendering Helpers ---
    function renderActivities(activities) {
        activitiesElem.innerHTML = activities.length ? "" : "<p style='padding:15px;'>No activities found.</p>";
        activities.forEach(act => {
            const div = document.createElement("div");
            div.className = "activity-item";
            
            // Set styles to ensure labels stay as they were, but button goes to far right
            div.style = `
                padding: 10px; 
                border-bottom: 1px solid #ccc; 
                cursor: pointer; 
                display: flex; 
                justify-content: space-between; 
                align-items: center;
            `;
            
            div.innerHTML = `
                <div style="flex-grow: 1;">
                    <div class="activity-two-col">
                        <div style="overflow:hidden; text-overflow:ellipsis;"><strong>Name:</strong> ${act.name}</div>
                        <div><strong>Docs:</strong> ${act.doc_count || 0}</div>
                    </div>
                    <div class="activity-two-col">
                        <div><strong>Academic Year:</strong> ${act.academic_year || "N/A"}</div>
                        <div><strong>SDG:</strong> ${act.sdg_relation || "N/A"}</div>
                    </div>
                </div>
                <div style="margin-left: 20px;">
                    <button class="delete-btn" data-id="${act.activity_id}">Delete</button>
                </div>
            `;
            
            div.querySelector(".delete-btn").addEventListener("click", (e) => {
                e.stopPropagation();
                handleDelete("activity", act.activity_id, act.name);
            });

            div.addEventListener("click", () => {
                showModal(act.name, `
                    <p><strong>Organization:</strong> ${act.org_name || "N/A"}</p>
                    <p><strong>SDG:</strong> ${act.sdg_relation || "N/A"}</p>
                    <p><strong>Academic Year:</strong> ${act.academic_year || "N/A"}</p>
                    <p><strong>Date:</strong> ${act.date_started || ""} to ${act.date_ended || ""}</p>
                    <p><strong>Description:</strong><br>${act.description || "No description provided."}</p>
                `);
            });
            activitiesElem.appendChild(div);
        });
    }

    function renderDocuments(documents) {
        documentsElem.innerHTML = documents.length ? "" : "<p style='padding:15px;'>No documents found.</p>";
        documents.forEach(doc => {
            const div = document.createElement("div");
            div.className = "doc-item";
            div.style = "padding: 10px; border-bottom: 1px solid #ccc; cursor: pointer; display: flex; justify-content: space-between; align-items: center;";
            div.innerHTML = `
                <div style="flex-grow: 1;">
                    <div class="activity-two-col">
                        <div style="overflow:hidden; text-overflow:ellipsis;"><strong>${doc.document_name}</strong></div>
                        <div>${doc.document_type || ""}</div>
                    </div>
                    <div class="activity-two-col">
                        <small>${doc.activity_name || ""}</small>
                    </div>
                </div>
                <div style="margin-left: 20px;">
                    <button class="delete-btn">Delete</button>
                </div>
            `;

            div.querySelector(".delete-btn").addEventListener("click", (e) => {
                e.stopPropagation();
                handleDelete("document", doc.document_id, doc.document_name);
            });

            div.addEventListener("click", () => {
                showModal(doc.document_name, `<p><strong>Type:</strong> ${doc.document_type}</p><p><strong>Activity:</strong> ${doc.activity_name}</p>`);
            });
            documentsElem.appendChild(div);
        });
    }

    // --- Modal & Action Helpers ---
    function showModal(title, htmlContent) {
        modalTitle.textContent = title;
        modalContent.innerHTML = htmlContent;
        modal.style.display = "flex";
    }

    closeModal.onclick = () => modal.style.display = "none";
    window.onclick = (event) => { if (event.target == modal) modal.style.display = "none"; };

    function handleDelete(type, id, name) {
        if (!confirm(`Are you sure you want to delete ${type} "${name}"?`)) return;

        fetch("../php/delete_activity.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ type: type, activity_id: id, id: id })
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert(`${type} deleted.`);
                loadDashboardData();
            } else {
                alert("Error: " + result.error);
            }
        });
    }

    // --- Search with Debounce ---
    let searchTimeout;
    searchInput.addEventListener("input", (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = e.target.value.trim();
            loadDashboardData();
        }, 300);
    });

    // Initial Load
    loadDashboardData();
});