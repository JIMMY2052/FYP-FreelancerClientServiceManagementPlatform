# Test Data for Login & Signup

## Admin Login Test Data

| Username/Email | Password | Expected Result | Status |
|---|---|---|---|
| jimmychankahlok@gmail.com | admin123 | Login successful, redirect to admin dashboard | ✅ Pass |
| admin@test.com | wrongpassword | Error: Invalid credentials | ⏳ Pending |
| invalid_email | password123 | Error: Invalid email format | ⏳ Pending |
| (empty) | password123 | Error: Email/Username required | ⏳ Pending |
| jimmychankahlok@gmail.com | (empty) | Error: Password required | ⏳ Pending |

---

## Freelancer Login Test Data

| Email | Password | Expected Result | Status |
|---|---|---|---|
| jimmychankahlok66@gmail.com | password123 | Login successful, redirect to freelancer dashboard | ✅ Pass |
| jc@gmail.com | password123 | Login successful, redirect to freelancer dashboard | ✅ Pass |
| nonexistent@gmail.com | password123 | Error: Account not found | ⏳ Pending |
| jimmychankahlok66@gmail.com | wrongpass | Error: Invalid password | ⏳ Pending |
| (empty) | password123 | Error: Email required | ⏳ Pending |
| jimmychankahlok66@gmail.com | (empty) | Error: Password required | ⏳ Pending |

---

## Client Login Test Data

| Email | Password | Expected Result | Status |
|---|---|---|---|
| jimmyckl-wm22@student.tarc.edu.my | password123 | Login successful, redirect to client dashboard | ✅ Pass |
| genting@gmail.com | password123 | Login successful, redirect to client dashboard | ✅ Pass |
| lucifa@gmail.com | password123 | Login successful, redirect to client dashboard | ✅ Pass |
| nonexistent@gmail.com | password123 | Error: Account not found | ⏳ Pending |
| jimmyckl-wm22@student.tarc.edu.my | wrongpass | Error: Invalid password | ⏳ Pending |

---

## Freelancer Signup Test Data

| First Name | Last Name | Email | Password | Confirm Password | Expected Result | Status |
|---|---|---|---|---|---|---|
| John | Doe | john.doe@gmail.com | password123 | password123 | Account created successfully, redirect to login | ⏳ Pending |
| Jane | Smith | jane.smith@gmail.com | pass456 | pass456 | Account created successfully, redirect to login | ⏳ Pending |
| Test | User | testuser@gmail.com | (empty) | (empty) | Error: Password required | ⏳ Pending |
| Alice | Johnson | alice@gmail.com | pass123 | passdiff | Error: Passwords do not match | ⏳ Pending |
| Bob | Brown | bob@gmail.com | pass | pass | Error: Password too short (min 6 characters) | ⏳ Pending |
| Charlie | Davis | (empty) | password123 | password123 | Error: Email required | ⏳ Pending |
| David | Wilson | jimmychankahlok66@gmail.com | password123 | password123 | Error: Email already exists | ⏳ Pending |
| (empty) | Garcia | frank.garcia@gmail.com | password123 | password123 | Error: First name required | ⏳ Pending |
| Edward | (empty) | edward.harris@gmail.com | password123 | password123 | Error: Last name required | ⏳ Pending |
| Frank | Martinez | invalid.email | password123 | password123 | Error: Invalid email format | ⏳ Pending |

---

## Client Signup Test Data

| Company Name | Email | Password | Confirm Password | Expected Result | Status |
|---|---|---|---|---|---|
| Tech Solutions | techsol@gmail.com | password123 | password123 | Account created successfully, redirect to login | ⏳ Pending |
| Creative Agency | creative@gmail.com | pass456 | pass456 | Account created successfully, redirect to login | ⏳ Pending |
| Design Studio | (empty) | password123 | password123 | Error: Email required | ⏳ Pending |
| Marketing Co | marketing@gmail.com | (empty) | (empty) | Error: Password required | ⏳ Pending |
| Startup Inc | startup@gmail.com | pass | pass | Error: Password too short (min 6 characters) | ⏳ Pending |
| Dev Team | devteam@gmail.com | pass123 | passdiff | Error: Passwords do not match | ⏳ Pending |
| Duplicate Co | jimmyckl-wm22@student.tarc.edu.my | password123 | password123 | Error: Email already exists | ⏳ Pending |
| (empty) | newcompany@gmail.com | password123 | password123 | Error: Company name required | ⏳ Pending |
| Invalid Email Co | invalid.email | password123 | password123 | Error: Invalid email format | ⏳ Pending |

