# Revision Countdown Feature - Setup Guide

## Overview
The revision countdown feature has been implemented to limit the number of times a client can request revisions on submitted work. When revisions reach 0, the client can only accept the work.

## Database Changes Required

### Step 1: Add RemainingRevisions Column
Run the SQL migration script to add the `RemainingRevisions` column to the `agreement` table:

```sql
-- Run this SQL script in your database
-- File: add_remaining_revisions_column.sql

ALTER TABLE `agreement` 
ADD COLUMN IF NOT EXISTS `RemainingRevisions` INT(11) NOT NULL DEFAULT 3 
AFTER `PaymentAmount`;

UPDATE `agreement` 
SET `RemainingRevisions` = 3 
WHERE `RemainingRevisions` IS NULL OR `RemainingRevisions` = 0;
```

**To execute:**
- Option 1: Run the file `add_remaining_revisions_column.sql` in phpMyAdmin
- Option 2: Copy the SQL and execute directly in your database management tool

## How It Works

### 1. Initial Revision Count

**For Gigs:**
- The revision count is taken from the gig's `RevisionCount` field
- Freelancers set this when creating their gig (can be 1, 2, 3, 5, 10, or unlimited)
- Different gigs can have different revision counts
- Set in `create_gig.php` → `gig_price.php`

**For Job-Based Projects:**
- Default: **3 revisions**
- Set automatically in `agreementClient_process.php`
- Consistent across all job-based agreements

### 2. Revision Countdown Process

**When client reviews work:**

1. **Client can see remaining revisions** on the review page
   - Displayed in a dedicated "Revisions" card
   - Color-coded: Green (>2), Yellow (1-2), Red (0)

2. **Client requests revision:**
   - Review notes are required
   - Confirmation shows remaining count after this revision
   - System decrements `RemainingRevisions` by 1
   - Freelancer receives notification with remaining count

3. **When revisions reach 0:**
   - "Request Revision" button is automatically disabled
   - Client can only "Accept Work"
   - Warning messages displayed
   - System prevents revision requests via validation

4. **Special Case - Unlimited Revisions:**
   - When a gig has "unlimited" revisions, `RemainingRevisions` is `NULL`
   - System displays "Unlimited" instead of a number
   - No countdown occurs - revisions are never decremented
   - Client can request revisions indefinitely
   - No warnings or restrictions apply

### 3. UI Features

**Review Work Page (`review_work.php`):**
- ✅ Revision counter card showing remaining revisions
- ✅ Visual warnings when revisions are low (≤2)
- ✅ Error message when no revisions remain
- ✅ Disabled button when count is 0
- ✅ JavaScript validation to prevent bypass

**Review Process (`review_work_process.php`):**
- ✅ Server-side validation to prevent revision requests when count is 0
- ✅ Automatic decrement on each revision request
- ✅ Updated notifications with remaining count
- ✅ Success messages showing remaining revisions

## Files Modified

### Core Functionality
1. **review_work.php** - Display revision count and UI controls
2. **review_work_process.php** - Handle revision countdown logic

### Agreement Creation
3. **agreementClient_process.php** - Set initial revision count (jobs: 3 revisions)
4. **gigAgreement_process.php** - Set initial revision count (uses gig's RevisionCount)
5. **process_gig_payment.php** - Set initial revision count (uses gig's RevisionCount)

### Database Migration
6. **add_remaining_revisions_column.sql** - Database schema update

## Testing Checklist

- [ ] Run the SQL migration script
- [ ] **Test Gig-Based Agreement:**
  - [ ] Create a gig with 2 revisions
  - [ ] Client purchases the gig
  - [ ] Verify agreement has RemainingRevisions = 2
  - [ ] Create another gig with 5 revisions
  - [ ] Verify new agreement has RemainingRevisions = 5
- [ ] **Test Job-Based Agreement:**
  - [ ] Create a job and accept application
  - [ ] Verify agreement has RemainingRevisions = 3 (default)
- [ ] **Test Revision Countdown:**
  - [ ] Submit work as freelancer
  - [ ] Request revision as client (should decrement by 1)
  - [ ] Continue until count reaches 0
  - [ ] Verify "Request Revision" button is disabled at 0
  - [ ] Verify client can only accept work when count is 0
- [ ] Check notifications include remaining revision count

## Configuration

### Changing Default Revisions for Jobs

To change the default number of revisions for job-based projects, update this line in:

**File: `agreementClient_process.php`** (line ~103)
```php
$default_revisions = 3; // Change this number to your desired default
```

### Changing Revision Options for Gigs

Freelancers select revision counts when creating gigs. To modify available options, edit:

**File: `gig_price.php`** (around line 200)
```php
<select id="revisions" name="revisions" required>
    <option value="" disabled selected hidden>Select</option>
    <option value="1">1 Revision</option>
    <option value="2">2 Revisions</option>
    <option value="3">3 Revisions</option>
    <option value="5">5 Revisions</option>
    <option value="10">10 Revisions</option>
    <option value="unlimited">Unlimited Revisions</option>
</select>
```

**Note:** The "unlimited" option is stored as `NULL` in the database. The revision countdown system treats `NULL` or `0` values as "no limit" and will not decrement or restrict revisions.

## Error Handling

The system includes multiple validation layers:

1. **Client-side (JavaScript):** Prevents form submission when count is 0
2. **UI-level:** Disables button when count is 0
3. **Server-side:** Validates count before processing revision request
4. **Database:** Integer constraint ensures valid values

## Notifications

Freelancers receive enhanced notifications:
- "Your work submission for '{project}' needs revisions. Please check the client's feedback and resubmit. You have X revision(s) remaining."
- Final revision: "This is your final revision."

Clients receive feedback:
- Success: "Revision requested. You have X revision(s) remaining. The freelancer has been notified."
- Error: "No revisions remaining. You can only accept the work."

## Future Enhancements (Optional)

Consider implementing:
- Different revision counts per project type
- Ability for client to purchase additional revisions
- Revision history tracking
- Analytics on revision usage patterns
