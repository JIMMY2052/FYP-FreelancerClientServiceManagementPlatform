# Wallet Security Implementation for Freelancer Contract Protection

## Overview
This security feature ensures that freelancers have sufficient funds in their wallet to cover potential penalties if they breach a contract. This protects clients from financial loss while maintaining fair escrow practices.

## Implementation Details

### 1. **Job Application Wallet Balance Check**

#### Files Modified:
- `page/job/applyJob.php`
- `page/job/answer_questions.php`

#### Functionality:
- **Before Application**: When a freelancer attempts to apply for a job, the system checks if their wallet balance is at least equal to the job budget.
- **If Insufficient**: The application is blocked with a clear error message explaining the requirement.
- **Error Message Example**: "Insufficient wallet balance. Your wallet balance (RM 50.00) must be at least RM 100.00 to apply for this job. This ensures security for both parties in case of contract breach. Please top up your wallet."

#### UI Changes in `answer_questions.php`:
- Warning alert displayed when wallet balance is insufficient
- Shows current balance, required balance, and shortfall amount
- Direct link to wallet top-up page
- Submit button is disabled when balance is insufficient
- Button text changes to "Insufficient Balance - Top Up Required"

---

### 2. **Reserved Balance Calculation**

#### Concept:
Freelancers who have accepted or ongoing jobs cannot withdraw funds that would bring their balance below the total of all their active job commitments.

#### Reserved Balance Includes:
1. **Job Applications** with status: `accepted` or `in_progress` (from `job_application` table joined with `job` table)
2. **Agreements** with status: `pending`, `ongoing`, or `signed` (from `agreement` table)

#### Formula:
```
Reserved Balance = SUM(Job Budgets for Active Applications) + SUM(Payment Amounts for Active Agreements)
Available Balance = Total Balance - Reserved Balance
```

---

### 3. **Withdrawal Restrictions**

#### File Modified:
- `page/payment/withdraw_process.php`

#### Functionality:
- For **freelancers only**: System calculates reserved balance before allowing withdrawal
- For **clients**: No restrictions (standard balance check only)

#### Withdrawal Validation:
```php
if (Available Balance < Withdrawal Amount) {
    Error: "Insufficient available balance"
    Shows breakdown:
    - Current Balance: RM XXX
    - Reserved for ongoing jobs: RM YYY
    - Available for withdrawal: RM ZZZ
}
```

---

### 4. **Wallet Display Updates**

#### File Modified:
- `page/payment/wallet.php`

#### New Features:
For freelancers with reserved balance > 0, the wallet card now shows:
1. **Total Balance**: Complete wallet balance
2. **Reserved for Ongoing Jobs**: Amount locked for job commitments
3. **Available for Withdrawal**: Amount that can actually be withdrawn

#### Visual Layout:
```
┌─────────────────────────────┐
│ Total Balance               │
│ RM 500.00                   │
│                             │
│ Reserved for Ongoing Jobs:  │
│ RM 200.00                   │
│                             │
│ Available for Withdrawal:   │
│ RM 300.00                   │
│                             │
│ [Withdraw Button]           │
└─────────────────────────────┘
```

---

## Database Queries

### Reserved Balance Calculation (for Freelancers):

```sql
-- Job Applications
SELECT COALESCE(SUM(j.Budget), 0) as reserved_amount 
FROM job_application ja 
INNER JOIN job j ON ja.JobID = j.JobID 
WHERE ja.FreelancerID = ? 
AND ja.Status IN ('accepted', 'in_progress') 
AND j.Status NOT IN ('completed', 'cancelled')

-- Agreements
SELECT COALESCE(SUM(PaymentAmount), 0) as reserved_amount 
FROM agreement 
WHERE FreelancerID = ? 
AND Status IN ('pending', 'ongoing', 'signed')
```

---

## User Experience Flow

### Scenario 1: Freelancer Applying for Job

