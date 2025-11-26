# Laravel Payment System - Project Roadmap

## Project Overview
A complete Laravel application with admin and user panels for managing payment transactions using the Payment Service Provider integration.

---

## Phase 1: Project Setup & Configuration

### 1.1 Laravel Installation & Setup
- [ ] Install Laravel (latest stable version)
- [ ] Configure database connection
- [ ] Set up environment variables (.env)
- [ ] Install required packages:
  - Laravel Breeze/Jetstream (for authentication)
  - Laravel UI (for admin panel)
  - Additional packages as needed

### 1.2 Project Structure
- [ ] Create service classes for Payment Provider
- [ ] Set up controllers structure
- [ ] Create models and migrations
- [ ] Set up routes (web.php, api.php)

---

## Phase 2: Database Schema & Models

### 2.1 Database Tables
- [ ] **users** (extend Laravel default)
  - id, name, email, password, role (admin/user), created_at, updated_at
  
- [ ] **transactions**
  - id, user_id, merchant_order_id, order_id, operation_id, operation_type
  - amount, currency, status, pay_method
  - order_desc, error_message, error_code
  - initial_amount, total_refunded_amount, customer_id
  - merchant_id, merchant_custom_data (JSON)
  - requisites (JSON) - stores card/bank/account details
  - bin_data (JSON) - stores card_brand, country, bank_name
  - customer_info (JSON) - stores customer details
  - browser_info (JSON) - stores browser information
  - created_at, finished_at, updated_at
  - Indexes: user_id, merchant_order_id, order_id, operation_id, status
  
- [ ] **transaction_logs** (optional - for audit trail)
  - id, transaction_id, event_type, data (JSON), created_at

### 2.2 Models
- [ ] User model (with relationships)
- [ ] Transaction model (with relationships)
- [ ] TransactionRequisite model (optional, if separate table)

---

## Phase 3: Authentication System

### 3.1 User Authentication
- [ ] Login/Register functionality
- [ ] Password reset
- [ ] Email verification (optional)
- [ ] Role-based access control (Admin/User)

### 3.2 Middleware
- [ ] Admin middleware (protect admin routes)
- [ ] User middleware (protect user routes)
- [ ] Guest middleware (for public routes)

---

## Phase 4: Payment Service Provider Integration

### 4.1 Service Class
- [ ] Create `PaymentServiceProvider` service class
  - `sign()` method (for signing requests)
  - `verify()` method (for verifying callbacks)
  - `recursiveSortAndClean()` private method
  - `generateSHA256()` private method

### 4.2 API Endpoints Configuration
- [ ] **Payout API**: `https://sandbox-api.debitly.tech/payout` (POST)
  - Request: Signed JSON with full payout request
  - Response: `{status, order_id, operation_id, signature}`
  
- [ ] **Status API**: `https://sandbox-api.debitly.tech/status` (POST)
  - Request: `{merchant_id, order_id, operation_id, signature}`
  - Response: Full transaction details with signature
  
- [ ] **Callback Endpoint**: `/api/payment/callback` (POST JSON)
  - Receive JSON callback from payment provider
  - Verify signature
  - Update transaction status
  
- [ ] **Result URL Endpoint**: `/payment/result` (POST form-urlencoded)
  - Handle form POST from result_url redirect
  - Verify signature
  - Update transaction status
  - Redirect user to appropriate page

### 4.3 Configuration
- [ ] Store merchant control value in .env (`PAYMENT_MERCHANT_CONTROL_VALUE`)
- [ ] Store merchant ID in .env (`PAYMENT_MERCHANT_ID`)
- [ ] Store API base URL in .env (`PAYMENT_API_URL=https://sandbox-api.debitly.tech`)
- [ ] Store callback URLs in .env (callback_url, result_url, processing_url, success_url, fail_url, back_url)
- [ ] Create payment configuration file (`config/payment.php`)

---

## Phase 5: User Panel

### 5.1 Dashboard
- [ ] User dashboard with statistics
  - Total transactions
  - Successful transactions
  - Failed transactions
  - Total amount processed

### 5.2 Transaction Management
- [ ] **Create Transaction Page**
  - Form to input transaction details:
    - Basic: amount, currency, order description, payment method (sepa, card, etc.)
    - Merchant links: callback_url, result_url, processing_url, success_url, fail_url, back_url
    - Customer information: id, email, ip_address
    - Browser info: user_agent, accept_header, javascript_enabled, language, color_depth, timezone, java_enabled, screen_height, screen_width
    - Requisites (based on payment method):
      - For SEPA: account_number, account_name, customer (first_name, last_name, address)
      - For Card: card details
      - For Bank: bank details
  - Submit button (creates signed request, sends to payout API)
  - Show loading state during API call
  - Display response (order_id, operation_id, status)
  
