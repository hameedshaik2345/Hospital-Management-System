# Software Requirements Specification (SRS) - MedFlow

## 1. Introduction
### 1.1 Purpose
The purpose of this document is to provide a detailed overview of the software requirements for the **MedFlow** Appointment Booking System. It defines the functional and non-functional requirements, system architecture, and user roles.

### 1.2 Scope
MedFlow is a web-based healthcare management platform designed to streamline doctor-patient interactions. Its primary scope includes online appointment booking, real-time token management, automated notifications (Push/SMS/Email), and medical record management.

---

## 2. Overall Description
### 2.1 User Roles
- **Patient**: Can search doctors, book appointments, track live tokens, and pay bills.
- **Doctor**: Can manage their daily schedule, update live status, and generate prescriptions.
- **Admin**: Has full system control, manages users, and handles offline (walk-in) bookings.
- **Pharmacist**: Manages prescription-based billing.

### 2.2 System Architecture
- **Framework**: Laravel 11.x (PHP 8.2+)
- **Frontend**: Blade Templating Engine, Vanilla Javascript, Bootstrap 5.
- **Database**: MySQL / SQLite.
- **Payment Gateway**: Razorpay API.
- **Notifications**: 
    - Web Push (VAPID / OpenSSL)
    - SMS (Fast2SMS API)
    - Email (SMTP / Laravel Mail)

---

## 3. Functional Requirements

### 3.1 Patient Management
- **FR_P1**: Secure registration and login.
- **FR_P2**: Mandatory phone verification via OTP before booking.
- **FR_P3**: Search doctors based on name, specialty, or distance (Geo-location).
- **FR_P4**: 4-Step booking process (Info -> Doctor -> Time/Token -> Payment).
- **FR_P5**: Real-time view of "Currently Serving" token for booked appointments.

### 3.2 Doctor Management
- **FR_D1**: Private dashboard with daily schedule visualization.
- **FR_D2**: One-click token increment feature with automated patient broadcasting.
- **FR_D3**: Live status toggling (Available, Busy, Break).
- **FR_D4**: Digital prescription creation and history tracking.

### 3.3 Admin & Staff Features
- **FR_A1**: User CRUD (Manage patients, doctors, and staff).
- **FR_A2**: Walk-in booking system for unregistered or offline patients.
- **FR_A3**: PDF generation for appointment confirmations (with print capability).
- **FR_A4**: System-wide analytics and reporting.

---

## 4. Non-Functional Requirements

### 4.1 Security
- **NFR_S1**: Data encryption at rest and in transit.
- **NFR_S2**: CSRF protection on all forms.
- **NFR_S3**: Role-based access control (RBAC) via Laravel Middlewares.
- **NFR_S4**: VAPID authentication for secure browser push notifications.

### 4.2 Performance & Reliability
- **NFR_P1**: Real-time token updates should deliver within 2 seconds of the doctor's action.
- **NFR_P2**: Scalability to handle up to 100 concurrent token status requests per doctor.
- **NFR_P3**: PDF generation response time under 3 seconds.

### 4.3 Usability
- **NFR_U1**: Mobile-first responsive design using modern CSS standards.
- **NFR_U2**: Minimal learning curve with intuitive dashboard layouts.

---

## 5. Technical Specifications

### 5.1 Database Schema Highlights
- **Users**: Auth details and roles.
- **Doctor_Profiles**: Specialty, hospital name, current token, and live status.
- **Appointments**: token_number, appointment_date (timestamp), status (scheduled/confirmed/completed/cancelled).
- **Push_Subscriptions**: Endpoints and encryption keys (P256DH, Auth).

### 5.2 External Integrations
- **Razorpay**: For secure Indian payment processing.
- **Fast2SMS**: For reliable OTP delivery.
- **OpenSSL**: For manual implementation of Web Push encryption (RFC 8291).

---

## 6. Token Schedule Logic
The system implements a custom token-to-time mapping:
- Blocks of 10 tokens.
- Morning Session: 09:00 AM – 01:00 PM.
- Inter-block Break: 10 minutes.
- Lunch Break: 01:00 PM – 04:00 PM.
- Evening Session: 04:00 PM – 08:00 PM.
