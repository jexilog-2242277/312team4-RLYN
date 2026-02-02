document.addEventListener("DOMContentLoaded", () => {
    const returnedItemsElem = document.getElementById("returnedItems");
    const editModal = document.getElementById("editModal");
    const editModalTitle = document.getElementById("editModalTitle");
    const editModalContent = document.getElementById("editModalContent");
    const closeEditModal = document.getElementById("closeEditModal");

    closeEditModal.onclick = () => editModal.style.display = "none";
    window.onclick = (e) => { if (e.target === editModal) editModal.style.display = "none"; };

    function loadReturnedItems() {
        fetch("../php/fetch_returned_items.php")
            .then(res => res.json())
            .then(data => {
                if (!data.success) return console.error(data.error);
                renderReturnedItems(data.items);
            });
    }

    function renderReturnedItems(items) {
        returnedItemsElem.innerHTML = items.length ? "" : "<tr><td colspan='3' style='text-align:center;padding:20px;'>No returned items found.</td></tr>";

        items.forEach(item => {
            const tr = document.createElement("tr");

            let detailsHTML = '';
            if(item.type === 'activity') {
                detailsHTML = `
                    <div class="activity-info-box">
                        <div class="activity-two-col">
                            <div><strong>Name:</strong> ${item.name}</div>
                            <div><strong>Docs:</strong> ${item.doc_count || 0}</div>
                        </div>
                        <div class="activity-two-col">
                            <div><strong>Year:</strong> ${item.academic_year || "N/A"}</div>
                            <div><strong>SDG:</strong> ${item.sdg_relation || "N/A"}</div>
                        </div>
                    </div>
                `;
            } else if(item.type === 'document') {
                detailsHTML = `
                    <div class="activity-info-box">
                        <div><strong>Document:</strong> ${item.name}</div>
                        <div><strong>Activity ID:</strong> ${item.activity_id}</div>
                    </div>
                `;
            }

            tr.innerHTML = `
                <td style="width:45%;">${detailsHTML}</td>
                <td style="width:40%;"><div class="reason-box">"${item.return_reason || 'No reason'}"</div></td>
                <td style="width:15%;">
                    <div class="action-btn-container">
                        <button class="edit-btn">${item.type === 'activity' ? 'Edit' : 'Resubmit Document'}</button>
                    </div>
                </td>
            `;
            returnedItemsElem.appendChild(tr);

            // Edit / Resubmit
            tr.querySelector(".edit-btn").addEventListener("click", () => {
                if(item.type === 'activity') openActivityModal(item);
                else openDocumentModal(item);
            });
        });
    }

   // --- Activity Modal ---
function openActivityModal(item) {
    editModalTitle.textContent = `Edit Activity: ${item.name}`;

    // Show uploaded documents for this activity
    let uploadedDocsHTML = '';
    if (item.documents && item.documents.length > 0) {
        uploadedDocsHTML = `
            <div style="margin-bottom:10px;">
                <strong>Uploaded Documents:</strong>
                <ul style="padding-left:15px; margin-top:5px;">
                    ${item.documents.map(doc => `<li>${doc.document_name}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    editModalContent.innerHTML = `
        <form id="editActivityForm" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:10px;">
            ${uploadedDocsHTML}

            <label>Name:</label>
            <input type="text" name="name" value="${item.name}" style="padding:5px;">

            <label>Academic Year:</label>
            <select name="academic_year" style="padding:5px;">
                <option value="2022-2023" ${item.academic_year === '2022-2023' ? 'selected' : ''}>2022-2023</option>
                <option value="2023-2024" ${item.academic_year === '2023-2024' ? 'selected' : ''}>2023-2024</option>
                <option value="2024-2025" ${item.academic_year === '2024-2025' ? 'selected' : ''}>2024-2025</option>
            </select>

            <label>SDG:</label>
            <select name="sdg_relation" style="padding:5px;">
                ${[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17].map(n => `<option value="${n}" ${item.sdg_relation.includes('SDG ' + n) ? 'selected' : ''}>SDG ${n}</option>`).join('')}
            </select>

            <label>Description:</label>
            <textarea name="description" rows="4" style="padding:5px;">${item.description || ''}</textarea>

            <label>Attach File (optional):</label>
            <input type="file" name="file">

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                <button type="button" id="cancelEdit" style="padding:5px 10px; background:#ccc; border:none; border-radius:4px;">Cancel</button>
                <button type="submit" style="padding:5px 10px; background:#28a745; color:white; border:none; border-radius:4px;">Save & Resubmit</button>
            </div>
        </form>
    `;

    editModal.style.display = "flex";

    document.getElementById("cancelEdit").onclick = () => editModal.style.display = "none";

    document.getElementById("editActivityForm").onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append("id", item.id);
        formData.append("type", "activity");

        fetch("../php/resubmit_item.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                editModal.style.display = "none";
                loadReturnedItems();
            } else {
                alert(data.error);
            }
        });
    };
}


    function openDocumentModal(doc) {
    editModalTitle.textContent = `Returned Document: ${doc.name}`;

    // Build file URL using backend field
    const fileUrl = `../uploads/documents/${doc.document_file_path}`;

    // Check if PDF (optional but recommended)
    const isPDF = doc.document_file_path.toLowerCase().endsWith('.pdf');

    editModalContent.innerHTML = `
        <div style="margin-bottom:12px;">
            <strong>Current Document:</strong><br>

            ${
                isPDF
                ? `<iframe 
                        src="${fileUrl}" 
                        style="width:100%; height:300px; border:1px solid #ccc; border-radius:4px;"
                   ></iframe>`
                : `<p style="font-size:13px; color:#555;">
                        Preview not available for this file type.
                   </p>`
            }

            <a href="${fileUrl}" target="_blank"
               style="display:inline-block; margin-top:6px; color:#0E0465; font-weight:bold;">
               Open in New Tab
            </a>
        </div>

        <hr>

        <form id="resubmitDocForm" enctype="multipart/form-data">
            <label>Upload New Version:</label>
            <input type="file" name="file" required>
            <input type="hidden" name="document_id" value="${doc.id}">

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:12px;">
                <button type="button" id="cancelDoc"
                    style="background:#ccc; border:none; padding:5px 10px; border-radius:4px;">
                    Cancel
                </button>
                <button type="submit"
                    style="background:#28a745; color:white; border:none; padding:5px 10px; border-radius:4px;">
                    Resubmit
                </button>
            </div>
        </form>
    `;

    editModal.style.display = "flex";

    document.getElementById("cancelDoc").onclick = () => {
        editModal.style.display = "none";
    };

    document.getElementById("resubmitDocForm").onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        fetch("../php/upload_returned_document.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                editModal.style.display = "none";
                loadReturnedItems();
            } else {
                alert(data.error);
            }
        });
    };
}


    loadReturnedItems();
});
