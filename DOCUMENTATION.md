# Kindergarten Management System - Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Site Map](#site-map)
3. [User Roles & Access](#user-roles--access)
4. [System Flowcharts](#system-flowcharts)
5. [Database Schema](#database-schema)
6. [API Endpoints](#api-endpoints)

---

## System Overview

The Kindergarten Management System is a comprehensive Laravel-based application for managing kindergarten operations, including student attendance tracking via RFID, behavior monitoring, incident reporting, and parent-teacher communication.

### Technology Stack
- **Backend**: Laravel 10.x
- **Admin Panel**: Filament 3.x
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Roles & Permissions
- **Frontend**: Livewire, Alpine.js, Tailwind CSS

### Key Features
- Multi-panel interface (Admin, Teacher, Parent)
- RFID-based attendance system
- Real-time attendance tracking
- Behavior monitoring
- Incident management
- Parent-student relationship management
- Secure pickup verification
- Role-based access control

---

## Site Map

```
Kindergarten Management System
│
├── Public Routes
│   ├── Login (/admin/login, /teacher/login, /parent/login)
│   └── RFID API
│       ├── POST /api/rfid/scan
│       ├── GET /api/rfid/status/{cardId}
│       └── GET /api/rfid/health
│
├── Admin Panel (/admin)
│   ├── Dashboard
│   │   ├── Total Students Widget
│   │   ├── Total Teachers Widget
│   │   ├── Attendance Rate Widget
│   │   └── Recent Incidents Widget
│   │
│   ├── Users Management
│   │   ├── List Users
│   │   ├── Create User
│   │   ├── Edit User
│   │   └── Delete User
│   │
│   ├── Classes Management
│   │   ├── List Classes
│   │   ├── Create Class
│   │   ├── Edit Class (Assign Teacher & Students)
│   │   └── Delete Class
│   │
│   ├── RFID Cards Management
│   │   ├── List RFID Cards
│   │   ├── Create RFID Card
│   │   ├── Assign Card to Student
│   │   └── Deactivate Card
│   │
│   ├── Attendance Management
│   │   ├── View All Attendance
│   │   ├── Daily Attendance Report
│   │   ├── Attendance Statistics
│   │   └── Export Attendance Data
│   │
│   ├── Behavior Management
│   │   ├── List All Behaviors
│   │   ├── Create Behavior Record
│   │   ├── Edit Behavior Record
│   │   └── View Behavior Trends
│   │
│   ├── Incidents Management
│   │   ├── List All Incidents
│   │   ├── Create Incident Report
│   │   ├── Edit Incident Report
│   │   └── View Incident Details
│   │
│   ├── Pickup Management
│   │   ├── List All Pickups
│   │   ├── Record Pickup
│   │   ├── Verify Authorized Person
│   │   └── Pickup History
│   │
│   └── Settings
│       ├── Role Management
│       ├── Permission Management
│       └── System Configuration
│
├── Teacher Panel (/teacher)
│   ├── Dashboard
│   │   ├── My Classes Overview
│   │   ├── Today's Attendance Summary
│   │   ├── Pending Actions
│   │   └── Recent Behaviors
│   │
│   ├── My Classes
│   │   ├── View Class Students
│   │   ├── Class Attendance
│   │   └── Class Performance
│   │
│   ├── Daily Attendance
│   │   ├── Mark Attendance (Manual)
│   │   ├── View Today's Attendance
│   │   ├── Late Students
│   │   └── Absent Students
│   │
│   ├── Behavior Records
│   │   ├── List Behaviors (My Students)
│   │   ├── Create Behavior Record
│   │   ├── Edit Behavior Record
│   │   └── View Behavior Details
│   │
│   ├── Incident Reports
│   │   ├── List Incidents (My Students)
│   │   ├── Create Incident Report
│   │   ├── Edit Incident Report
│   │   └── View Incident Details
│   │
│   └── Student Pickups
│       ├── List Today's Pickups
│       ├── Record Pickup
│       └── Verify Authorized Person
│
└── Parent Panel (/parent)
    ├── Dashboard
    │   ├── My Children Overview
    │   ├── Today's Status
    │   ├── Recent Notifications
    │   └── Upcoming Events
    │
    ├── My Children
    │   ├── View Child Profile
    │   ├── Attendance History
    │   ├── Behavior Reports
    │   └── Incident Reports
    │
    ├── Attendance
    │   ├── View Child Attendance
    │   ├── Monthly Calendar
    │   └── Attendance Statistics
    │
    ├── Behaviors
    │   ├── View Behavior Records
    │   ├── Positive Behaviors
    │   └── Areas for Improvement
    │
    ├── Incidents
    │   ├── View Incident Reports
    │   ├── Incident Details
    │   └── Action Plans
    │
    └── Pickup History
        ├── View Pickup Records
        └── Authorized Persons List
```

---

## User Roles & Access

### Admin
- **Full system access**
- Manages all users, classes, and system settings
- Views system-wide reports and analytics
- Controls RFID card assignments
- Manages roles and permissions

### Teacher
- **Class-specific access**
- Manages students in assigned classes only
- Records daily attendance (manual and RFID)
- Creates behavior and incident reports
- Records student pickups
- Views class-level reports

### Parent
- **Child-specific access**
- Views own children's information only
- Monitors attendance history
- Reviews behavior and incident reports
- Views pickup history
- Receives notifications about their children

---

## System Flowcharts

### 1. RFID Attendance Flow

```
┌─────────────────┐
│  RFID Reader    │
│  Scans Card     │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│ POST /api/rfid/scan     │
│ Payload: {card_id}      │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│ Validate Card ID        │
│ - Check if exists       │
│ - Check if active       │
└────────┬────────────────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
Invalid    Valid
Card       Card
    │         │
    │         ▼
    │    ┌────────────────────────┐
    │    │ Get Student Info       │
    │    │ - Student ID           │
    │    │ - Name                 │
    │    │ - Class                │
    │    └────────┬───────────────┘
    │             │
    │             ▼
    │    ┌────────────────────────┐
    │    │ Check Today's Record   │
    │    └────────┬───────────────┘
    │             │
    │        ┌────┴────┐
    │        │         │
    │        ▼         ▼
    │   Has Record  No Record
    │        │         │
    │        │         ▼
    │        │    ┌─────────────────────┐
    │        │    │ Create Attendance   │
    │        │    │ - Check-in Time     │
    │        │    │ - Status: Present   │
    │        │    │ - Late if >8:30 AM  │
    │        │    └─────────┬───────────┘
    │        │              │
    │        ▼              ▼
    │   ┌─────────────────────────┐
    │   │ Update Check-out Time   │
    │   │ if already checked in   │
    │   └─────────┬───────────────┘
    │             │
    ▼             ▼
┌──────────────────────────┐
│ Return Response          │
│ - Success/Error          │
│ - Student Name           │
│ - Check-in/out Time      │
│ - Status (Late/On-time)  │
└──────────────────────────┘
```

### 2. Teacher Daily Attendance Management Flow

```
┌──────────────────────┐
│ Teacher Logs In      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Navigate to          │
│ Daily Attendance     │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────┐
│ System Loads Students        │
│ - Filter by teacher's class  │
│ - Load today's attendance    │
│ - Show RFID status           │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Display Student List         │
│ ┌──────────────────────────┐ │
│ │ Student 1: ✓ Present     │ │
│ │ Check-in: 8:15 AM        │ │
│ │ Status: On-time          │ │
│ └──────────────────────────┘ │
│ ┌──────────────────────────┐ │
│ │ Student 2: ⚠ Late        │ │
│ │ Check-in: 8:45 AM        │ │
│ └──────────────────────────┘ │
│ ┌──────────────────────────┐ │
│ │ Student 3: ✗ Unmarked    │ │
│ │ [Mark Present] [Absent]  │ │
│ └──────────────────────────┘ │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Teacher Actions              │
│ - Mark as Present            │
│ - Mark as Absent             │
│ - Add absence reason         │
│ - Update check-in time       │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Save Changes to Database     │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Update Stats & Refresh View  │
│ - Attendance rate            │
│ - Late count                 │
│ - Absent count               │
└──────────────────────────────┘
```

### 3. Behavior Recording Flow

```
┌──────────────────────┐
│ Teacher/Admin        │
│ Observes Behavior    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────┐
│ Navigate to Behavior Section │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Click "Create Behavior"      │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Fill Behavior Form           │
│ ┌──────────────────────────┐ │
│ │ Select Student           │ │
│ │ Select Type:             │ │
│ │  ○ Positive              │ │
│ │  ○ Negative              │ │
│ │                          │ │
│ │ Select Category:         │ │
│ │  ☐ Sharing               │ │
│ │  ☐ Following Rules       │ │
│ │  ☐ Respect               │ │
│ │  ☐ Participation         │ │
│ │                          │ │
│ │ Description:             │ │
│ │ [Text area]              │ │
│ │                          │ │
│ │ Date & Time              │ │
│ └──────────────────────────┘ │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Validate Form Data           │
└──────────┬───────────────────┘
           │
      ┌────┴────┐
      │         │
      ▼         ▼
   Invalid   Valid
      │         │
      │         ▼
      │    ┌────────────────────┐
      │    │ Save to Database   │
      │    └────────┬───────────┘
      │             │
      │             ▼
      │    ┌────────────────────┐
      │    │ Notify Parent      │
      │    │ (if configured)    │
      │    └────────┬───────────┘
      │             │
      ▼             ▼
┌──────────────────────────────┐
│ Show Success/Error Message   │
└──────────────────────────────┘
```

### 4. Parent Dashboard Access Flow

```
┌──────────────────────┐
│ Parent Logs In       │
│ /parent/login        │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────┐
│ Authenticate User            │
│ - Verify credentials         │
│ - Check role = parent        │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Load Parent's Children       │
│ - Query parent_student table │
│ - Get student details        │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Load Dashboard Widgets       │
│                              │
│ ┌──────────────────────────┐ │
│ │ My Children              │ │
│ │ • Emma Smith (Class A)   │ │
│ │ • Jack Smith (Class C)   │ │
│ └──────────────────────────┘ │
│                              │
│ ┌──────────────────────────┐ │
│ │ Today's Status           │ │
│ │ Emma: ✓ Present (8:15)   │ │
│ │ Jack: ⚠ Late (8:50)      │ │
│ └──────────────────────────┘ │
│                              │
│ ┌──────────────────────────┐ │
│ │ Recent Activity          │ │
│ │ • New behavior report    │ │
│ │ • Attendance: 95%        │ │
│ └──────────────────────────┘ │
│                              │
│ ┌──────────────────────────┐ │
│ │ Quick Actions            │ │
│ │ [View Attendance]        │ │
│ │ [View Behaviors]         │ │
│ │ [View Incidents]         │ │
│ └──────────────────────────┘ │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Parent Can Navigate To:      │
│ - Attendance History         │
│ - Behavior Reports           │
│ - Incident Reports           │
│ - Pickup History             │
└──────────────────────────────┘
```

### 5. Incident Report Workflow

```
┌──────────────────────┐
│ Incident Occurs      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────┐
│ Teacher/Staff Creates Report │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Fill Incident Form           │
│ ┌──────────────────────────┐ │
│ │ Select Student(s)        │ │
│ │ Incident Type:           │ │
│ │  ○ Minor Injury          │ │
│ │  ○ Major Injury          │ │
│ │  ○ Behavioral            │ │
│ │  ○ Other                 │ │
│ │                          │ │
│ │ Severity:                │ │
│ │  ○ Low                   │ │
│ │  ○ Medium                │ │
│ │  ○ High                  │ │
│ │                          │ │
│ │ Location:                │ │
│ │ [Text field]             │ │
│ │                          │ │
│ │ Description:             │ │
│ │ [Text area]              │ │
│ │                          │ │
│ │ Action Taken:            │ │
│ │ [Text area]              │ │
│ │                          │ │
│ │ ☑ Parent Notified        │ │
│ │ ☐ Medical Attention      │ │
│ │ ☐ Follow-up Required     │ │
│ └──────────────────────────┘ │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Save Incident Report         │
└──────────┬───────────────────┘
           │
      ┌────┴────┐
      │         │
      ▼         ▼
  High/Med    Low
  Severity  Severity
      │         │
      ▼         │
┌─────────────────┐│
│ Notify Admin    ││
│ Immediately     ││
└─────────┬───────┘│
          │        │
          ▼        ▼
┌──────────────────────────────┐
│ Notify Parent                │
│ - Email notification         │
│ - In-app notification        │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Track in System              │
│ - Incident log               │
│ - Student record             │
│ - Class statistics           │
└──────────────────────────────┘
```

### 6. Pickup Authorization Flow

```
┌──────────────────────┐
│ Pickup Time          │
│ Person Arrives       │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────┐
│ Teacher Opens Pickup Screen  │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Select Student               │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ System Shows Authorized List │
│ ┌──────────────────────────┐ │
│ │ Authorized to Pick Up:   │ │
│ │ • Sarah Smith (Mother)   │ │
│ │ • John Smith (Father)    │ │
│ │ • Mary Johnson (Grandma) │ │
│ └──────────────────────────┘ │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Select Person Picking Up     │
└──────────┬───────────────────┘
           │
      ┌────┴────┐
      │         │
      ▼         ▼
 Authorized  Not Listed
      │         │
      │         ▼
      │    ┌────────────────────┐
      │    │ Verify ID          │
      │    │ Contact Parent     │
      │    │ Get Approval       │
      │    └────────┬───────────┘
      │             │
      │        ┌────┴────┐
      │        │         │
      │        ▼         ▼
      │    Approved  Denied
      │        │         │
      ▼        ▼         ▼
┌──────────────────────────────┐
│ Record Pickup                │
│ - Student ID                 │
│ - Picked up by               │
│ - Pickup time                │
│ - Notes (if any)             │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Release Student              │
│ Update Attendance Status     │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Notify Parent (if configured)│
│ "Emma picked up at 3:30 PM"  │
└──────────────────────────────┘
```

---

## Database Schema

### Key Tables and Relationships

```
┌─────────────────────┐
│      users          │
├─────────────────────┤
│ id                  │◄─────┐
│ name                │      │
│ email               │      │
│ password            │      │
│ role                │      │
│ created_at          │      │
└─────────────────────┘      │
                             │
                             │
┌─────────────────────┐      │
│     classes         │      │
├─────────────────────┤      │
│ id                  │◄───┐ │
│ name                │    │ │
│ teacher_id          │────┘ │
│ school_year         │      │
│ created_at          │      │
└─────────────────────┘      │
         ▲                   │
         │                   │
┌─────────────────────┐      │
│     students        │      │
├─────────────────────┤      │
│ id                  │◄───┐ │
│ user_id             │────┼─┘
│ class_id            │────┘
│ first_name          │
│ last_name           │
│ date_of_birth       │
│ created_at          │
└─────────────────────┘
         ▲
         │
         │
┌─────────────────────┐
│   parent_student    │
├─────────────────────┤
│ id                  │
│ parent_id           │────┐
│ student_id          │────┤
│ relationship        │    │
└─────────────────────┘    │
                           │
                           │
┌─────────────────────┐    │
│    rfid_cards       │    │
├─────────────────────┤    │
│ id                  │    │
│ card_id (unique)    │    │
│ student_id          │────┘
│ is_active           │
│ created_at          │
└─────────────────────┘
         │
         │
         ▼
┌─────────────────────┐
│   attendances       │
├─────────────────────┤
│ id                  │
│ student_id          │────┐
│ date                │    │
│ status              │    │
│ check_in_time       │    │
│ check_out_time      │    │
│ is_late             │    │
│ absence_reason      │    │
│ teacher_id          │    │
│ created_at          │    │
└─────────────────────┘    │
                           │
                           │
┌─────────────────────┐    │
│    behaviors        │    │
├─────────────────────┤    │
│ id                  │    │
│ student_id          │────┤
│ teacher_id          │    │
│ type                │    │
│ category            │    │
│ description         │    │
│ date                │    │
│ created_at          │    │
└─────────────────────┘    │
                           │
                           │
┌─────────────────────┐    │
│    incidents        │    │
├─────────────────────┤    │
│ id                  │    │
│ student_id          │────┤
│ reported_by         │    │
│ incident_type       │    │
│ severity            │    │
│ location            │    │
│ description         │    │
│ action_taken        │    │
│ parent_notified     │    │
│ created_at          │    │
└─────────────────────┘    │
                           │
                           │
┌─────────────────────┐    │
│     pickups         │    │
├─────────────────────┤    │
│ id                  │    │
│ student_id          │────┘
│ picked_up_by        │
│ relationship        │
│ pickup_time         │
│ notes               │
│ created_at          │
└─────────────────────┘
```

### Table Descriptions

#### users
- Core user table for authentication
- Contains admins, teachers, and parents
- Uses Spatie roles and permissions

#### classes
- Represents classroom groups
- Each class has one assigned teacher
- Students are assigned to classes

#### students
- Extends user table with student-specific data
- Links to a user account (optional for young children)
- Belongs to a class

#### parent_student
- Many-to-many relationship between parents and students
- A student can have multiple parents/guardians
- A parent can have multiple children

#### rfid_cards
- Stores RFID card identifiers
- One card per student
- Can be activated/deactivated

#### attendances
- Daily attendance records
- Tracks check-in/check-out times
- Records late arrivals and absences
- Links to both student and teacher

#### behaviors
- Positive and negative behavior records
- Categorized for easy filtering
- Used for student progress tracking

#### incidents
- Records of incidents requiring documentation
- Severity levels for prioritization
- Tracks parent notification and follow-up

#### pickups
- Records who picked up each student
- Verifies authorized pickup persons
- Maintains pickup history

---

## API Endpoints

### RFID API

#### POST /api/rfid/scan
Records attendance via RFID card scan.

**Request:**
```json
{
  "card_id": "ABC123XYZ"
}
```

**Response (Success - Check-in):**
```json
{
  "success": true,
  "message": "Attendance recorded successfully",
  "data": {
    "student": {
      "id": 1,
      "name": "Emma Smith",
      "class": "Class A"
    },
    "action": "check_in",
    "time": "2025-11-08 08:15:00",
    "status": "on_time"
  }
}
```

**Response (Success - Check-out):**
```json
{
  "success": true,
  "message": "Check-out recorded successfully",
  "data": {
    "student": {
      "id": 1,
      "name": "Emma Smith",
      "class": "Class A"
    },
    "action": "check_out",
    "time": "2025-11-08 15:30:00"
  }
}
```

**Response (Error - Invalid Card):**
```json
{
  "success": false,
  "message": "Invalid or inactive RFID card",
  "error": "CARD_NOT_FOUND"
}
```

---

#### GET /api/rfid/status/{cardId}
Get student information and today's attendance status for a card.

**Response:**
```json
{
  "success": true,
  "data": {
    "card_id": "ABC123XYZ",
    "student": {
      "id": 1,
      "name": "Emma Smith",
      "class": "Class A"
    },
    "today_attendance": {
      "date": "2025-11-08",
      "status": "present",
      "check_in_time": "08:15:00",
      "check_out_time": null,
      "is_late": false
    }
  }
}
```

---

#### GET /api/rfid/health
Health check endpoint for RFID system.

**Response:**
```json
{
  "success": true,
  "message": "RFID system is operational",
  "timestamp": "2025-11-08T08:15:00Z"
}
```

---

## Authentication & Authorization

### Login Endpoints
- Admin: `/admin/login`
- Teacher: `/teacher/login`
- Parent: `/parent/login`

### Role-Based Access Control

```
Permissions Matrix
─────────────────────────────────────────────────
Action              │ Admin │ Teacher │ Parent
─────────────────────────────────────────────────
Manage Users        │   ✓   │    ✗    │   ✗
Manage Classes      │   ✓   │    ✗    │   ✗
Manage RFID Cards   │   ✓   │    ✗    │   ✗
View All Students   │   ✓   │    ✗    │   ✗
View Own Students   │   ✓   │    ✓    │   ✗
View Own Children   │   ✗   │    ✗    │   ✓
Mark Attendance     │   ✓   │    ✓    │   ✗
View All Attendance │   ✓   │    ✗    │   ✗
View Class Att.     │   ✓   │    ✓    │   ✗
View Child Att.     │   ✗   │    ✗    │   ✓
Create Behaviors    │   ✓   │    ✓    │   ✗
View All Behaviors  │   ✓   │    ✗    │   ✗
View Child Behaviors│   ✗   │    ✗    │   ✓
Create Incidents    │   ✓   │    ✓    │   ✗
View All Incidents  │   ✓   │    ✗    │   ✗
View Child Incidents│   ✗   │    ✗    │   ✓
Record Pickups      │   ✓   │    ✓    │   ✗
View Pickup History │   ✓   │    ✓    │   ✓
System Settings     │   ✓   │    ✗    │   ✗
─────────────────────────────────────────────────
```

---

## Realistic Data Patterns

### Attendance Seeder Features

The system generates realistic kindergarten attendance data:

#### Punctuality Profiles
Students are assigned personality profiles affecting arrival times:

- **Early Birds (25%)**: Arrive 7:30-8:00 AM
- **On-time (45%)**: Arrive 8:00-8:30 AM  
- **Sometimes Late (20%)**: Usually on-time, occasionally late
- **Frequently Late (10%)**: Often arrive after 8:30 AM

#### Attendance Rate
- Overall: ~95% attendance
- Accounts for occasional absences
- Weekend detection (no school)

#### School Hours
- Drop-off: 7:30-9:00 AM
- Late threshold: 8:30 AM
- Pick-up: 3:00-4:00 PM

#### Absence Reasons
When students are absent:
- Doctor appointment
- Dentist appointment  
- Sick
- Family emergency

#### Teacher Assignment
- Uses actual class teachers
- Not randomly assigned
- Maintains data integrity

---

## System Requirements

### Server Requirements
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Node.js & NPM (for frontend assets)

### PHP Extensions
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath

### Recommended
- Redis (for caching and queues)
- Supervisor (for queue workers)
- SSL certificate (for production)

---

## Installation Guide

### 1. Clone Repository
```bash
git clone <repository-url>
cd project-app-2
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure database credentials in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=projectapp2
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 5. Storage Permissions
```bash
chmod -R 775 storage bootstrap/cache
```

### 6. Build Assets
```bash
npm run build
```

### 7. Start Development Server
```bash
php artisan serve
```

Access the application at: `http://localhost:8000`

---

## Default Credentials

After seeding, you can login with:

### Admin
- Email: `admin@example.com`
- Password: `password`

### Teacher
- Email: `teacher1@example.com` through `teacher10@example.com`
- Password: `password`

### Parent
- Email: `parent1@example.com` through `parent20@example.com`
- Password: `password`

---

## Security Considerations

### HTTPS
- Always use HTTPS in production
- Force HTTPS in production environment
- Configure proper SSL certificates

### CSRF Protection
- Enabled by default for web routes
- API routes use Sanctum tokens
- CSRF tokens verified on all state-changing requests

### Authentication
- Passwords hashed with bcrypt
- Session-based authentication for web
- Token-based authentication for API

### Authorization
- Role-based access control via Spatie
- Middleware protects all panel routes
- Row-level security for data access

### Input Validation
- All inputs validated
- SQL injection protection via Eloquent ORM
- XSS protection via Blade templating

---

## Maintenance & Support

### Daily Tasks
- Monitor attendance records
- Review incident reports
- Check system logs

### Weekly Tasks
- Backup database
- Review user access logs
- Update attendance statistics

### Monthly Tasks
- Generate attendance reports
- Review behavior trends
- Update class assignments as needed

### Backup Strategy
```bash
# Database backup
php artisan backup:run

# Automated backups (cron)
0 2 * * * cd /path/to/app && php artisan backup:run
```

---

## Troubleshooting

### Common Issues

#### RFID Card Not Working
1. Check if card is active in system
2. Verify card_id matches exactly
3. Check API endpoint is accessible
4. Review application logs

#### Permission Denied Errors
1. Verify user role assignment
2. Clear cache: `php artisan cache:clear`
3. Check Spatie permissions configuration

#### Session Issues
1. Clear sessions: `php artisan session:flush`
2. Check session driver configuration
3. Verify storage permissions

#### Database Connection Errors
1. Verify credentials in `.env`
2. Check MySQL service is running
3. Ensure database exists

---

## Future Enhancements

### Planned Features
- [ ] Mobile app for parents
- [ ] Real-time notifications via WebSocket
- [ ] Automated report generation
- [ ] Parent-teacher messaging
- [ ] Student progress tracking
- [ ] Meal planning and tracking
- [ ] Photo sharing with parents
- [ ] Emergency broadcast system
- [ ] Integration with payment systems
- [ ] Multi-language support

---

## Support & Contact

For technical support or questions:
- Documentation: This file
- Laravel Docs: https://laravel.com/docs
- Filament Docs: https://filamentphp.com/docs

---

**Last Updated**: November 8, 2025  
**Version**: 1.0  
**Developed with**: Laravel 10.x + Filament 3.x