- [ ] **Transaction List Page**
  - View all user's transactions
  - Display: merchant_order_id, amount, currency, status, pay_method, created_at
  - Filter by status (processing, approved, declined)
  - Search by merchant_order_id, order_id
  - Pagination
  
- [ ] **Transaction Detail Page**
  - View full transaction details
  - Status information with timestamps
  - Payment method details
  - Requisites information
  - Customer information
  - Error messages and codes (if any)
  - Operation details (order_id, operation_id, operation_type)
  - "Check Status" button (calls status API)

### 5.3 Status Check
- [ ] Button to check transaction status (calls status API)
- [ ] Real-time status update via AJAX
- [ ] Display status changes
- [ ] Show updated transaction details

---

## Phase 6: Admin Panel

### 6.1 Admin Dashboard
- [ ] Admin dashboard with statistics
  - Total users
  - Total transactions
  - Total revenue
  - Recent transactions
  - Charts/graphs (optional)

### 6.2 User Management
- [ ] **Users List Page**
  - View all users
  - Search users
  - Filter by role
  - View user details
  - Edit user (optional)
  - Delete user (optional)
  - View user's transactions

### 6.3 Transaction Management
- [ ] **All Transactions List**
  - View all transactions from all users
  - Filter by:
    - User
    - Status
    - Date range
    - Payment method
    - Amount range
  - Search functionality
  - Export to CSV/Excel (optional)
  - Pagination
  
- [ ] **Transaction Detail View**
  - Full transaction details
  - User information
  - Payment details
  - Requisites information
  - Status history
  - Ability to manually update status (optional)

### 6.4 Reports & Analytics
- [ ] Transaction reports
- [ ] Revenue reports
- [ ] User activity reports
- [ ] Export functionality

---

## Phase 7: API Integration

### 7.1 Payment API Integration
- [ ] HTTP client setup (Guzzle)
- [ ] **Payout API Integration**
  - Build payout request payload
  - Sign the request using PaymentServiceProvider
  - Send POST request to `https://sandbox-api.debitly.tech/payout`
  - Handle response: `{status, order_id, operation_id, signature}`
  - Store order_id and operation_id in database
  - Verify response signature (optional)
  
- [ ] **Status API Integration**
  - Build status request: `{merchant_id, order_id, operation_id}`
  - Sign the request using PaymentServiceProvider
  - Send POST request to `https://sandbox-api.debitly.tech/status`
  - Handle response with full transaction details
  - Verify response signature
  - Update transaction in database

### 7.2 Callback Handlers
- [ ] **JSON Callback Handler** (`/api/payment/callback`)
  - Receive POST JSON callback
  - Verify callback signature
  - Extract transaction data
  - Update transaction status in database
  - Store requisites, bin_data, error messages
  - Log callback data
  - Handle duplicate callbacks (idempotency)
  - Return appropriate HTTP response
  
- [ ] **Form Callback Handler** (`/payment/result`)
  - Receive form POST (application/x-www-form-urlencoded)
  - Extract: merchant_id, signature, merchant_order_id, order_id, operation_id, operation_type, status, pay_method, amount, currency, error_message, error_code
  - Verify signature
  - Update transaction status
  - Redirect user based on status:
    - Success → success_url
    - Failed → fail_url
    - Processing → processing_url

---

## Phase 8: Frontend Implementation

### 8.1 User Interface
- [ ] Modern, responsive design
- [ ] Use Bootstrap/Tailwind CSS
- [ ] User-friendly forms
- [ ] Loading states
- [ ] Success/Error notifications
- [ ] Mobile responsive

### 8.2 Admin Interface
- [ ] Professional admin theme
- [ ] Data tables with sorting/filtering
- [ ] Dashboard widgets
- [ ] Charts and graphs
- [ ] Responsive design

---

## Phase 9: Security & Validation

### 9.1 Security Measures
- [ ] CSRF protection
- [ ] XSS protection
- [ ] SQL injection prevention
- [ ] Input validation
- [ ] Rate limiting
- [ ] Secure API keys storage

### 9.2 Validation
- [ ] Form validation
- [ ] API request validation
- [ ] Callback signature verification
- [ ] Data sanitization

---

## Phase 10: Testing & Documentation

