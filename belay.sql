-- 1. Create the database
CREATE DATABASE belay_portfolio;
USE belay_portfolio;

-- 2. Users table (for yourself or students, optional for login/admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','student','visitor') DEFAULT 'visitor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Contacts / Messages table
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Portfolio projects table
CREATE TABLE portfolio_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255),        -- image filename/path
    project_url VARCHAR(255),  -- optional link to live project
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. YouTube videos table
CREATE TABLE youtube_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Educational notes table
CREATE TABLE education_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('medical','software','grade12','remedial','english') NOT NULL,
    note_title VARCHAR(150) NOT NULL,
    note_content TEXT NOT NULL,
    attachment VARCHAR(255),  -- optional PDF or image
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. CV uploads table
CREATE TABLE cv_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 8. Video uploads table (for uploaded lecture videos, tutorials, etc.)
CREATE TABLE video_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 9. Student contacts table (for logged students or mentorship)
CREATE TABLE student_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    student_email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    related_note_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (related_note_id) REFERENCES education_notes(id) ON DELETE SET NULL
);

-- 10. Social media links table
CREATE TABLE social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50) NOT NULL, -- e.g., facebook, linkedin
    url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 11. File attachments table (general purpose)
CREATE TABLE attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150),
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    type ENUM('pdf','image','video','other') NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
