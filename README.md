# 🎭 Actor Submission System

A **Laravel + Vue 3 + TailwindCSS** project to manage **actor submissions**, validate inputs, and extract structured details using **OpenAI GPT-5**.

---

## 📌 Table of Contents
1. [About the Project](#-about-the-project)  
2. [Features](#-features)  
3. [Tech Stack](#-tech-stack)  
4. [Project Structure](#-project-structure)  
5. [Installation](#-installation)  
6. [Environment Setup](#-environment-setup)  
7. [Database Migration](#-database-migration)  
8. [API Routes](#-api-routes)  
9. [Pages](#-pages)  
10. [AI Integration](#-ai-integration)  
11. [Screenshots](#-screenshots)  
12. [Contributing](#-contributing)  
13. [License](#-license)  

---

## 📖 About the Project
This project provides an **Actor Submission System** where users can:
- Enter email and actor description.  
- Get **real-time validation** (email uniqueness + description keyword checks).  
- Use **OpenAI GPT-5** to extract structured details (first name, last name, address, height, weight, gender, age).  
- Store and display submitted actors in a clean UI.  

---

## ✨ Features
- ✅ Actor submission form (**Vue 3 + TailwindCSS**)  
- ✅ Real-time **email uniqueness check**  
- ✅ Real-time **description validation** (checks keywords)  
- ✅ AI-powered data extraction with **OpenAI GPT-5**  
- ✅ Backend validations using **Laravel Validator**  
- ✅ Actor list table with latest submissions  

---

## 🛠️ Tech Stack
- **Backend:** Laravel 10  
- **Frontend:** Vue 3 (via CDN in Blade) + TailwindCSS  
- **Database:** MySQL  
- **AI:** OpenAI GPT-5 API  
- **HTTP Client:** Laravel HTTP Client  

---

## 📂 Project Structure
/app
/Http/Controllers/ActorController.php # API + logic
/app/Models/Actor.php # Actor model
/resources/views/
submission.blade.php # Vue + Tailwind form
list.blade.php # Actor list
/routes/web.php # Web routes
/routes/api.php # API routes

