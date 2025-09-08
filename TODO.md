
# TODO List for the Collaborazione Pastorale Website

This document outlines the identified issues, redundancies, and potential improvements for the website.

## 1. General Improvements

- **Project Structure:**
    - [x] **Unify `index.html`:** There are multiple `index.html` files. Decide on a single entry point for the site and remove the others to avoid confusion. The root `index.html` just redirects to `pages/index.html`, which is unnecessary.
    - [x] **Move `config.php`:** The `config.php` file containing database credentials should be moved outside of the web root directory to prevent it from being accessed directly from the browser.
    - [x] **Remove test files:** The `pages/foglietto/testIndex.php` file seems to be a test file and should be removed from the production environment.
- **Consistency:**
    - [x] **CSS Styling:** There are two CSS files, `styles.css` and `stylesViewer.css`. Consider merging them or at least making sure the styles are consistent and don't override each other unexpectedly.
    - [ ] **JavaScript:** The JavaScript is scattered across multiple files. Consider using a module bundler like Webpack or Parcel to better organize and manage the JavaScript code.
- **Performance:**
    - [ ] **Image Optimization:** Some images are quite large. Consider optimizing them for the web to improve page load times.
    - [ ] **Minify Assets:** Minify CSS and JavaScript files in a production environment to reduce their size.

## 2. Frontend (HTML/CSS/JS)

- **HTML:**
    - [x] **Semantic HTML:** Use more semantic HTML tags where appropriate (e.g., `<header>`, `<footer>`, `<nav>`, `<main>`, `<article>`, `<section>`).
    - [ ] **Hardcoded Content:** Much of the content is hardcoded in the HTML files (e.g., events, team members). Consider moving this data to a separate JSON file or a simple CMS to make it easier to update.
    - [x] **Remove inline styles:** The `home.html` file has a lot of inline CSS in a `<style>` tag. This should be moved to the main `styles.css` file.
- **CSS:**
    - [ ] **CSS Variables:** The use of CSS variables is good. Ensure they are used consistently throughout the stylesheet.
    - [x] **Responsive Design:** The site is responsive, but some elements could be improved on smaller screens. For example, the `foglietto` images in `eventi.html` are too wide on mobile.
- **JavaScript:**
    - [ ] **Modularity:** The JavaScript code could be more modular. The `components.js` file is a good start, but it could be broken down into smaller, more focused modules.
    - [x] **Hardcoded Paths:** The `components.js` file has hardcoded paths to images and other resources. These should be made relative or configurable.
    - [x] **Event Handling:** The `main.js` file has some redundant event listeners. These could be consolidated.

## 3. Backend (PHP/Admin Panel)

- **PHP:**
    - [ ] **Code Organization:** The PHP code is mostly procedural. Consider organizing it into classes and functions to improve readability and maintainability.
    - [ ] **Error Handling:** The error handling could be more robust. For example, in `adminFog.php`, the file upload errors are translated to strings, which is good, but the script could also log the errors for debugging purposes.
    - [ ] **Database Interactions:** Use prepared statements for all database queries to prevent SQL injection vulnerabilities. The `admin.php` file uses them, but other files might not.
- **Admin Panel:**
    - [ ] **User Interface:** The admin panel is functional, but the UI could be improved to be more user-friendly.
    - [ ] **Input Validation:** Add more robust input validation on the server-side to prevent invalid data from being saved to the database.
    - [ ] **File Uploads:** The file upload functionality in `adminFog.php` is a good start, but it could be improved with more security checks, such as checking the file's MIME type in addition to the extension.

## 4. Security

- [x] **`config.php` Location:** As mentioned before, move `config.php` outside the web root. This is a critical security vulnerability.
- **SQL Injection:** Ensure all database queries use prepared statements.
- **Cross-Site Scripting (XSS):** Sanitize all user input before displaying it on the page to prevent XSS attacks. The use of `htmlspecialchars()` is a good practice, but it should be used consistently.
- **CSRF Protection:** The login form has CSRF protection, which is great. Ensure that all forms that perform actions (e.g., adding users, updating settings) have CSRF protection as well.
- **File Uploads:** In `adminFog.php`, consider renaming uploaded files to prevent potential security issues with user-provided filenames. Also, store the uploaded files in a directory that is not directly accessible from the web.

## 5. File Cleanup

- **x] **Redundant `index.html`:** Remove the `index.html` file from the root directory and use the one in `pages/` as the main entry point.
- [x] **Backup Files:** Remove the `.bk` files (`eventi.html.bk`, `index.html.bk`).
- [x] **Test Files:** Remove `pages/foglietto/testIndex.php`.
- **Unused Images:** Check for any unused images in the `static/images/` directory and remove them.
- [x] **`home.html`:** This file seems to be a duplicate of the main page with inline styles. It should be removed and the styles should be moved to the main CSS file.
- [x] **`work.html`:** This page seems to be a placeholder for a "work in progress" page. It can be removed if the site is live.
