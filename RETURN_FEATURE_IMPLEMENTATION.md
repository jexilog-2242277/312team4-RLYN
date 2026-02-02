# OSAs Dashboard Return Feature Implementation

## Overview
This implementation converts the "Delete" functionality to a "Return" workflow without modifying the database structure. Items are not deleted but instead marked as "returned" with notes from OSAs staff.

## Changes Made

### 1. **Frontend Changes**

#### `/js/dashboard.js`
- **Replaced Delete Buttons with Return Buttons**: Changed all "Delete" buttons to "Return" buttons for both activities and documents
- **Added Return Modal**: Created a modal dialog that appears when OSAs staff clicks "Return"
- **Return Modal Features**:
  - Displays the item name being returned
  - Text area for OSAs staff to enter notes about what needs to be changed
  - Cancel and Submit buttons
  - Modal closes after successful submission

**Key Functions Added**:
- `openReturnModal(type, id, name)` - Opens the return confirmation dialog
- `submitReturnBtn.addEventListener()` - Handles the return submission

#### `/html/dashboard.php`
- Enabled the "Returned Items" navigation link for students
- This link points to `/html/returned.php`

#### `/html/returned.php`
- Updated to display returned items in a table format
- Shows item details, return notes, and date returned
- Added "Reupload" buttons to quickly navigate students to create/upload pages
- Fetches returned items from the new PHP endpoint

### 2. **Backend Changes**

#### `/php/return_item.php` (NEW)
Handles the return of items:
- **Authentication**: Only OSAS and admin users can return items
- **Return Record Creation**: Creates a structured return record with:
  - Unique return ID
  - Item type (activity/document)
  - Item ID and name
  - Organization ID
  - Return notes from OSAs staff
  - Timestamp
  - Status (returned)

- **Logging Strategy** (No Database Changes):
  - Returns are logged to JSON files in `/returns_log/` directory
  - Daily files: `returns_YYYY-MM-DD.json` (full audit log)
  - Daily files: `session_returns_YYYY-MM-DD.json` (for student notifications)
  - File-based approach avoids database schema changes

#### `/php/fetch_returned_items.php` (NEW)
Fetches returned items for student view:
- **Role-Based Filtering**:
  - Students see only returned items from their organization
  - OSAS/Admin see all returned items
- **Data Source**: Reads from JSON log files
- **Sorting**: Returns sorted by most recent first

### 3. **Directory Structure**
```
project-root/
├── returns_log/                    (NEW - auto-created)
│   ├── returns_2025-02-02.json    (Full audit log)
│   └── session_returns_2025-02-02.json  (Student notifications)
├── php/
│   ├── return_item.php            (NEW - handles returns)
│   ├── fetch_returned_items.php   (NEW - retrieves returns)
│   └── delete_activity.php        (unchanged)
├── js/
│   └── dashboard.js               (updated)
├── html/
│   ├── dashboard.php              (updated)
│   └── returned.php               (updated)
└── ...
```

## Workflow

### For OSAs/Admin Users:
1. View activities or documents in the dashboard
2. Click "Return" button instead of "Delete"
3. Modal appears prompting for return notes
4. Enter feedback about what needs to be changed
5. Click "Return Item" to submit
6. Item is logged as returned, student is notified

### For Students:
1. Navigate to "Returned Items" link in sidebar
2. View all items returned by OSAs with feedback notes
3. Click "Reupload" to make changes and resubmit

## Data Storage

### Return Records (JSON Format):
```json
{
  "return_id": "return_1234567890",
  "type": "activity|document",
  "item_id": 123,
  "item_name": "Activity Name",
  "org_id": 456,
  "osas_user_id": 789,
  "note": "Please provide more details in section 2",
  "timestamp": "2025-02-02 14:30:00",
  "status": "returned"
}
```

## Key Benefits

✅ **No Database Schema Changes** - Uses file-based JSON logging
✅ **Zero Data Loss** - Items are never actually deleted
✅ **Audit Trail** - Complete record of all returns with notes
✅ **Student Feedback** - Clear communication about required changes
✅ **Simple Implementation** - Minimal changes to existing code
✅ **Role-Based Access** - Students only see their own returned items

## Security Considerations

- Return operations only allowed for OSAS/Admin roles
- File-based logs are created in a dedicated directory
- Sensitive notes are stored securely in JSON files
- Session-based authentication checked on all endpoints

## Future Enhancements (Optional)

- Email notifications when items are returned
- Search/filter for returned items by date or reason
- Archive old return logs
- Export return history for reporting
- Admin dashboard to monitor return statistics

## Testing Checklist

- [ ] OSAs user can click "Return" on activities
- [ ] OSAs user can click "Return" on documents
- [ ] Return modal appears with text area for notes
- [ ] Return submits successfully with notes
- [ ] Student can view returned items
- [ ] Returned item displays OSAs note
- [ ] "Reupload" button redirects correctly
- [ ] Multiple returns are logged properly
- [ ] JSON files are created in `/returns_log/`

## Troubleshooting

**Returns not appearing for students:**
- Check `/returns_log/` directory exists and has JSON files
- Verify student's organization ID matches in return record

**Modal not appearing:**
- Check browser console for JavaScript errors
- Verify `openReturnModal()` function is called

**Permission denied on return:**
- Ensure user role is 'osas' or 'admin'
- Check session is properly established

## Backward Compatibility

The old `delete_activity.php` endpoint remains unchanged and functional for any other parts of the system that might use it. The new return workflow is completely additive and doesn't interfere with existing functionality.
