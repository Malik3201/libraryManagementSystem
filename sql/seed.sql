-- Purpose: Seed data for Library Management System (users, books, borrow_records)

USE library_db;

-- Users (passwords are plaintext placeholders; real app will hash on insert/update)
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin One', 'admin1@example.com', 'Password123', 'admin'),
('Faculty One', 'faculty1@example.com', 'Password123', 'faculty'),
('Faculty Two', 'faculty2@example.com', 'Password123', 'faculty'),
('Student One', 'student1@example.com', 'Password123', 'student'),
('Student Two', 'student2@example.com', 'Password123', 'student'),
('Student Three', 'student3@example.com', 'Password123', 'student');

-- Books
INSERT INTO books (title, author, category, cover_url, availability) VALUES
('Clean Code', 'Robert C. Martin', 'Software Engineering', 'uploads/covers/clean_code.jpg', 1),
('The Pragmatic Programmer', 'Andrew Hunt; David Thomas', 'Software Engineering', 'uploads/covers/pragmatic_programmer.jpg', 1),
('Introduction to Algorithms', 'Thomas H. Cormen', 'Computer Science', 'uploads/covers/clrs.jpg', 1),
('Design Patterns', 'Erich Gamma; Richard Helm; Ralph Johnson; John Vlissides', 'Computer Science', 'uploads/covers/design_patterns.jpg', 1),
('Artificial Intelligence: A Modern Approach', 'Stuart Russell; Peter Norvig', 'Artificial Intelligence', 'uploads/covers/aima.jpg', 1),
('Deep Learning', 'Ian Goodfellow; Yoshua Bengio; Aaron Courville', 'Artificial Intelligence', 'uploads/covers/deep_learning.jpg', 0),
('Database System Concepts', 'Abraham Silberschatz', 'Databases', 'uploads/covers/db_concepts.jpg', 1),
('Operating System Concepts', 'Abraham Silberschatz', 'Operating Systems', 'uploads/covers/os_concepts.jpg', 1),
('Computer Networks', 'Andrew S. Tanenbaum', 'Networking', 'uploads/covers/computer_networks.jpg', 1),
('Modern Operating Systems', 'Andrew S. Tanenbaum', 'Operating Systems', 'uploads/covers/modern_os.jpg', 0),
('Structure and Interpretation of Computer Programs', 'Harold Abelson; Gerald Jay Sussman', 'Computer Science', 'uploads/covers/sicp.jpg', 1),
('Clean Architecture', 'Robert C. Martin', 'Software Engineering', 'uploads/covers/clean_architecture.jpg', 1),
('Refactoring', 'Martin Fowler', 'Software Engineering', 'uploads/covers/refactoring.jpg', 1);

-- Borrow Records (mix of borrowed, returned, overdue)
-- Assume user_ids 1..6 and book_ids 1..13 from the inserts above
INSERT INTO borrow_records (user_id, book_id, borrow_date, return_date, status) VALUES
(4, 1, '2025-09-01', '2025-09-10', 'returned'),
(5, 2, '2025-09-05', NULL, 'borrowed'),
(6, 6, '2025-08-15', NULL, 'overdue'),
(2, 4, '2025-09-12', '2025-09-20', 'returned'),
(3, 10, '2025-09-18', NULL, 'borrowed');


