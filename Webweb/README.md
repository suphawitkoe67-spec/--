# ClubConnect-WebSystem — ระบบจัดการและลงทะเบียนชมรมออนไลน์

รายวิชา COMP342 วิศวกรรมซอฟต์แวร์เบื้องต้น (Mini Project / Demo)

## 1\. วัตถุประสงค์

แก้ปัญหาการรับสมัครชมรมแบบกระดาษ/Google Forms กระจัดกระจาย ให้มีระบบกลางสำหรับ
นักศึกษาค้นหาชมรม + สมัครออนไลน์ และให้ผู้ดูแลชมรมตรวจใบสมัคร/อนุมัติได้ในที่เดียว
อ้างอิง Requirement / Use Case / Workflow / Test Case จากเอกสารใบงานในโฟลเดอร์รายวิชา
(`software\_engineering\_lab\_worksheets.pdf`, ใบงานที่ 1–2, เอกสารระบบชมรม)

## 2\. Technology

* Frontend: HTML + CSS (`assets/style.css`) + JavaScript (`assets/script.js`) — ไม่ใช้ framework ใหญ่
* Backend: PHP (ไม่ต้องติดตั้ง dependency, รันด้วย PHP Development Server หรือ XAMPP ได้)
* Data Storage: **ไฟล์ JSON** (`data/\*.json`) แทน Database Server

  * อ่าน/เขียนด้วย `file\_get\_contents` / `file\_put\_contents` + `json\_decode` / `json\_encode`
  * เขียนแบบ `LOCK\_EX` และ **backup อัตโนมัติ** ไป `data/backup/` ก่อนเขียนทุกครั้ง
* Session: PHP Session สำหรับ Login/สิทธิ์

## 3\. โครงสร้างโฟลเดอร์

```
Webweb/
├── index.php               # กระจายตาม role (student->dashboard, admin->admin/dashboard)
├── login.php / logout.php
├── dashboard.php           # Student: ค้นหา+กรอง+รายชื่อชมรม
├── club\_detail.php         # รายละเอียดชมรม + ปุ่มสมัคร/หมดเขต
├── apply.php               # ฟอร์มใบสมัคร + upload (ตรวจ 10MB ทั้ง JS/PHP)
├── application\_status.php  # Student ดูเฉพาะใบสมัครตัวเอง (?id= ดูใบเดียว)
├── admin/
│   ├── dashboard.php       # สรุปจำนวน PENDING/APPROVED/REJECTED
│   ├── applications.php    # ตารางใบสมัคร + filter สถานะ
│   ├── application\_detail.php # อนุมัติ/ไม่อนุมัติ + หมายเหตุ (+เตือนเปลี่ยนซ้ำ)
│   ├── announcements.php   # สร้าง/ลบประกาศ + ทางไป club\_form
│   └── club\_form.php       # สร้าง/แก้ไข/ลบชมรม
├── api/auth.php, clubs.php, applications.php, announcements.php
├── includes/functions.php  # readJson/writeJson/backupJson/auth/escape กลาง
├── includes/header.php, footer.php
├── data/users.json, clubs.json, applications.json, announcements.json
├── data/backup/            # ไฟล์ .bak อัตโนมัติ
├── uploads/                # เอกสารแนบ (ชื่อไฟล์แบบ APPxxxx\_rand.ext)
└── assets/style.css, script.js
```

## 4\. วิธี Run

ต้องมี PHP ก่อน (ถ้ายังไม่มี ติดตั้งจาก https://www.php.net/downloads หรือใช้ XAMPP)

```powershell
cd Webweb
php -S localhost:8000
```

แล้วเปิด http://localhost:8000

กรณี XAMPP: copy โฟลเดอร์ `Webweb` ไปไว้ใน `htdocs` แล้วเปิด `http://localhost/Webweb/`

> หมายเหตุเรื่องอัปโหลด: กติการะบบคือไฟล์ ≤ 10MB โค้ด PHP ตรวจขนาด/นามสกุล/MIME ให้แล้ว
> แต่ถ้าเซิร์ฟเวอร์ตั้ง `upload\_max\_filesize` / `post\_max\_size` ต่ำกว่า 10MB
> ไฟล์ใหญ่จะถูก PHP ปฏิเสธก่อนถึงโค้ด — ระบบดักกรณีนี้ไว้แล้วและแสดงข้อความ
> "ขนาดไฟล์ต้องไม่เกิน 10MB" เช่นกัน (แนะนำตั้ง `upload\_max\_filesize=10M`, `post\_max\_size=12M`)

## 5\. บัญชี Demo

|Role|Username|Password|
|-|-|-|
|Student|student01|1234|
|Student|student02|1234|
|Club Admin|admin01|1234|
|(แสดงไว้ใต้ฟอร์ม Login ด้วย)|||

## 6\. Features (ตาม FR)

* FR-01 Login + Session + แยก role, กัน Student เข้าหน้า admin, ไม่ login เข้าหน้า private ไม่ได้
* FR-02 ดูรายชื่อ/รายละเอียด/คุณสมบัติ/วันรับสมัคร
* FR-03 ค้นหาชื่อ + กรองประเภท (ดนตรี/กีฬา/วิชาการ/เทคโนโลยี/ศิลปะ/จิตอาสา)
* FR-04 ฟอร์มสมัคร + ตรวจ required + Application ID อัตโนมัติ (APP0001…) + PENDING + created\_at/updated\_at
* FR-05 แนบ PDF/JPG/JPEG/PNG ≤ 10MB ตรวจทั้ง JS และ PHP ข้อความ "ขนาดไฟล์ต้องไม่เกิน 10MB"
* FR-06 Admin สร้าง/แก้ไขชมรม + ประกาศ → Student เห็นทันที
* FR-07 Admin ดู/อนุมัติ/ปฏิเสธ + หมายเหตุ + อัปเดต updated\_at + เตือนถ้าเปลี่ยนสถานะซ้ำ
* FR-08 Student ดูสถานะ PENDING/รอตรวจสอบ, APPROVED/อนุมัติ, REJECTED/ไม่อนุมัติ เฉพาะของตัวเอง

## 7\. Data Storage \& Limitations

* Version นี้ใช้ **JSON File Storage สำหรับ Demo/Mini Project แทน Database Server** ตามโจทย์
* ข้อจำกัด: ไม่รองรับ concurrent สูง (มีแค่ LOCK\_EX + backup), รหัสผ่านเก็บ plain text (demo), ไม่มี email/line แจ้งเตือน, ไม่มีระบบชำระเงิน/กิจกรรม/attendance (นอก scope โดยตั้งใจ)

## 8\. Future Work

* ย้ายไป SQLite/MySQL เมื่อต้องการผู้ใช้พร้อมกันเยอะ
* Hash รหัสผ่าน (password\_hash), CSRF token, แบ่ง role ต่อชมรม, แจ้งเตือน, export CSV

