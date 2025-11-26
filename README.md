# Debitly Payment System

A complete Laravel application with admin and user panels for managing payment transactions using the Debitly Payment API integration.

## Features

- **User Panel**
  - User dashboard with transaction statistics
  - Create new payment transactions
  - View transaction history
  - Check transaction status
  - Filter and search transactions

- **Admin Panel**
  - Admin dashboard with overall statistics
  - View all users and their transactions
  - View all transactions from all users
  - Filter and search functionality

- **Payment Integration**
  - Payout API integration
  - Status API integration
  - JSON callback handler
  - Form callback handler (result URL)
  - Signature verification for all callbacks

## Requirements

- PHP >= 8.1
- Composer
- MySQL/PostgreSQL
- Web server (Apache/Nginx) or PHP built-in server

## Installation

1. **Clone or navigate to the project directory**
   ```bash
   cd Debitly
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Create environment file**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Configure database in `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=debitly
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Configure payment settings in `.env`**
   ```env
   PAYMENT_API_URL=https://sandbox-api.debitly.tech
   PAYMENT_MERCHANT_ID=85
   PAYMENT_MERCHANT_CONTROL_VALUE=1ecacb670059bcb7943a9f67da5ccfee441edbc3
   PAYMENT_CALLBACK_URL=http://localhost/api/payment/callback
   PAYMENT_RESULT_URL=http://localhost/payment/result
   PAYMENT_PROCESSING_URL=http://localhost/payment/processing
   PAYMENT_SUCCESS_URL=http://localhost/payment/success
   PAYMENT_FAIL_URL=http://localhost/payment/fail
   PAYMENT_BACK_URL=http://localhost/
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   ```

8. **Seed database (creates admin and test user)**
   ```bash
   php artisan db:seed
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

## Default Login Credentials

**Admin:**
- Email: `admin@debitly.com`
- Password: `password`

**User:**
- Email: `user@debitly.com`
- Password: `password`

## Project Structure

```
Debitly/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin controllers
│   │   │   ├── User/           # User controllers
│   │   │   ├── Auth/           # Authentication controllers
│   │   │   └── PaymentController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Transaction.php
│   └── Services/
│       └── PaymentServiceProvider.php
├── config/
│   └── payment.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── admin/              # Admin views
│       ├── user/               # User views
│       ├── auth/               # Authentication views
│       └── layouts/
└── routes/
    ├── web.php
    └── api.php
```

## API Endpoints

### Payment APIs (Internal)
- `POST /api/payment/payout` - Create payout request (requires auth)
- `POST /api/payment/status` - Check transaction status (requires auth)
- `POST /api/payment/callback` - Receive JSON callback from payment provider (public)

### Web Routes
- `POST /payment/result` - Receive form POST from result URL redirect (public)
- `GET /payment/success` - Success page
- `GET /payment/fail` - Failure page
- `GET /payment/processing` - Processing page

## Usage

1. **Login** as a user or admin
2. **Create Transaction** (User only)
   - Fill in transaction details
   - Submit to create payout request
   - Transaction will be sent to Debitly API
3. **View Transactions**
   - See all your transactions
   - Filter by status
   - Search by order ID
4. **Check Status**
   - Click "Check Status" on any transaction
   - Updates transaction from API
5. **Admin Panel**
   - View all users
   - View all transactions
   - Filter and search

## Payment Flow

1. User creates transaction → Signed request sent to `/payout` API
2. Payment provider processes → Sends callback to `/api/payment/callback`
3. User redirected → Form POST to `/payment/result`
4. Status updates → Transaction status updated in database

## Security

- All callbacks are verified using signature verification
- CSRF protection enabled
- Admin routes protected by middleware
- User authentication required for user routes

## Notes

- Make sure to update callback URLs in `.env` to match your domain
- The payment API uses sandbox environment by default
- All signatures are verified before processing callbacks
- Transaction data is stored in JSON format for flexibility

## Troubleshooting

1. **Composer install fails**: Make sure PHP curl extension is enabled or use `--ignore-platform-req=ext-curl`
2. **Database connection error**: Check database credentials in `.env`
3. **Callback not working**: Verify callback URLs are accessible from the internet
4. **Signature verification fails**: Check merchant control value in `.env`

## License

MIT

