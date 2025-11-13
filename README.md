# AuthBoard — Mini Social Web App 

AuthBoard is a lightweight social platform built in PHP and MySQL — designed for web development practice labs.  
It includes user authentication, post creation with optional images, profile sessions, and the ability to **edit posts within 24 hours**.

---

## 🚀 Features

### 🧑‍💻 Authentication
- User Registration with form validation  
- Secure Login using password hashing (`password_hash`, `password_verify`)  
- Session-based authentication and logout  
- Basic input sanitization and security  

### 📝 Posts & Feed
- Create text-based posts from the dashboard  
- Optional image upload (up to 4MB)  
- View all posts in a shared feed (all users visible)  
- Each post shows author name, email, and creation time  
- Responsive Tailwind UI for modern look and feel  

### ✏️ Edit Post (New!)
- Users can edit their **own posts** within 24 hours of posting  
- Edited posts show an “(edited)” indicator  
- Validation for ownership and time window  
- Simple edit form pre-filled with existing content  

### 🖼️ Image Upload
- Supports PNG, JPG, GIF, WEBP uploads  
- Uploaded images stored in `/public/uploads`  
- Displayed inline in feed with responsive design  

### 🎨 UI / UX
- Clean modern Tailwind CSS design  
- Responsive layout for mobile and desktop  
- Dynamic image preview before upload  
- Fade-in animations and card-style UI
### ✨ Usage
- Register or login.
- Create a new post with or without image.
- View posts from all users in the dashboard feed.
- If you’re the author of a post (and it’s <24 hours old), an Edit button will appear.
- Click Edit → change content → save → post updates and shows “edited”.
### 👨‍💻 Author
- Developed by: Tamim Amin
- Course: Web Practice Lab / PHP & MySQL

