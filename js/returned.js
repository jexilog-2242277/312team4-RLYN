document.addEventListener("DOMContentLoaded", () => {
    const returnedItemsElem = document.getElementById("returnedItems");
    const totalReturnedElem = document.getElementById("totalReturned");

    const editModal = document.getElementById("editModal");
    const editModalTitle = document.getElementById("editModalTitle");
    const editModalContent = document.getElementById("editModalContent");
    const closeEditModal = document.getElementById("closeEditModal");

    // Close modal
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
                        <button onclick="reuploadDocs(${item.activity_id})">Reupload</button>
                    </div>
                </td>
            `;
            returnedItemsElem.appendChild(tr);

            // Attach edit click
            tr.querySelector(".edit-btn").addEventListener("click", (e) => {
                e.stopPropagation();
                openEditModal(item);
            });
        });
    }

    function openEditModal(item) {
        editModalTitle.textContent = `Edit Activity: ${item.name}`;

        // Pre-fill fields with current activity data
        editModalContent.innerHTML = `
            <div style="display:flex; flex-direction:column; gap:10px;">
                <label>Name:</label>
                <input type="text" id="editName" value="${item.name}" style="padding:5px;">

                <label>Academic Year:</label>
                <input type="text" id="editYear" value="${item.academic_year || ''}" style="padding:5px;">

                <label>SDG:</label>
                <input type="text" id="editSDG" value="${item.sdg_relation || ''}" style="padding:5px;">

                <label>Description:</label>
                <textarea id="editDescription" rows="4" style="padding:5px;">${item.description || ''}</textarea>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                    <button id="cancelEdit" style="padding:5px 10px; background:#ccc; border:none; border-radius:4px;">Cancel</button>
                    <button id="submitEdit" style="padding:5px 10px; background:#28a745; color:white; border:none; border-radius:4px;">Save</button>
                </div>
            </div>
        `;

        editModal.style.display = "flex";

        // Cancel button
        document.getElementById("cancelEdit").onclick = () => editModal.style.display = "none";

        // Submit button
        document.getElementById("submitEdit").onclick = () => {
            const updatedData = {
                id: item.activity_id,
                name: document.getElementById("editName").value.trim(),
                academic_year: document.getElementById("editYear").value.trim(),
                sdg_relation: document.getElementById("editSDG").value.trim(),
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

function reuploadDocs(id) {
    window.location.href = `upload.php?activity_id=${id}`;
}
