# MedFlow User Manual

Welcome to **MedFlow**, a state-of-the-art Healthcare Management & Appointment Booking platform. This manual provides a comprehensive guide on how to use every feature of the platform, tailored to each user role.

---

## 📋 Table of Contents
1. [General Features](#general-features)
2. [Patient Portal](#patient-portal)
3. [Doctor Portal](#doctor-portal)
4. [Admin Portal](#admin-portal)
5. [Pharmacist Portal](#pharmacist-portal)
6. [Payment & Security](#payment--security)

---

## 🌐 General Features
- **Responsive Design**: MedFlow is fully accessible via desktop, tablet, and smartphone.
- **Role-Based Access**: Secure login systems for Patients, Doctors, Admin, and Pharmacists.
- **Real-Time Notifications**:
    - **Web Push Notifications**: Receive alerts in your browser even when the tab is closed.
    - **Email Alerts**: Confirmation and reminders sent directly to your inbox.
    - **SMS Alerts**: Immediate updates on token status (if configured).

---

## 🏥 Patient Portal

### 1. Account Setup
- **Registration**: Sign up with your name, phone number, and a secure password.
- **Phone Verification**: Complete the verification process (via OTP) to unlock booking features.
- **Profile Management**: Update your personal details, gender, and address at any time.

### 2. Appointment Booking
- **Step 1: Patient Info**: Confirm your identity for the booking.
- **Step 2: Doctor Search**: 
    - Search for doctors by name or hospital.
    - Use the **Distance Filter** (if location is enabled) to find the nearest clinic.
- **Step 3: Session Selection**: 
    - Choose a date and a **Token Number**.
    - View **Approximate Timing** for each token block based on the clinic's schedule.
- **Step 4: Confirmation & Payment**:
    - Review appointment details.
    - Pay the booking fee securely via **Razorpay**.

### 3. Patient Dashboard
- **Live Token Status**: See exactly which token the doctor is currently serving in real-time.
- **Upcoming Appointments**: Track your status (Scheduled/Confirmed) and view details.
- **Symptom Analyzer**: Access an AI-powered analyzer to understand your symptoms before visiting.
- **PDF Confirmation**: Download a PDF receipt of your appointment for records or offline use.
- **Bill Payment**: Pay for pharmacy prescriptions and hospital bills directly from the dashboard.

---

## 🩺 Doctor Portal

### 1. Daily Management
- **Doctor Dashboard**: View total appointments, upcoming patients, and your schedule for today.
- **Live Status Control**: Toggle your status between **"Available"**, **"Busy"**, or **"On Break"** to keep patients informed.
- **Live Token Counter**: Increment the **"Current Token"** as you see patients. This automatically sends Push and Email notifications to upcoming patients (e.g., those who are 10 tokens away).

### 2. Patient Interaction
- **Appointments List**: View detailed information about each patient.
- **Prescription Generation**: Create digital prescriptions after consultation. 
- **Status Updates**: Mark appointments as 'Completed' or 'Cancelled'.

### 3. Exports
- **PDF Schedule**: Print your daily schedule to have a physical copy on your desk.
- **PDF History**: Export your entire consultation history for reporting.

---

## ⚙️ Admin Portal

### 1. Walk-in Bookings
- **Direct Token Assignment**: Book patients who arrive offline at the clinic.
- **Instant Receipts**: After booking a walk-in, download the PDF confirmation immediately to print and hand to the patient.

### 2. User & System Management
- **User Management**: Create, edit, or remove Patient, Doctor, or Staff accounts.
- **Appointment Overview**: Monitor all hospital appointments across all departments.
- **Financial Tracking**: View payment statuses for all bookings and bills.

### 3. Global Exports
- **System Schedule**: Generate a master schedule for the entire hospital.
- **System History**: Export comprehensive data across all doctors and patients.

---

## 💊 Pharmacist Portal
- **Dashboard**: View pending prescriptions sent by doctors.
- **Bill Generation**: Create bills for medicines prescribed to patients.
- **Payment Verification**: Track when patients have paid their pharmacy bills online.

---

## 💳 Payment & Security
- **Secure Transactions**: All payments are processed through Razorpay with industry-standard encryption.
- **VAPID Security**: Push notifications use high-level asymmetric encryption to protect your data.
- **Privacy**: Patient medical history and symptoms are only accessible to the assigned doctor.

---

> [!TIP]
> **Pro Tip for Patients**: Enable "Push Notifications" the first time the browser asks. This allows us to notify you when you are only 10 patients away, so you don't have to wait in the clinic queue!
