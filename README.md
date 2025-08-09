# ByWay - Online Learning Management System

## Overview
ByWay is a comprehensive online learning management system inspired by platforms like Udemy. It provides a seamless experience for learners, instructors, and administrators through a robust set of features, including course browsing, payment processing, instructor revenue analytics, and an admin dashboard for platform management. The system is built with a focus on user authentication, course management, and secure payment integration, ensuring a scalable and user-friendly experience.

## Project Goal
The goal of ByWay is to create an interactive and efficient e-learning platform that supports:
- Learners in discovering, enrolling, and engaging with courses.
- Instructors in creating, managing, and monetizing their courses.
- Administrators in overseeing platform operations, user management, and financial transactions.

The project was developed by a team of four trainees (Mohamed, Ola, Mennah, Asmaa) over two weeks, with tasks distributed to cover authentication, learner features, instructor features, and the admin dashboard.

## Features

### Authentication
- **User Registration**: Single endpoint for learners and instructors to create accounts, with email verification and social login (at least one of Facebook, Google, or Microsoft).
- **Login**: Unified endpoint for learners, instructors, and admins.
- **Forgot Password**: Password reset via email with a secure, time-limited link.
- **Logout**: Secure session termination.

### Learner Features
- **Profile Management**: Update personal information (refer to Figma design).
- **Enrolled Courses**: View and track progress in enrolled courses.
- **Course Browsing**: Smart search and filtering for courses and instructors.
- **Favorites & Cart**: Add courses to favorites or cart for purchase.
- **Course Details**: View detailed course information, including reviews (only enrolled learners can review).
- **Video Playback**: Watch course videos and mark them as completed.
- **Payment**: Purchase courses or cart items using Fawry, E-Wallet, or Credit/Debit Card, with email and in-app notifications.
- **Payment History**: View transaction history.
- **Payment Methods**: Add and manage payment methods.
- **Notifications**: View and manage in-app notifications.
- **Account Closure**: Option to permanently close an account.

### Instructor Features
- **Profile Management**: Update profile details, including social media links (refer to Figma design).
- **Course Management**: Create, edit, delete, search, and filter courses.
- **Course Details**: View detailed course information, including enrolled students and reviews.
- **Reviews**: View all reviews for their courses.
- **Revenue Analytics**: View total profits, available balance, last transaction, and performance charts.
- **Withdrawal Requests**: Request withdrawals of available balance (refer to Figma design).
- **Notifications**: View and manage in-app notifications.

### Admin Dashboard
- **Overview**: Displays charts and metrics for active learners, instructors, published courses, total revenue, top-rated courses, and recent payout requests.
- **User Management**: Manage learner accounts (view, edit, delete, or suspend).
- **Instructor Management**: Add, view, edit, or suspend instructor accounts.
- **Course Management**: Approve, edit, or delete courses.
- **Payments & Revenue**: Monitor transactions and manage payout requests.
- **Reviews & Ratings**: Manage course reviews, with the ability to remove inappropriate ones.
- **Platform Settings**: Configure categories, payment policies, and notification settings.
- **Reports & Analytics**: Generate detailed reports on platform performance, user growth, and revenue.

## Project Structure
The project is divided into three main modules, developed over two weeks:
    - **Authentication**: Handled by Mennah, covering registration, login, password reset, and logout.
    - **Learner Features**: Split between Mennah (core features like profile, course browsing, and notifications) and Mohamed (payment-related features).
    - **Instructor Features**: Split between Asmaa (core features like profile and course management) and Mohamed (revenue and withdrawal features).
    - **Admin Dashboard**: Handled by Ola, covering all admin-related functionalities.

### Task Distribution
- **Mennah**: Authentication, learner profile, enrolled courses, course browsing, favorites/cart, course details, video playback, and learner notifications.
- **Mohamed**: Payment history, payment processing, payment methods, instructor revenue analytics, withdrawal requests, and instructor notifications.
- **Asmaa**: Instructor profile, course creation, course management, course details, instructor reviews.
- **Ola**: Admin dashboard, including overview, user management, instructor management, course management, payments, reviews, platform settings, and analytics.

## Technologies Used
- **Backend**: Laravel
- **Database**: MySql
- **Payment Integration**: Support for Fawry, E-Wallet, and Credit/Debit Card (mock or real APIs).
- **Authentication**: JWT , email verification, and social login.
- **Design**: Figma for UI/UX reference.

## Contributors
- **Mohamed**: Payment and revenue-related features.
- **Ola**: Admin dashboard.
- **Mennah**: Authentication and core learner features.
- **Asmaa**: Instructor features.

## License
[Specify license, e.g., MIT License]
