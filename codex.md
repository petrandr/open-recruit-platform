## Instructions for Laravel + Orchid Recruitment App

### Context:
You are developing a Recruitment Application using Laravel and Orchid for the admin interface. The application allows users to create and manage job positions, applicants, and their respective applications. Database relationships are defined using Laravel migrations.

### Framework & Guidelines:
- **Framework:** Laravel 12 with Orchid platform for the admin panel.
- **ORM:** Use Laravel's Eloquent ORM for database interactions.
- **Coding Standards:** Adhere strictly to PSR standards and Laravel conventions.
- **Admin UI:** Use Orchid Screens and Layouts for admin interfaces, following Orchid best practices (screens, layouts, fields, actions).
- **Migrations & Models:** Ensure database interactions align with existing migrations and relationships.
- **External Packages:** You are not allowed to modify any file within the vendors dir.

### Application Features:
- **Job Management:**
    - Manage job listings: create, view, edit, delete.
    - Include fields: title, description, location, type, department, status, posted_date, expiry_date.

- **Applicant Management:**
    - Manage applicants: create, view, edit, delete.
    - Fields: personal details, resume, cover letter, LinkedIn profile.

- **Application Tracking:**
    - Track applications linking applicants to jobs.
    - Application statuses: submitted, reviewed, rejected, accepted.

- **Custom Questions:**
    - Create and manage custom questions per job.
    - Store and manage applicant answers.

- **UTM Tracking:**
    - Capture and manage UTM parameters from marketing campaigns.
