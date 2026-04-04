# Web4All — Internship Management Platform

> A full-stack PHP web application built from scratch for managing internship offers, companies, student applications, and promotion tracking — developed as a team project at CESI engineering school.

---

## Context

Finding an internship is one of the most time-consuming steps in a student's academic journey. Web4All was designed to solve this by centralizing internship offers, company profiles, and student applications into a single platform — replacing scattered spreadsheets, LinkedIn searches, and email chains.

The project was carried out over a full academic block by a group of 4 students, following Scrum methodology with regular sprints, daily standups, and backlog management. The final deliverable was presented as a live technical demo to a jury acting as the client (CESI).

---

## Architecture

The application follows a strict **MVC (Model-View-Controller)** architecture built entirely from scratch — no framework, no CMS.

```
/
├── config/
│   └── routes.php       # Server-side routing
├── public/              # Entry point (index.php), static assets (CSS, JS, images)
│   └── assets/          # CSS, images & audio
├── src/
│   ├── Controllers/     # One controller per feature (AccountController, OffersController, etc.)
│   ├── Models/          # PDO-based models (Annonces, Candidatures, Entreprises, Note, etc.)
│   ├── Core/            # Base Controller class, routing logic
│   ├── Tests/           # PHPUnit unit tests
│   └── Database.php     # PDO connection factory
│   └── Pagination.php   # Server-side pagination
├── templates/
│   └── partials/        # Twig HTML base template & navbar
│   └── pages/           # Twig HTML templates
├── .env                 # Environment variables (DB credentials, Supabase keys)
└── composer.json
```

### Routing

A custom URL router maps readable routes to controller actions:

```
/?page=offres              → OffersController::index()
/?page=detail-annonce&id=  → OfferDetailsController::index()
/?page=postuler&id=        → ApplyController::index()
...
```

All routing is handled server-side in PHP, keeping URLs clean and consistent.

### Template Engine

All views are rendered through **Twig**, used as a strict separation layer between logic and presentation. Twig handles:
- Layout inheritance (`base.twig.html` extended by every page)
- Partial includes (header, footer, search bar)
- Conditional rendering based on user role
- Automatic HTML escaping, preventing XSS by default

---

## Features

### Role-Based Access Control

Three distinct user roles with differentiated permissions:

| Feature | Student | Pilote | Admin |
|---|---|---|---|
| Browse offers & companies | ✅ | ✅ | ✅ |
| Apply to offers | ✅ | ❌ | ❌ |
| Manage wishlist | ✅ | ❌ | ❌ |
| Rate companies | ✅ | ❌ | ❌ |
| Manage students | ❌ | ✅ | ✅ |
| Create / edit offers | ❌ | ✅ | ✅ |
| Create / edit companies | ❌ | ✅ | ✅ |
| Manage pilote accounts | ❌ | ❌ | ✅ |
| Access full platform | ❌ | ❌ | ✅ |

### Internship Offers

- Full CRUD (create, read, update, delete) for offers
- Search and filter by keyword, skills, duration, company
- Offer detail page with company info, tags, salary, and application count
- Paginated listing with real-time total offer count
- Statistics dashboard: top wishlisted offers, offers by duration, average applications per offer

### Company Management

- Company profiles with contact details, ratings, and review history
- Student-submitted ratings (1–5 stars) with comments
- Average rating and review count displayed live
- Search by name with results pagination

### Applications (Candidatures)

- Students apply with a CV and cover letter (PDF upload via Supabase Storage)
- File validation: MIME type check (`application/pdf`), 2MB size limit
- Each student can only apply once per offer (duplicate prevention at DB and PHP level)
- Pilotes can browse all applications from their assigned students
- Students can review their own submitted applications

### Wishlist

- Add / remove offers from personal wishlist
- Toggle via async JavaScript (no page reload)
- Wishlist count visible on each offer card and detail page

### Account Management

- Secure registration and login with `password_hash()` / `password_verify()`
- Profile editing (name, email, public profile)
- Pilotes manage their own student roster
- Admins manage all accounts across all roles

### Pagination

All listing pages (offers, companies, students, applications) implement server-side pagination to handle large datasets efficiently.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Language | PHP 8 (OOP, PSR-12) |
| Template engine | Twig |
| Database | PostgreSQL (via PDO) |
| File storage | Supabase Storage (PDF upload via cURL) |
| Environment | vlucas/phpdotenv |
| Testing | PHPUnit |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Fonts | Bootstrap Icons, custom CSS variables |
| Version control | Git / GitHub |

---

## Security

Security was a core requirement of the project specifications and was implemented at every layer of the stack.

### Password Hashing

User passwords are never stored in plain text. PHP's `password_hash()` with `PASSWORD_DEFAULT` (bcrypt) is used on registration, and a migration script was written to hash any previously plain-text passwords in the database.

```php
$hash = password_hash($password, PASSWORD_DEFAULT);
password_verify($input, $hash); // login check
```

### SQL Injection Prevention

Every database query uses **PDO prepared statements** with bound parameters. Raw string interpolation in SQL queries is strictly avoided throughout the codebase.