### 10.1 Testing
- [ ] Unit tests for Payment Service Provider
- [ ] Feature tests for transactions
- [ ] API endpoint tests
- [ ] Callback verification tests

### 10.2 Documentation
- [ ] API documentation
- [ ] User guide
- [ ] Admin guide
- [ ] Installation guide
- [ ] Code comments

---

## Phase 11: Additional Features (Optional)

### 11.1 Enhanced Features
- [ ] Email notifications (transaction status)
- [ ] SMS notifications (optional)
- [ ] Transaction receipts (PDF generation)
- [ ] Multi-currency support
- [ ] Transaction refunds
- [ ] Audit logs
- [ ] Activity logs

---

## Technical Stack

- **Backend**: Laravel (latest)
- **Database**: MySQL/PostgreSQL
- **Frontend**: Blade Templates + Bootstrap/Tailwind
- **Authentication**: Laravel Breeze/Jetstream
- **HTTP Client**: Guzzle (for API calls)
- **Additional**: jQuery/Axios for AJAX

## API Integration Details

### Payout API
- **Endpoint**: `https://sandbox-api.debitly.tech/payout`
- **Method**: POST
- **Content-Type**: application/json
- **Request**: Signed JSON payload (as per php-sign-request.php)
- **Response**: `{status, order_id, operation_id, signature}`

### Status API
- **Endpoint**: `https://sandbox-api.debitly.tech/status`
- **Method**: POST
- **Content-Type**: application/json
- **Request**: `{merchant_id, order_id, operation_id, signature}`
- **Response**: Full transaction details with signature

### Callback (JSON)
- **Endpoint**: `/api/payment/callback` (our endpoint)
- **Method**: POST
- **Content-Type**: application/json
- **Payload**: Full transaction details with signature
- **Action**: Verify signature, update transaction

### Result URL (Form)
- **Endpoint**: `/payment/result` (our endpoint)
- **Method**: POST
- **Content-Type**: application/x-www-form-urlencoded
- **Fields**: merchant_id, signature, merchant_order_id, order_id, operation_id, operation_type, status, pay_method, amount, currency, error_message, error_code
- **Action**: Verify signature, update transaction, redirect user

---

## File Structure Preview

```
Debitly/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── UserController.php
│   │   │   │   └── TransactionController.php
│   │   │   ├── User/
│   │   │   │   ├── DashboardController.php
│   │   │   │   └── TransactionController.php
│   │   │   └── PaymentController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Transaction.php
│   └── Services/
│       └── PaymentServiceProvider.php
├── database/
│   └── migrations/
│       ├── create_users_table.php
│       └── create_transactions_table.php
├── resources/
│   ├── views/
│   │   ├── admin/
│   │   ├── user/
│   │   └── auth/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
└── config/
    └── payment.php
```

---

## Implementation Priority

1. **High Priority**: Phases 1-4 (Setup, Database, Auth, Payment Integration)
2. **Medium Priority**: Phases 5-7 (User Panel, Admin Panel, API)
3. **Low Priority**: Phases 8-11 (UI Polish, Testing, Additional Features)

---

## Estimated Timeline

- **Phase 1-4**: 2-3 days
- **Phase 5-7**: 3-4 days
- **Phase 8-11**: 2-3 days
- **Total**: ~7-10 days (depending on complexity and features)

---

## Questions to Consider

1. Which authentication package? (Breeze, Jetstream, or custom)
2. Which CSS framework? (Bootstrap, Tailwind, or custom)
3. Admin panel theme preference? (AdminLTE, CoreUI, or custom)
4. Database preference? (MySQL, PostgreSQL)
5. Any specific features to prioritize or skip?

---

---

## Key Integration Points Summary

### 1. Payout Request Flow
- User fills form → Sign request → POST to `/payout` → Store response (order_id, operation_id)

### 2. Status Check Flow
- User clicks "Check Status" → Sign status request → POST to `/status` → Update transaction

### 3. Callback Flow (JSON)
- Payment provider POSTs to `/api/payment/callback` → Verify signature → Update transaction

### 4. Result URL Flow (Form)
- Payment provider redirects to `/payment/result` (form POST) → Verify signature → Update transaction → Redirect user

### 5. Signature Verification
- All callbacks/results must verify signature before processing
- Use `PaymentServiceProvider::verify()` method
- Reject if signature doesn't match

---

**Ready to proceed? Please review and let me know:**
1. Any changes to the roadmap
2. Features to add/remove
3. Preferences for tech stack (Breeze/Jetstream, Bootstrap/Tailwind, etc.)
4. Priority order if different
5. Any specific requirements or constraints