---

## Login Edge Cases Test Data

| Test Case | Input | Expected Result | Status |
|---|---|---|---|
| SQL Injection in Email | admin' OR '1'='1 | Error: Invalid email format | ⏳ Pending |
| SQL Injection in Password | ' OR '1'='1' -- | Login fails, error message shown | ⏳ Pending |
| XSS in Email | <script>alert('xss')</script>@test.com | Error: Invalid email format | ⏳ Pending |
| Very Long Email | aaaaaaaaaaaaaaaaa...@test.com (300+ chars) | Error: Email too long or Invalid | ⏳ Pending |
| Very Long Password | aaaaaaaaaaaaaaaaa...aaaa (1000+ chars) | Login attempt with validation | ⏳ Pending |
| Special Characters Email | test+tag@gmail.com | Login successful (if account exists) | ⏳ Pending |
| Case Sensitivity | JIMMYCHANKAHLOK66@GMAIL.COM | Login successful (if email matches) | ⏳ Pending |
| Spaces in Email | jimmychankahlok66@gmail.com (with spaces) | Error: Invalid email format | ⏳ Pending |
| Deleted User Account | (previously active, now deleted) | Error: Account not found or Account deleted | ⏳ Pending |
| Suspended User Account | (marked as inactive/suspended) | Error: Account is suspended/inactive | ⏳ Pending |

---

## Signup Edge Cases Test Data

| Test Case | Input | Expected Result | Status |
|---|---|---|---|
| Email with + | john+test@gmail.com | Account created successfully | ⏳ Pending |
| Email with numbers | user123@test.com | Account created successfully | ⏳ Pending |
| Very Long Name | (256+ character name) | Might be truncated or error | ⏳ Pending |
| Special Characters Name | John@#$%Doe | Validation based on rules | ⏳ Pending |
| Password with special chars | P@ssw0rd!#$% | Account created successfully | ⏳ Pending |
| Name with spaces | John Paul Smith | Account created successfully | ⏳ Pending |
| Name with hyphens | Mary-Jane Watson | Account created successfully | ⏳ Pending |
| Rapid successive signups | Same email, multiple signup attempts | Rate limiting or error | ⏳ Pending |
| Unicode characters | João José | Account creation (depends on DB support) | ⏳ Pending |

---

## Notes for Testing

### Existing Valid Accounts (Already Created):

**Freelancers:**
- Email: `jimmychankahlok66@gmail.com`
- Email: `jc@gmail.com`

**Clients:**
- Email: `jimmyckl-wm22@student.tarc.edu.my`
- Email: `genting@gmail.com`
- Email: `lucifa@gmail.com`

**Admin:**
- Email: `jimmychankahlok@gmail.com`

### Test Execution Instructions:

1. **Before Testing**: Clear browser cache and cookies
2. **Password Testing**: All passwords should be hashed before storage
3. **Email Validation**: Verify email format validation works
4. **Session Management**: Check if sessions are created correctly after login
5. **Error Messages**: Verify error messages don't reveal sensitive info
6. **Security**: Test for SQL injection, XSS, and CSRF vulnerabilities
7. **Redirect URLs**: Verify correct redirects after login/signup success/failure

### Browser Testing:

- [ ] Chrome (Latest)
- [ ] Firefox (Latest)
- [ ] Safari (Latest)
- [ ] Edge (Latest)
- [ ] Mobile browsers

### Status Legend:

- ✅ Pass - Test completed successfully
- ❌ Fail - Test failed, needs fixing
- ⏳ Pending - Test not yet executed
