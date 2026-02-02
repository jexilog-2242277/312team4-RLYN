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
        returnedItemsElem.innerHTML = items.length ? "" : 
        "<tr><td colspan='3' style='padding:20px; text-align:center;'>No returned items found.</td></tr>";

        items.forEach(item => {
            const tr = document.createElement("tr");
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
                    <div class="reason-box">
                        "${item.return_reason || "No reason provided."}"
                    </div>
                </td>
                <td style="width: 15%;">
                    <div class="action-btn-container">
                        <button class="edit-btn" data-id="${item.activity_id}">Edit</button>
                        <button onclick="resubmitItem('activity', ${item.activity_id})">Resubmit</button>
                    </div>
                </td>
            `;
            returnedItemsElem.appendChild(tr);

            tr.querySelector(".edit-btn").addEventListener("click", (e) => {
                e.stopPropagation();
                openEditModal(item);
            });
        });
    }

    function openEditModal(item) {
        editModalTitle.textContent = `Edit Activity: ${item.name}`;

        // Academic year dropdown (last 10 years)
        const currentYear = new Date().getFullYear();
        let yearOptions = '';
        for(let y = currentYear; y >= currentYear - 10; y--){
            yearOptions += `<option value="${y}" ${item.academic_year == y ? 'selected' : ''}>${y}</option>`;
        }

        // SDG options (match dashboard.php)
        const sdgList = [
            "No Poverty","Zero Hunger","Good Health","Quality Education","Gender Equality",
            "Clean Water","Affordable Energy","Decent Work","Industry & Innovation",
            "Reduced Inequality","Sustainable Cities","Responsible Consumption",
            "Climate Action","Life Below Water","Life on Land","Peace & Justice","Partnerships"
        ];
        let sdgOptions = '<option value="">Select SDG</option>';
        sdgList.forEach(sdg => {
            sdgOptions += `<option value="${sdg}" ${item.sdg_relation == sdg ? 'selected' : ''}>${sdg}</option>`;
        });

        editModalContent.innerHTML = `
            <div style="display:flex; flex-direction:column; gap:10px;">
                <label>Name:</label>
                <input type="text" id="editName" value="${item.name}" style="padding:5px;">

                <label>Academic Year:</label>
                <select id="editYear" style="padding:5px;">${yearOptions}</select>

                <label>SDG:</label>
                <select id="editSDG" style="padding:5px;">${sdgOptions}</select>

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
                id: item.activity_id,
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
                    loadReturnedItems();
                } else {
                    alert("Error: " + result.error);
                }
            })
            .catch(err => console.error(err));
        };
    }

    loadReturnedItems();
});

// Resubmit function for both activity and documents
function resubmitItem(type, id) {
    fetch("../php/resubmit_item.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ type, id })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            alert(data.message);
            location.reload(); // refresh page
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error(err));
}
