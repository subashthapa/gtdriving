# GT Driving Website User Guide

This guide explains how to use the live GT Driving website at [gtdriving.com.au](https://gtdriving.com.au).

## Quick start for the SuperAdmin

1. Open [gtdriving.com.au/login](https://gtdriving.com.au/login).
2. Sign in with `subashthapaa@gmail.com` and the password used during registration.
3. Open the [dashboard](https://gtdriving.com.au/dashboard) to see site totals and upcoming bookings.
4. Use the administration links in the [Admin tools](#admin-tools) section below.
5. Open the profile menu in the top-right corner to update account details, change the password, enable two-factor authentication, or log out.

The account `subashthapaa@gmail.com` is verified and has the `SuperAdmin` role. It can access every administrator route currently available in the website.

## Public website

The home page contains:

- About Us information.
- Driving lesson packages.
- A booking calendar.
- Testimonials.
- Registration and login links.

Visitors do not need to sign in before making a booking.

## Book a driving lesson

1. Open the [home page](https://gtdriving.com.au) and scroll to **Booking Calendar**.
2. Choose an instructor.
3. Select a date in the calendar.
4. Select one of the available time slots.
5. Enter the learner's name, email address, phone number, and any special instructions.
6. Review the displayed lesson fee and select **Confirm Booking**.

Important booking details:

- Payment is cash to the instructor.
- A new booking starts with a **Pending** payment status.
- The website prevents overlapping bookings for the same instructor.
- A signed-in user's saved account information is used in the booking form.
- If a guest uses an existing email address, the booking is attached to that existing account.

## Learner account

After signing in, select the account name in the top navigation and open **Dashboard**.

Learners can:

- See upcoming and past lessons.
- See the assigned instructor, lesson time, instructions, fee, and payment status.
- Edit a future booking's date, time, and instructions.
- Cancel a future unpaid booking.

A past booking cannot be cancelled. A paid booking must be refunded before it can be cancelled.

## Instructor account

The instructor dashboard shows:

- Total earnings and outstanding payments.
- Assigned learners and lesson counts.
- Upcoming lessons.
- Booking history.
- Learner contact details and booking instructions.

To record cash payment, use the payment selector beside a lesson and choose **Paid**. Use **Refunded** if payment has been returned. Earnings are calculated from bookings marked **Paid**.

## Admin dashboard

Open [gtdriving.com.au/dashboard](https://gtdriving.com.au/dashboard).

The dashboard shows:

- Total users.
- Total bookings.
- Total content pages.
- Total packages.
- The next ten upcoming bookings.

## Admin tools

Some admin tools are available by direct URL in the current version of the website.

| Tool | Address | What it does |
| --- | --- | --- |
| Users | [dashboard/admin/users](https://gtdriving.com.au/dashboard/admin/users) | List, create, edit, or delete user accounts. |
| Packages | [admin/packages](https://gtdriving.com.au/admin/packages) | Create, edit, show/hide, or delete lesson packages. |
| Pages | [admin/pages](https://gtdriving.com.au/admin/pages) | Create, edit, or delete website page records. |
| Messages | [admin/messages](https://gtdriving.com.au/admin/messages) | View, create, edit, or delete customer enquiries. |
| Booked sessions | [admin/bookings](https://gtdriving.com.au/admin/bookings) | Filter and update learners, instructors, schedules, instructions, and payment status. |

### Manage users

- Select **Create User** to add a name, email address, and temporary password.
- Select **Edit** to change a user's name or email address.
- Select **Delete** only when the account and its associated access are no longer required.

The current user screen does not assign roles. Role changes must be performed through an approved server administration process.

### Manage packages

- Select **Add Package**.
- Enter the package name, subtitle, price, images, and visibility status.
- Set the status to shown when the package should be available.
- Use **Edit** to update an existing package.
- Use **Delete** carefully; deletion is permanent from the website interface.

### Manage pages

- Select **Add Page**.
- Enter the title, optional subtitle and description, and optional images.
- Use **Edit** to update a page record.
- Use **Delete** only after confirming the page is no longer needed.

### Manage messages

- Open **Messages** to see the latest customer enquiries.
- Select **View** to read the complete enquiry.
- Select **Edit** to correct or update a record.
- Select **Delete** to remove an enquiry.

## Profile and security

Open the account menu and select **Profile**. From there:

- Update the account name, email address, and profile photo.
- Change the password.
- Enable two-factor authentication and save the recovery codes securely.
- Review and log out other browser sessions.

New accounts must verify their email address before opening the dashboard or any administration screen. If the message does not arrive, use **Resend Verification Email** on the verification screen.

Passwords must contain at least 12 characters with upper and lower case letters, a number, and a symbol.

Use a unique password and enable two-factor authentication for every administrator account. Scan the QR code with an authenticator app, confirm the one-time code, and store the recovery codes in a password manager. Do not share administrator credentials.

## Troubleshooting

- **An admin page returns 403:** confirm the signed-in account has the `Admin` or `SuperAdmin` role.
- **The dashboard asks for email verification:** complete email verification or ask an authorised server administrator to verify the account.
- **A time slot is unavailable:** another booking already uses that instructor and time; choose another slot.
- **A booking cannot be cancelled:** past bookings cannot be cancelled, and paid bookings must be refunded first.
- **Changes do not appear:** refresh the page and sign in again if the session has expired.
- **Password reset email does not arrive:** contact the site operator; production email delivery may require server mail configuration.
- **Verification email does not arrive:** check spam, confirm the address is correct, then select **Resend Verification Email**. Contact the site operator if it still does not arrive.

## Safe administration checklist

- Review the target record before editing or deleting it.
- Keep at least one working `SuperAdmin` account.
- Back up the production database before bulk or high-risk changes.
- Never place passwords, recovery codes, or server credentials in repository files.
