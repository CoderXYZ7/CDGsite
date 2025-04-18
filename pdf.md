## 📘 Project Description: Simple PDF Management Web System

This web system is designed to manage and display PDF documents. It includes a basic admin panel for uploading PDF files and a public-facing page for viewing and downloading them. The system should be lightweight and use only HTML, CSS, JavaScript, and PHP—compatible with typical shared hosting environments. No frameworks or databases are required.

---

### 🎯 Features

#### 1. **Admin Upload Page (`admin.php`)**

- A password-protected form (basic PHP password check is enough) where the admin can:
  - Upload a PDF file.
  - The uploaded PDF must be named using the **date format** `YYYY-MM-DD.pdf` (e.g., `2025-04-18.pdf`).
  - Automatically save PDFs in a `pdfs/` folder on the server.
  - Overwrite or reject duplicates based on filename.
  - Display upload success/failure messages.

#### 2. **Public Viewer Page (`index.php`)**

- Embed the **most recent PDF** (determined by filename, sorted in descending date order) at the top of the page using an `<iframe>` or `<embed>`.
- List **all available PDFs** below the embedded viewer with:
  - File names (which are also the dates)
  - Download links
- The page should be visually clean and mobile-friendly.

---

### 🛠 Technologies Required

- **Frontend:** HTML, CSS, JS
- **Backend:** PHP
- **Hosting:** Must be deployable on shared hosting (e.g., cPanel or similar)
- **Security:** Basic password check for admin page (hardcoded is fine)

---

### ✅ Constraints

- No frameworks (like Laravel, React, etc.)
- No database (file-based system only)
- File names must follow the format `YYYY-MM-DD.pdf` and act as both the title and date
- System must be portable and easily deployable on cheap or free hosting