```php
$stmt = $pdo->prepare('SELECT * FROM annonce WHERE id_annonce = :id');
$stmt->execute([':id' => $annonceId]);
```

### XSS Prevention

Twig automatically escapes all rendered variables using `{{ variable }}`. The `| raw` filter is never applied to user-supplied content, ensuring that injected scripts are always rendered as plain text.

### CSRF Awareness

Forms are protected against cross-site request forgery through session validation and by ensuring state-changing actions require a POST request with an authenticated session.

### Role Enforcement

Access control is enforced server-side on every controller method, not just via frontend visibility. Unauthorized access attempts redirect to safe pages or return 403 errors:

```php
protected function requireRole(string $role): void
{
    if (($_SESSION['user_role'] ?? '') !== $role) {
        http_response_code(403);
        $this->render('pages/erreur.twig.html', ['code' => 403]);
        exit;
    }
}
```

### File Upload Security

Uploaded CVs and cover letters are validated before storage:
- MIME type verified using PHP's `finfo` extension (not just file extension)
- File size capped at 2MB
- Files are stored on Supabase with randomized filenames (`uniqid()`)
- Direct file access is handled via Supabase's public or signed URL system

### Environment Variables

All sensitive credentials (database host/user/password, Supabase URL and service key) are stored in a `.env` file loaded via `vlucas/phpdotenv`. The `.env` file is excluded from version control via `.gitignore`.

---

## SEO

The project specifications required adherence to basic SEO best practices, which were implemented across all pages.

- **Semantic HTML5**: proper use of `<main>`, `<header>`, `<nav>`, `<section>`, `<article>`, `<footer>` throughout
- **Title tags**: unique, descriptive `<title>` per page injected via Twig block inheritance
- **Meta descriptions**: relevant `<meta name="description">` tags on all public-facing pages
- **Heading hierarchy**: single `<h1>` per page, logical `<h2>`/`<h3>` structure
- **Image alt attributes**: all images include descriptive `alt` text
- **Readable URLs**: route names are human-readable and keyword-relevant (e.g., `?page=offres-de-stage`)
- **sitemap.xml**: submitted to facilitate search engine crawling
- **robots.txt**: configured to guide indexers and protect private routes
- **Performance**: lazy-loaded images, minimal render-blocking resources, page load targets under 3 seconds

---

## Testing

Unit tests are written with **PHPUnit** and cover the `RateCompanyController`. Tests are isolated from the production PostgreSQL database using an **in-memory SQLite database**, with SQLite custom functions registered to replicate PostgreSQL behavior (e.g., `NOW()`).

The testing strategy uses anonymous class mocks that extend real controllers, overriding the `render()` method to capture output without requiring a full HTTP response cycle. This approach avoids heavy mocking frameworks while keeping tests readable and close to the real implementation.

```
Tests: 3, Assertions: 3 ✅
```

Test cases cover:
- Unauthenticated POST requests (`notLoggedIn` flag)
- Valid rating submission (`success` flag)
- Invalid rating out of range (`error` flag)

---

## What This Project Taught

This project was a comprehensive introduction to professional web development practices, covering every layer of a production application.

**Architecture & Backend**
- Designing and implementing a custom MVC architecture from scratch reinforced a deep understanding of separation of concerns, dependency flow, and the role each layer plays — without the abstraction of a framework hiding the details.
- Writing a custom URL router clarified how frameworks like Laravel or Symfony handle routing internally.
- Using PDO and prepared statements built strong habits around database interaction safety.

**Security**
- Implementing password hashing, role-based access control, XSS protection, and SQL injection prevention made security feel like a design constraint rather than an afterthought. Each vulnerability class was understood by solving it directly in code.

**File Handling & External APIs**
- Integrating Supabase Storage via raw cURL requests (without an SDK) gave hands-on experience with HTTP headers, authorization tokens, MIME validation, and error handling for external services.

**Templating & Frontend**
- Twig's inheritance model (`extends`/`block`) demonstrated how large UIs can be composed from small, reusable parts — a concept that maps directly to modern component-based frameworks.
- Managing asynchronous UI updates (wishlist toggle, like counter) without a frontend framework deepened understanding of the Fetch API and DOM manipulation.

**Testing**
- Writing PHPUnit tests against a controller that depends on a database required solving real problems: how to isolate external dependencies, how to mock just enough without over-engineering, and how to structure tests so they reflect actual user behavior.

**Project Management**
- Running a 4-person Scrum project over several weeks — with backlogs, sprint planning, and a client-facing demo — provided practical experience with collaborative development workflows, code review, and task decomposition.

**SEO & Standards**
- Implementing SEO requirements (meta tags, sitemap, robots.txt, semantic HTML, heading hierarchy) turned abstract best practices into concrete, measurable decisions made during development.

---

## Project Scope

This project was developed as part of an academic block at CESI engineering school, simulating a real client/agency relationship. The team acted as the agency (Web4All), and the jury served as the client during the final technical demonstration.

---

## License

This project was created as part of an academic assignment for the OOP Project at CESI. All rights reserved by the authors.
