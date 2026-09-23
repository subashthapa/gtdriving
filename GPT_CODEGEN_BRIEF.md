# GT Driving - GPT Full Project Code Generation Brief

Use this document to ask GPT (or any coding LLM) to generate the full project codebase for GT Driving.

## 1. Project Goal
Build a production-ready driving school web app with:
- Public marketing pages
- Booking calendar and time slots
- Learner booking flow (guest + authenticated)
- Instructor and learner dashboards
- Admin panel for users/pages/packages/messages
- Role-based access control
- Seed data and tests

## 2. Required Tech Stack
- Backend: Laravel 11 (PHP 8.2+)
- Frontend: Inertia.js + Vue 3 + Vite
- Auth: Laravel Jetstream + Fortify + Sanctum
- RBAC: spatie/laravel-permission
- Styling: Tailwind CSS
- Calendar: FullCalendar Vue 3 (or Vue Cal where needed)
- DB: SQLite for local dev (schema should be DB-agnostic)

## 3. User Roles
- SuperAdmin
- Admin
- Instructor
- Learner

## 4. Functional Requirements

### 4.1 Public Site
- Home page with sections: hero, packages, about, booking feature block, testimonials, contact.
- Contact form (`/messages`) to store messages.

### 4.2 Booking
- Day click in calendar loads available slots from `timeslots` table.
- Available slots endpoint supports filtering by date and instructor.
- Booking flow supports:
  - Existing authenticated learner
  - Guest learner with existing email (reuse existing user)
  - New guest learner (create user, assign `Learner` role)
- Prevent double-booking for same instructor/date/start_time.
- Booking create endpoint returns JSON success/error.

### 4.3 Instructor Features
Instructor dashboard must include:
- Assigned learners list (unique learners with lesson counts)
- Past lesson history
- Future/planned calendar list
- Total earnings (booking duration × hourly rate)
- Counts: total lessons, past lessons, upcoming lessons, total learners

### 4.4 Learner Features
Learner dashboard must include:
- Upcoming bookings
- Past bookings
- Edit/delete own bookings only

### 4.5 Admin Features
- Admin dashboard stats (users, bookings, pages, packages, upcoming bookings)
- CRUD resources:
  - Users
  - Packages
  - Pages
  - Messages

### 4.6 Authentication & Session Reliability
- Login/logout/register/reset with Jetstream/Fortify.
- Eliminate 419 issues:
  - Ensure CSRF token meta exists in root blade
  - Axios sends CSRF header
  - If custom modal login is used, call `/sanctum/csrf-cookie` before POST `/login`
  - Avoid stale token after login; reload page/session state correctly

## 5. Data Model Requirements

### users
- id, name, email, password, phone, income (decimal 10,2 default 0), timestamps
- Include Jetstream/Fortify fields as needed

### bookings
- id
- user_id (nullable)
- instructor (int user id)
- start_date, end_date (date)
- start_time, end_time (time)
- approved_by (nullable)
- instructions (nullable text/string)
- timestamps

### timeslots
- id
- start_time, end_time
- is_visible boolean default false/true per requirement
- timestamps

### packages
- package_name, subtitle, price, image, thumbnail, description, status, added_by, timestamps

### pages
- title, subtitle, description, image, thumbnail, added_by, timestamps

### messages
- name, phone, email, session_type, message, timestamps

## 6. Routing Requirements

### Web routes
- `GET /` -> home
- `POST /messages` -> store public contact message
- `POST /book` -> create booking (public)
- Authenticated/verified:
  - `GET /dashboard`
  - `GET /bookings`
  - `PUT /bookings/{booking}`
  - `DELETE /bookings/{booking}`
- Admin-only:
  - `/admin/packages` resource
  - `/admin/pages` resource
  - `/admin/messages` resource
  - `/dashboard/admin/users` resource

### API routes
- `GET /api/available-slots?date=YYYY-MM-DD&instructor={id}`
- `GET /api/booked-dates`
- `GET /api/get-time-slots`
- `GET /api/instructors`
- `GET /api/instructors/{id}/bookings`
- Admin-only: `GET /api/admin/stats`

## 7. Seeding Requirements
Create seeders for:
- Roles and permissions
- Users (at least: superadmin, admin, 2 instructors, 3 learners)
- Timeslots (e.g., 30-minute intervals)
- Packages
- Bookings with mix of past/future for instructors
- User income calculation from bookings for instructors

Income formula:
- `minutes = diff(start_time, end_time)`
- `income = (minutes / 60) * hourly_rate`
- Hourly rate from config: `services.instructor.hourly_rate` (default 60)

## 8. Testing Requirements
Use PHPUnit feature tests to verify:
- Public home route and contact message submission
- Available-slots endpoint behavior and filtering
- Guest booking + new learner creation
- Existing learner booking without duplicate user creation
- Booking conflict prevention
- Booking owner authorization (update/delete)
- Instructor bookings visibility
- Instructor dashboard data payload (learners/history/upcoming/earnings)
- Authentication login/logout flow (no 419 regressions)

## 9. Non-Functional Requirements
- Clean controller/service structure
- Form request validation
- Role middleware enforcement
- Avoid debug code (`dd`, `dump`)
- Consistent route names
- Defensive error handling for API and UI
- Mobile-responsive pages

## 10. Output Format for GPT (Important)
When generating code, GPT should:
1. Show a migration/file plan first.
2. Generate complete files with exact paths.
3. Include all migrations/controllers/models/routes/vue pages/tests/seeders.
4. Ensure code is internally consistent (method names, route names, props, imports).
5. End with commands to run:
   - `composer install`
   - `npm install`
   - `php artisan migrate:fresh --seed`
   - `npm run build`
   - `./vendor/bin/phpunit`

---

## Copy/Paste Prompt You Can Use with GPT

You are a senior Laravel + Inertia/Vue engineer. Build the full GT Driving application from scratch with production-grade code.

Use these hard requirements:
- Laravel 11, PHP 8.2, Inertia + Vue 3, Jetstream/Fortify/Sanctum, Spatie roles.
- Roles: SuperAdmin, Admin, Instructor, Learner.
- Public booking flow must support guest + existing learners and prevent duplicate slot booking.
- Dashboard must support learner, instructor, and admin experiences.
- Instructor dashboard must include assigned learners, booking history, future/planned lessons, and total earnings.
- Add seeders for realistic users/timeslots/bookings and compute instructor incomes.
- Include complete feature tests for routes, booking flow, auth/logout, and dashboard data.
- Prevent 419 issues (CSRF/session handling for login/logout and custom auth modal flows).

Generate complete code files with exact paths, not snippets. Keep naming consistent. Do not leave TODOs. Do not use debug statements. Ensure all tests pass.

Also provide the final run commands and expected outputs.
