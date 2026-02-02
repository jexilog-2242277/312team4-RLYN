document.addEventListener("DOMContentLoaded", () => {
    const returnedItemsElem = document.getElementById("returnedItems");
    const totalReturnedElem = document.getElementById("totalReturned");

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
                        <button onclick="editActivity(${item.activity_id})">Edit</button>
                        <button onclick="reuploadDocs(${item.activity_id})">Reupload</button>
                    </div>
                </td>
            `;
            returnedItemsElem.appendChild(tr);
        });
    }

    loadReturnedItems();
});

function editActivity(id) {
    window.location.href = `create.php?id=${id}`;
}

function reuploadDocs(id) {
    window.location.href = `upload.php?activity_id=${id}`;
}