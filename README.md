# Yedire Frewoch Project 📚

## Overview 🌟
Yedire Frewoch is a comprehensive web application developed for the Yedire Frewoch organization. It provides a platform for managing educational resources, fostering community engagement, and supporting organizational activities.

## Features ✨

### Resource Management 📋
- Digital library of educational materials  
- Document categorization and search functionality  
- Version control for resources  

### User Management 👥
- Role-based access control  
- User profiles and authentication  
- Member directory  

### Community Engagement 🤝
- Discussion forums  
- Event calendar and registration  
- Announcement system  

### Content Management 📝
- Blog posts and articles  
- Media gallery  
- News updates  

---

## Access Points 📋
- **Admin Dashboard**: Access administrative functions at `/admin`.
    # Dashboard Module 📊

## Overview 🌟
The `dashboard.php` file is a core component of the Yedire Frewoch project. It serves as the backend logic for managing and displaying key features of the dashboard, such as school images, urgent messages, and other administrative functionalities. This module ensures smooth interaction with the database and provides fallback mechanisms for missing resources.

---

## Features ✨

### 1. Image Management 🖼️
- **Dynamic Image Loading**: Displays images for schools with a fallback placeholder if the image is missing.
- **Database Integration**: Automatically creates the `school_images` table if it doesn't exist.
- **Image Metadata**: Stores additional information such as title, description, and upload date.
- **Featured Images**: Supports marking images as featured for special emphasis.

### 2. Urgent Messages 📢
- **Table Creation**: Ensures the `urgent_messages` table exists in the database.
- **Message Management**: Handles urgent announcements for schools or users (implementation details can be extended).

### 3. Database Connection 🔗
- **MySQL Integration**: Connects to the `yedire_frewoch` database using MySQLi.
- **Error Handling**: Terminates execution with an error message if the connection fails.

---
## How It Works ⚙️

1. **Database Connection**:
   - Connects to the MySQL database using the credentials provided in the script.
   - Terminates execution if the connection fails.

2. **Table Creation**:
   - Checks if the `school_images` and `urgent_messages` tables exist. If not, it creates them with the required schema.

3. **Image Fallback**:
   - Uses the `get_image_path()` helper function to check if an image exists.
   - If the image is missing, it returns a placeholder image located at `images/placeholder.jpg`.

4. **Urgent Messages**:
   - Handles urgent announcements for schools or users, ensuring they are stored in the database.

---

## Prerequisites 🛠️

- **PHP**: Version 7.4 or higher.
- **MySQL**: A running MySQL server with a database named `yedire_frewoch`.
- **Web Server**: Apache or Nginx configured to serve PHP files.

---

## Setup Instructions 🚀

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/CRXNCM/Yedire_frewoch.git
   cd Yedire_frewoch
   ```
2. **Configure the Database**:

- Ensure the MySQL server is running.
- Create a database named `yedire_frewoch`:
```bash
    CREATE DATABASE yedire_frewoch;
```
3. Update the database credentials in `dashboard.php`
```bash
    <?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'yedire_frewoch';
```
4. **Run the Application**:

- Serve the project using a local server (e.g., XAMPP, WAMP, or PHP's built-in server):
```bash
    php -S localhost:8000
```

## About Yedire Frewoch Organization 🏢 
We are Ye Dire Firewoch Charity Association (YDFCA): 
- a registered non-profit organization in Ethiopia. Our mission is to improve education by providing daily meals to children in primary schools.
- a local Ethiopian team committed to improving education through school feeding programs. Since our founding in 2010, 
- we've grown to serve daily meals to over 1,500 children in 18 primary schools across Dire Dawa.
- Our work focuses on keeping children in school, especially girls, by addressing the root cause of classroom hunger. 
- We believe every child deserves the opportunity to learn without the distraction of an empty stomach.

**Our mission is to empower individuals through education and create a collaborative learning environment that benefits the wider community.**

---

## License 📄
This project is licensed under the [MIT License](LICENSE).

---

## Contact 📞
For questions or support, please contact us at:  
**Made with ❤️ by the American Corner Dev Team**