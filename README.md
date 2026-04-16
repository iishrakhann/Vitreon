# PuneEventHub

PuneEventHub is a multi-module venue management platform scaffolded for the Pune market. It combines:

- `php-app/` for the customer-facing web app, clean routing, sessions, and owner dashboard shell
- `python-ai/` for AI-powered review sentiment scoring and recommendation-ready service endpoints
- `java-booking-validator/` for atomic booking validation under concurrency
- `database/` for MySQL schema and locking-oriented table design

## Architecture Highlights

- Lavender Dream visual system using CSS custom properties and glassmorphism
- SEO-friendly PHP router with route parameters and controller dispatch
- Mobile-first discovery UX with a bottom navigation bar
- Google OAuth 2.0 entry and callback flow in the PHP app
- Razorpay webhook verification with Twilio owner SMS dispatch hooks
- PHP persistence layer for users, bookings, and webhook events backed by MySQL
- Booking initiation path that calls the Spring Boot validator before creating a Razorpay order
- MySQL InnoDB schema designed for row-level locking and slot holds
- Spring Boot service pattern ready for `SELECT ... FOR UPDATE` based validation
- Flask sentiment API that accepts a list of review strings and returns normalized scores from `0` to `1`

## Run Locally

### PHP app

```powershell
cd "C:\xampp\htdocs\EMS2\php-app"
C:\xampp\php\php.exe -S localhost:8000 -t public
```

Expected credential keys are listed in `php-app/.env.example`. This scaffold reads from process environment variables, so export them in your shell or web server config before testing live Google, Razorpay, Maps, or Twilio flows.

### XAMPP Apache + MySQL

- Open `http://localhost/EMS2/php-app/public` after starting Apache and MySQL in XAMPP
- The PHP app now loads `php-app/.env` directly, so local DB settings work without extra Apache `SetEnv` directives
- Import `database/schema.sql` and `database/seed_demo.sql` into the default XAMPP MySQL server, then keep `DB_USERNAME=root` and blank `DB_PASSWORD` unless you changed them

### Python AI service

```powershell
cd "C:\Users\ishra\Documents\New project\python-ai"
python -m venv .venv
.venv\Scripts\Activate.ps1
pip install -r requirements.txt
python run.py
```

### Java Booking Validator

```powershell
cd "C:\Users\ishra\Documents\New project\java-booking-validator"
mvn spring-boot:run
```

### Database bootstrap

```powershell
mysql -u root -p < database\schema.sql
mysql -u root -p < database\seed_demo.sql
```

## Implemented Follow-Up

- `GET /auth/google` starts Google OAuth and `GET /auth/google/callback` stores the signed-in user in the PHP session
- Google-authenticated users are upserted into MySQL through the PHP repository layer
- `POST /bookings/deposit/initiate` asks the Spring Boot validator to hold the slot, creates a pending booking row, and then creates a Razorpay order
- Venue discovery and detail pages now read venues, owner phones, slot IDs, pricing, and review aggregates from MySQL instead of a hard-coded PHP array
- `POST /webhooks/razorpay` verifies the `X-Razorpay-Signature` header and audits incoming events to `php-app/storage/integration-events.log`
- Razorpay webhook events are also persisted to `payment_webhook_events`, and successful captures update the `bookings` table
- `payment.captured` webhook events attempt a Twilio SMS to the owner phone number passed in Razorpay `notes.owner_phone`
- Discovery UI now uses a config-driven Google Maps embed instead of a static placeholder
- Checkout view now opens Razorpay Checkout.js automatically when live keys are configured

## Example Razorpay Notes Payload

Attach these values to the payment entity `notes` object so the webhook can notify the correct owner:

```json
{
  "owner_phone": "+919876543210",
  "venue_name": "Lavender Crown Baner",
  "booking_reference": "PEH-2026-0412"
}
```

## Suggested Next Integrations

- Add a Redis-backed expiry worker if you want second-level hold release precision
- Move the PHP venue catalog facade fully to repository-first pagination and filtering once the dataset grows
- Add a webhook retry/reconciliation job so stalled Razorpay events can be replayed safely
"# Vitreon" 
