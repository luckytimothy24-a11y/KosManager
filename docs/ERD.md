# KosManager - Entity Relationship Diagram

## Database Schema Overview

```
+-------------+       +-----+       +----------+
|    users     |1----->| kos |1----->|  kamar   |
+-------------+   1:N +-----+   1:N +----------+
| id           |       | id   |       | id        |
| name         |       | owner_id(FK)| | kos_id(FK)|
| email        |       | name  |       | room_number|
| password     |       | address|      | room_name |
| role         |       | status |      | floor     |
| phone        |       +-----+       | room_type |
| address      |           |          | daily_price|
| avatar       |           |          | monthly_price|
| is_active    |           |          | area      |
+-------------+           |          | status    |
      |                   |          +----------+
      |                   |              |  |  |
      | 1:N              1:N            N  N  N
      |                   |              |  |  |
      v                   v              |  |  |
+-------------+     +----------+        |  |  |
|  bookings   |     |penghunis |<-------+  |  |
+-------------+     +----------+           |  |
| user_id(FK) |     | user_id(FK)|         |  |
| kos_id(FK)  |     | kos_id(FK) |         |  |
| kamar_id(FK)|     | kamar_id(FK)|--------+  |
| status      |     | status     |            |
+-------------+     +----+-------+            |
                          |                   |
                     1:N  |  1:N              |
                          v   v               |
                   +----------+  +----------+ |
                   | kontraks |  | check_ins| |
                   +----------+  +----------+ |
                   | penghuni_id(FK)| | penghuni_id(FK)|
                   | kamar_id(FK)   | | kamar_id(FK)  |
                   | status         | +----------+ |
                   +----+-----------+              |
                        |                          |
                   1:N  |                    +----------+
                        v                    |check_outs|
                   +----------+              +----------+
                   | tagihans |              | penghuni_id(FK)|
                   +----------+              | kamar_id(FK)  |
                   | kontrak_id(FK)|         | status        |
                   | kamar_id(FK)  |         +----------+
                   | status        |
                   +----+-----------+
                        |
                   1:N  |
                        v
                   +------------+
                   |pembayarans |
                   +------------+
                   | tagihan_id(FK)|
                   | status        |
                   +------------+

+-------------------+     +---------------+
| notifications     |     |  audit_logs   |
+-------------------+     +---------------+
| user_id(FK)       |     | user_id(FK)   |
| type, title       |     | action        |
| message, is_read  |     | module        |
+-------------------+     | description   |
                          +---------------+

+-------------------+     +---------------+
| kamar_fasilitas   |     |  kos_user     |
+-------------------+     +---------------+
| kamar_id(FK)      |     | kos_id(FK)    |
| fasilitas_id(FK)  |     | user_id(FK)   |
+-------------------+     +---------------+
  (pivot: kamar <->        (pivot: kos <-
   fasilitas)               admin assignment)

+-------------------+
|    fasilitas      |
+-------------------+
| id                |
| name              |
| icon              |
+-------------------+
```

## Tables Summary

| # | Table | Records | Description |
|---|-------|---------|-------------|
| 1 | users | 11 | All users (super_admin, admin, owner, tenant) |
| 2 | kos | 3 | Boarding houses |
| 3 | kamar | 15 | Rooms (5 per kos) |
| 4 | fasilitas | 10 | Facilities (WiFi, AC, etc.) |
| 5 | kamar_fasilitas | - | Room-Facility pivot |
| 6 | kos_user | - | Kos-Admin assignment pivot |
| 7 | bookings | 5 | Room bookings |
| 8 | penghunis | 2 | Active tenants |
| 9 | kontraks | 2 | Rental contracts |
| 10 | check_ins | - | Check-in records |
| 11 | check_outs | - | Check-out records |
| 12 | tagihans | 2 | Bills/invoices |
| 13 | pembayarans | - | Payment records |
| 14 | notifications | - | System notifications |
| 15 | audit_logs | - | Activity audit trail |

## Key Relationships

```
User (owner)  ──1:N──> Kos ──1:N──> Kamar
User (tenant) ──1:N──> Booking ──1:1──> Kontrak
Penghuni      ──1:N──> Tagihan ──1:N──> Pembayaran
Kamar         ──M:N──> Fasilitas (via kamar_fasilitas)
Kos           ──M:N──> User (admin) (via kos_user)
```

## Status Flow

### Kamar Status
```
Available ──(Booking Approved)──> Booked
Booked    ──(Check-in)──> Occupied
Occupied  ──(Check-out)──> Available
Available ──(Manual)──> Maintenance
```

### Booking Status
```
Pending ──(Approve)──> Approved ──(Check-in Done)──> Completed
Pending ──(Reject)──> Rejected
Approved ──(Cancel)──> Cancelled
```

### Kontrak Status
```
Active ──(End Date)──> Expired
Active ──(Early Terminate)──> Terminated
```

### Tagihan Status
```
Unpaid ──(Upload Payment)──> Pending Verification ──(Approve)──> Paid
Unpaid ──(Past Due Date)──> Overdue
```

### Pembayaran Status
```
Pending ──(Admin Approve)──> Approved
Pending ──(Admin Reject)──> Rejected
```