1. Freelancer views job (Budget: RM 100)
2. Clicks "Apply" → Redirected to screening questions page
3. **Wallet Check**: Balance = RM 50
4. **Warning Displayed**: "Insufficient wallet balance... need RM 50 more"
5. Submit button disabled
6. Freelancer clicks "Top up now" link
7. After topping up to RM 100+
8. Returns to application page
9. Submit button enabled
10. Can now submit application successfully

### Scenario 2: Freelancer Attempting Withdrawal

**Case A: With Ongoing Jobs**
1. Total Balance: RM 500
2. Ongoing Job Budget: RM 200
3. Available: RM 300
4. Attempts to withdraw RM 350
5. **Error**: "Insufficient available balance. Reserved for ongoing jobs: RM 200.00. Available for withdrawal: RM 300.00"
6. Can only withdraw up to RM 300

**Case B: Without Ongoing Jobs**
1. Total Balance: RM 500
2. Reserved: RM 0
3. Available: RM 500
4. Can withdraw full amount (up to RM 500)

---

## Security Benefits

### For Clients:
- ✅ **Guaranteed Financial Security**: If freelancer breaches contract, admin can deduct penalty from their wallet
- ✅ **Verified Commitment**: Only serious freelancers with sufficient funds can apply
- ✅ **Reduced Risk**: Lower chance of freelancers accepting jobs they can't afford to commit to

### For Freelancers:
- ✅ **Clear Requirements**: Know upfront how much balance needed
- ✅ **Transparent Reserved Amounts**: See exactly how much is locked and why
- ✅ **Fair System**: Only restricts based on actual commitments

### For Platform:
- ✅ **Dispute Resolution**: Admin can enforce financial penalties when needed
- ✅ **Trust Building**: Both parties protected by escrow-like system
- ✅ **Quality Control**: Filters out freelancers who aren't financially prepared

---

## Testing Scenarios

### Test 1: Application with Insufficient Balance
- Create freelancer with RM 50 balance
- Try to apply for job with RM 100 budget
- **Expected**: Warning shown, submit disabled

### Test 2: Application with Sufficient Balance
- Create freelancer with RM 150 balance
- Apply for job with RM 100 budget
- **Expected**: Application submitted successfully

### Test 3: Withdrawal with Reserved Balance
- Freelancer has RM 500 total
- Has accepted job worth RM 200
- Try to withdraw RM 350
- **Expected**: Error - only RM 300 available

### Test 4: Withdrawal without Reserved Balance
- Freelancer has RM 500 total
- No ongoing jobs
- Try to withdraw RM 400
- **Expected**: Withdrawal successful

---

## Future Enhancements

1. **Release Reserved Funds**: Automatically release reserved balance when jobs are completed or cancelled
2. **Partial Penalties**: Allow admin to set custom penalty amounts (not full job budget)
3. **Grace Period**: Implement warning system before full restriction
4. **Insurance Option**: Allow freelancers to purchase insurance instead of locking funds
5. **Tiered Requirements**: Reduce required balance based on freelancer rating/history

---

## Configuration

### Minimum Balance Multiplier
Currently set to 1:1 (balance must equal job budget). Could be made configurable:
```php
$required_balance = $job_budget * BALANCE_MULTIPLIER;
// Example: 0.5 = 50% of job budget required
// Example: 1.0 = 100% of job budget required (current)
// Example: 1.5 = 150% of job budget required
```

### Job Statuses Counted as "Active"
- Job Applications: `accepted`, `in_progress`
- Agreements: `pending`, `ongoing`, `signed`

These can be adjusted based on business rules.

---

## Technical Notes

- All monetary calculations use `floatval()` for precision
- Database queries use prepared statements for security
- Reserved balance is recalculated on each wallet/withdrawal page load
- No caching implemented (consider adding for performance)
- Transaction-safe: Uses `beginTransaction()` and `rollBack()` for data integrity

---

## Support & Maintenance

For issues or questions regarding this feature, check:
1. Error logs in `error_log` for detailed error messages
2. Database consistency for job/agreement statuses
3. Wallet transaction history for audit trail

Last Updated: November 30, 2025
