# TicketFlow – Ticket Management Web App

A fully responsive, accessible ticket management app built with Twig and plain CSS.

## Features

- Landing page with hero, features, and call-to-action
- Authentication (Login/Signup) with form validation and mock credentials
- Dashboard with ticket summary cards
- Full ticket CRUD (Create, Read, Update, Delete)
- Toast notifications for feedback
- Consistent Navbar and Footer
- Responsive, accessible, and semantic UI

## Folder Structure

```
  /templates        # Landing, AuthLogin, AuthSignup, Dashboard, Tickets, Navbar, Footer, TicketCard, Form, Toast
  /public
     /index.php
     /assets
        /styles     # CSS files for layout and components
```

## Setup

1. Install dependencies:
   ```bash
   composer install
   ```
2. Start the PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
3. Open [https://localhost:8000/](https://localhost:8000/) in your browser.

## Mock Credentials

- **Login:** `demo@ticketflow.com`
- **Password:** `demo123`

## Accessibility & Responsiveness

- Semantic HTML, focus states, color contrast
- Mobile-first, grid and stacked layouts

---
