document.addEventListener("DOMContentLoaded", () => {
    const returnedItemsElem = document.getElementById("returnedItems");
    const totalReturnedElem = document.getElementById("totalReturned");

    const editModal = document.getElementById("editModal");
    const editModalTitle = document.getElementById("editModalTitle");
    const editModalContent = document.getElementById("editModalContent");
    const closeEditModal = document.getElementById("closeEditModal");

    closeEditModal.onclick = () => editModal.style.display = "none";
    window.onclick = (event) => { if (event.target === editModal) editModal.style.display = "none"; };

    function loadReturnedItems() {
        fetch("../php/fetch_returned_items.php")
            .then(res => res.json())
            .then(data => {
                if (!data.success) return console.error(data.error);
                totalReturnedElem.textContent = data.items.length;
                renderReturnedItems(data.items);
            });
    }

    function renderReturnedItems(items) {
        returnedItemsElem.innerHTML = items.length ? "" : "<tr><td colspan='3' style='padding:20px; text-align:center;'>No returned items found.</td></tr>";

        items.forEach(item => {
            const tr = document.createElement("tr");
            if(item.type === 'activity') {
                tr.innerHTML = `
                    <td style="width: 45%;">
                        <div class="activity-info-box">
                            <div class="activity-two-col">
                                <div><strong>Name:</strong> ${item.name}</div>
                                <div><strong>Docs:</strong> ${item.doc_count || 0}</div>
                            </div>
                            <div class="activity-two-col">
                                <div><strong>Academic Year:</strong> ${item.academic_year || "N/A"}</div>
                                <div><strong>SDG:</strong> ${item.sdg_relation || "N/A"}</div>
                            </div>
                        </div>
                    </td>
                    <td style="width: 40%;">
                        <div class="reason-box">"${item.return_reason || "No reason provided."}"</div>
                    </td>
                    <td style="width: 15%;">
                        <div class="action-btn-container">
                            <button class="edit-btn" data-id="${item.id}">Edit</button>
                            <button onclick="resubmitItem('activity', ${item.id})">Resubmit</button>
                        </div>
                    </td>
                `;
                // attach edit for activities
                tr.querySelector(".edit-btn").addEventListener("click", (e) => {
                    e.stopPropagation();
                    openEditModal(item);
                });
            } else if(item.type === 'document') {
                tr.innerHTML = `
                    <td style="width: 45%;">
                        <div class="activity-info-box">
                            <div class="activity-two-col">
                                <div><strong>Document:</strong> ${item.name}</div>
                                <div><strong>Activity ID:</strong> ${item.activity_id || "N/A"}</div>
                            </div>
                        </div>
                    </td>
                    <td style="width: 40%;">
                        <div class="reason-box">"${item.return_reason || "No reason provided."}"</div>
                    </td>
                    <td style="width: 15%;">
                        <div class="action-btn-container">
                            <button class="edit-btn" onclick="attachNewFile(${item.id})">Attach File</button>
                            <button onclick="resubmitItem('document', ${item.id})">Resubmit</button>
                        </div>
                    </td>
                `;
            }
            returnedItemsElem.appendChild(tr);
        });
    }

    function openEditModal(item) {
        editModalTitle.textContent = `Edit Activity: ${item.name}`;

        editModalContent.innerHTML = `
            <div style="display:flex; flex-direction:column; gap:10px;">
                <label>Name:</label>
                <input type="text" id="editName" value="${item.name}" style="padding:5px;">

                <label>Academic Year:</label>
                <select id="editYear" style="padding:5px;">
                    <option value="2024-2025" ${item.academic_year==="2024-2025"?"selected":""}>2024-2025</option>
                    <option value="2023-2024" ${item.academic_year==="2023-2024"?"selected":""}>2023-2024</option>
                    <option value="2022-2023" ${item.academic_year==="2022-2023"?"selected":""}>2022-2023</option>
                </select>

                <label>SDG:</label>
                <select id="editSDG" style="padding:5px;">
                    <option value="">Select SDG</option>
                    <option value="1" ${item.sdg_relation==="1"?"selected":""}>No Poverty</option>
                    <option value="2" ${item.sdg_relation==="2"?"selected":""}>Zero Hunger</option>
                    <option value="3" ${item.sdg_relation==="3"?"selected":""}>Good Health</option>
                    <option value="4" ${item.sdg_relation==="4"?"selected":""}>Quality Education</option>
                    <option value="5" ${item.sdg_relation==="5"?"selected":""}>Gender Equality</option>
                    <option value="6" ${item.sdg_relation==="6"?"selected":""}>Clean Water</option>
                    <option value="7" ${item.sdg_relation==="7"?"selected":""}>Affordable Energy</option>
                    <option value="8" ${item.sdg_relation==="8"?"selected":""}>Decent Work</option>
                    <option value="9" ${item.sdg_relation==="9"?"selected":""}>Industry & Innovation</option>
                    <option value="10" ${item.sdg_relation==="10"?"selected":""}>Reduced Inequality</option>
                    <option value="11" ${item.sdg_relation==="11"?"selected":""}>Sustainable Cities</option>
                    <option value="12" ${item.sdg_relation==="12"?"selected":""}>Responsible Consumption</option>
                    <option value="13" ${item.sdg_relation==="13"?"selected":""}>Climate Action</option>
                    <option value="14" ${item.sdg_relation==="14"?"selected":""}>Life Below Water</option>
                    <option value="15" ${item.sdg_relation==="15"?"selected":""}>Life on Land</option>
                    <option value="16" ${item.sdg_relation==="16"?"selected":""}>Peace & Justice</option>
                    <option value="17" ${item.sdg_relation==="17"?"selected":""}>Partnerships</option>
                </select>

                <label>Description:</label>
                <textarea id="editDescription" rows="4" style="padding:5px;">${item.description || ''}</textarea>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                    <button id="cancelEdit" style="padding:5px 10px; background:#ccc; border:none; border-radius:4px;">Cancel</button>
                    <button id="submitEdit" style="padding:5px 10px; background:#28a745; color:white; border:none; border-radius:4px;">Save</button>
                </div>
            </div>
        `;

        editModal.style.display = "flex";

        document.getElementById("cancelEdit").onclick = () => editModal.style.display = "none";

        document.getElementById("submitEdit").onclick = () => {
            const updatedData = {
                id: item.id,
                name: document.getElementById("editName").value.trim(),
                academic_year: document.getElementById("editYear").value,
                sdg_relation: document.getElementById("editSDG").value,
                description: document.getElementById("editDescription").value.trim()
            };

            fetch("../php/update_activity.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(updatedData)
            })
            .then(res => res.json())
            .then(result => {
                if(result.success){
                    alert("Activity updated!");
                    editModal.style.display = "none";
                    loadReturnedItems(); // refresh table
                } else {
                    alert("Error: " + result.error);
                }
            })
            .catch(err => console.error(err));
        };
    }

    loadReturnedItems();
});

// --- Resubmit item ---
function resubmitItem(type, id) {
    fetch("../php/resubmit_item.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ type, id })
    })
    .then(res => res.json())
    .then(result => {
        if(result.success){
            alert(result.message);
            location.reload();
        } else {
            alert("Error: " + result.error);
        }
    });
}

// --- Attach new file for returned document ---
function attachNewFile(documentId) {
    window.location.href = `upload.php?document_id=${documentId}`;
}
