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
    const btnOrg = document.getElementById("btnOrg");
    const btnYear = document.getElementById("btnYear");
    const btnSDG = document.getElementById("btnSDG");
    
    const panelOrg = document.getElementById("panelOrg");
    const panelYear = document.getElementById("panelYear");
    const panelSDG = document.getElementById("panelSDG");
    
    const orgSelect = document.getElementById("orgSelect");
    const yearSelect = document.getElementById("yearSelect");
    const sdgCheckboxes = document.querySelectorAll(".sdg-item input");
    
    // Action Buttons
    const btnApply = document.getElementById("btnApply");
    const btnClear = document.getElementById("btnClear");

    // --- State Management ---
    let userRole = ""; 
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

    const hideAllPanels = () => {
        [panelOrg, panelYear, panelSDG].forEach(p => { if(p) p.style.display = "none"; });
        [btnAll, btnOrg, btnYear, btnSDG].forEach(b => { if(b) b.classList.remove("active"); });
    };

    btnAll.addEventListener("click", () => {
        hideAllPanels();
        btnAll.classList.add("active");
        if(orgSelect) orgSelect.value = "";
        if(yearSelect) yearSelect.value = "";
        sdgCheckboxes.forEach(cb => cb.checked = false);
        currentFilters.year = "";
        currentFilters.sdgs = [];
        loadDashboardData();
    });

    if(btnOrg) {
        btnOrg.addEventListener("click", () => {
            const isVisible = panelOrg.style.display === "block";
            hideAllPanels();
            if (!isVisible) {
                panelOrg.style.display = "block";
                btnOrg.classList.add("active");
            } else {
                btnAll.classList.add("active");
            }
        });
    }

    btnYear.addEventListener("click", () => {
        const isVisible = panelYear.style.display === "block";
        hideAllPanels();
        if (!isVisible) {
            panelYear.style.display = "block";
            btnYear.classList.add("active");
        } else {
            btnAll.classList.add("active");
        }
    });

    btnSDG.addEventListener("click", () => {
        const isVisible = panelSDG.style.display === "block";
        hideAllPanels();
        if (!isVisible) {
            panelSDG.style.display = "block";
            btnSDG.classList.add("active");
        } else {
            btnAll.classList.add("active");
        }
    });

    btnApply.addEventListener("click", () => {
        currentFilters.year = yearSelect.value;
        currentFilters.sdgs = Array.from(sdgCheckboxes)
            .filter(i => i.checked)
            .map(i => i.value);
        loadDashboardData();
        hideAllPanels();
        btnAll.classList.add("active");
    });

    btnClear.addEventListener("click", () => {
        if(orgSelect) orgSelect.value = "";
        if(yearSelect) yearSelect.value = "";
        sdgCheckboxes.forEach(cb => cb.checked = false);
        currentFilters.year = "";
        currentFilters.sdgs = [];
        loadDashboardData();
        hideAllPanels();
        btnAll.classList.add("active");
    });

    // --- Data Fetching & Rendering ---

    function loadDashboardData() {
        const params = new URLSearchParams();
        if (currentFilters.search) params.append("search", currentFilters.search);
        if (currentFilters.year) params.append("year", currentFilters.year);
        if (currentFilters.sdgs.length > 0) params.append("sdgs", currentFilters.sdgs.join(","));

        fetch(`../php/fetch_dashboard_data.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) return console.error("API Error:", data.error);
                
                // Content Manipulation: Store role for button toggling
                userRole = data.userRole; 

                totalActivities.textContent = data.totalActivities || 0;
                totalDocuments.textContent = data.totalDocuments || 0;
                renderActivities(data.activities || []);
                renderDocuments(data.documents || []);
            })
            .catch(err => console.error("Fetch error:", err));
    }

    function renderActivities(activities) {
        activitiesElem.innerHTML = activities.length ? "" : "<p style='padding:15px;'>No activities found.</p>";
        activities.forEach(act => {
            const div = document.createElement("div");
            div.className = "activity-item";
            div.style = "padding: 10px; border-bottom: 1px solid #ccc; cursor: pointer; display: flex; justify-content: space-between; align-items: center;";
            
            // Content Manipulation: Toggling buttons vs status labels
            let actionHtml = "";
            if (userRole === 'osas' || userRole === 'admin') {
                actionHtml = `<button class="return-btn" data-id="${act.activity_id}" data-name="${act.name}">Return</button>`;
            } else if (userRole === 'student') {
                actionHtml = `<span style="color: #28a745; font-weight: bold;">Submitted</span>`;
            }

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
                <div style="margin-left: 20px; display: flex; gap: 5px;">
                    ${actionHtml}
                </div>
            `;
            
            if (userRole === 'osas' || userRole === 'admin') {
                div.querySelector(".return-btn").addEventListener("click", (e) => {
                    e.stopPropagation();
                    openReturnModal("activity", act.activity_id, act.name);
                });
            }

            div.addEventListener("click", () => {
                showModal(act.name, `
                    <p><strong>Organization:</strong> ${act.org_name || "N/A"}</p>
                    <p><strong>SDG:</strong> ${act.sdg_relation || "N/A"}</p>
                    <p><strong>Academic Year:</strong> ${act.academic_year || "N/A"}</p>
                    <p><strong>Description:</strong><br>${act.description || "No description."}</p>
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
        div.style = "padding: 10px; border-bottom: 1px solid #ccc; display: flex; justify-content: space-between; align-items: center;";
        
        let actionHtml = "";
        if (userRole === 'osas' || userRole === 'admin') {
            // Added Download button next to Return
            actionHtml = `
                <a href="../uploads/documents/${doc.document_file_path}" download="${doc.document_name}" class="download-btn" style="text-decoration: none; padding: 5px 10px; background: #28a745; color: white; border-radius: 4px; font-size: 12px;">Download</a>
                <button class="return-btn" data-id="${doc.document_id}" data-name="${doc.document_name}">Return</button>
            `;
        } else if (userRole === 'student') {
            const statusLabel = doc.visibility === 'public' ? 'Pending' : 'Submitted';
            const statusColor = doc.visibility === 'public' ? '#0E0465' : '#33af3d';
            actionHtml = `<span style="font-weight: bold; color: ${statusColor};">${statusLabel}</span>`;
        }

        div.innerHTML = `
            <div style="flex-grow: 1;">
                <div class="activity-two-col">
                    <div style="overflow:hidden; text-overflow:ellipsis;"><strong>${doc.document_name}</strong></div>
                    <div>${doc.document_type || ""}</div>
                </div>
                <small>${doc.activity_name || ""}</small>
            </div>
            <div style="margin-left: 20px; display: flex; gap: 10px; align-items: center;">
                ${actionHtml}
            </div>
        `;

            if (userRole === 'osas' || userRole === 'admin') {
                div.querySelector(".return-btn").addEventListener("click", (e) => {
                    e.stopPropagation();
                    openReturnModal("document", doc.document_id, doc.document_name);
                });
            }
            documentsElem.appendChild(div);
        });
    }

    function showModal(title, htmlContent) {
        modalTitle.textContent = title;
        modalContent.innerHTML = htmlContent;
        modal.style.display = "flex";
    }

    closeModal.onclick = () => modal.style.display = "none";
    window.onclick = (event) => { if (event.target == modal) modal.style.display = "none"; };

    // Modal for Return functionality
    const returnModalOverlay = document.createElement("div");
    returnModalOverlay.id = "returnModalOverlay";
    returnModalOverlay.style = "position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; z-index: 3000;";
    returnModalOverlay.innerHTML = `
        <div style="background:#fff; padding:30px; border-radius:10px; width:500px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
            <h3 style="color: #0E0465; margin-bottom: 15px; border-bottom: 2px solid #0E0465;">Return Item</h3>
            <p id="returnItemName" style="margin-bottom: 15px; color: #333;"></p>
            <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #0E0465;">Notes for return:</label>
            <textarea id="returnNote" placeholder="Explain what needs to be changed or provide feedback..." style="width: 100%; height: 150px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: Arial, sans-serif; font-size: 14px;"></textarea>
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button id="cancelReturnBtn" style="padding: 10px 20px; background: #ccc; color: #333; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button id="submitReturnBtn" style="padding: 10px 20px; background: #0E0465; color: white; border: none; border-radius: 4px; cursor: pointer;">Return Item</button>
            </div>
        </div>
    `;
    document.body.appendChild(returnModalOverlay);

    let pendingReturn = null;

    function openReturnModal(type, id, name) {
        pendingReturn = { type, id, name };
        document.getElementById("returnItemName").textContent = `Item: ${name}`;
        document.getElementById("returnNote").value = "";
        returnModalOverlay.style.display = "flex";
    }

    document.getElementById("cancelReturnBtn").addEventListener("click", () => {
        returnModalOverlay.style.display = "none";
        pendingReturn = null;
    });

    document.getElementById("submitReturnBtn").addEventListener("click", () => {
        const note = document.getElementById("returnNote").value.trim();
        if (!note) {
            alert("Please provide a note for the return.");
            return;
        }

        fetch("../php/return_item.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                type: pendingReturn.type,
                id: pendingReturn.id,
                name: pendingReturn.name,
                note: note
            })
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert(`Item returned successfully. Student has been notified.`);
                returnModalOverlay.style.display = "none";
                pendingReturn = null;
                loadDashboardData();
            } else {
                alert("Error: " + result.error);
            }
        })
        .catch(err => console.error("Error:", err));
    });

    // Close modal when clicking outside
    returnModalOverlay.addEventListener("click", (e) => {
        if (e.target === returnModalOverlay) {
            returnModalOverlay.style.display = "none";
            pendingReturn = null;
        }
    });

    let searchTimeout;
    searchInput.addEventListener("input", (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = e.target.value.trim();
            loadDashboardData();
        }, 300);
    });

    loadDashboardData();
});
