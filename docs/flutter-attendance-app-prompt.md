# Flutter Attendance App - InfiniteBrain

## 📱 รายละเอียดโปรเจค

สร้างแอพพลิเคชัน Flutter สำหรับเซ็นชื่อเข้าเรียนของนักเรียน โดยใช้ลายเซ็นดิจิทัล

### ข้อมูลทั่วไป
- **ชื่อแอพ:** InfiniteBrain - โปรแกรมบันทึกเวลาเรียน
- **Platform:** Android & iOS
- **Base URL:** `http://34.87.33.138:81/api`
- **Logo:** ใช้ไฟล์ `infinite-b-icon-non-text-2.png` 

---

## 🎨 UI/UX Design

### Theme Colors
```dart
// Primary Colors
Color primaryGreen = Color(0xFF4CAF50);      // ปุ่มหลัก
Color lightGreen = Color(0xFF81C784);        // ปุ่มรอง
Color backgroundColor = Color(0xFFFFFFFF);   // พื้นหลัง
Color textColor = Color(0xFF333333);         // ข้อความ
Color borderColor = Color(0xFFE0E0E0);       // ขอบ
```

### หน้าจอหลัก (Main Screen)
1. **AppBar:** 
   - สีเขียว (#4CAF50)
   - Logo ด้านซ้าย
   - ชื่อ "InfiniteBrain" และ "โปรแกรมบันทึกเวลาเรียน"
   - Menu icon ด้านขวา

2. **Signature Pad:**
   - กรอบสี่เหลี่ยมสำหรับวาดลายเซ็น
   - ขนาดประมาณ 300x150 pixels
   - พื้นหลังสีขาว ขอบสีเทา

3. **ปุ่ม "ลบลายเซ็นต์":**
   - สีเขียว เต็มความกว้าง
   - ใช้ลบลายเซ็นที่วาด

4. **Form Fields:**
   - **นักเรียน:** Dropdown/Autocomplete พร้อม icon รูปคน
   - **วิชาที่เรียน:** Dropdown พร้อม icon รูปโลก
   - **วันที่เรียน:** Date picker (format: YYYY-MM-DD)
   - **เวลาที่เรียน:** Time picker (format: HH:mm:ss)
   - **ชั่วโมงที่เรียน:** Dropdown (1, 2, 3, 4... ชั่วโมง)

5. **ปุ่มด้านล่าง:**
   - **"ล้างข้อมูล":** ปุ่มสีเขียวอ่อน (outline)
   - **"บันทึกข้อมูล":** ปุ่มสีเขียวเข้ม (filled)

---

## 🔌 API Endpoints

### 1. ดึงรายชื่อนักเรียน
```
GET /api/searchstudent
Headers:
  - X-Auth-Key: infinite@2022

Response: 
[
  {
    "id": 1,
    "code": "STD001",
    "name": "ชื่อนักเรียน",
    ...
  }
]
```

### 2. ดึงรายชื่อวิชา (ตามนักเรียนที่เลือก)
```
GET /api/searchsubject?std={student_id}
Headers:
  - X-Auth-Key: infinite@2022

Response:
[
  {
    "id": 1,
    "title": "ชื่อวิชา",
    ...
  }
]
```

### 3. บันทึกข้อมูลการเข้าเรียน
```
POST /api/savehistories
Headers:
  - X-Auth-Key: infinite@2022
  - Content-Type: multipart/form-data

Body (FormData):
  - student: {student_id}      // ID นักเรียน
  - subject: {subject_id}      // ID วิชา
  - date: "2025-12-13"         // วันที่ (Y-m-d)
  - stime: "10:48:57"          // เวลาเริ่ม (H:i:s)
  - hour: 1                    // จำนวนชั่วโมง
  - file: {signature_image}    // ไฟล์รูปลายเซ็น (PNG/JPG)

Response Success (200):
{
  "message": "Data Save Successfully",
  "file": "random_filename.png"
}

Response Error (400):
{
  "message": "No file found"
}
```

---

## 📦 Dependencies ที่ต้องใช้

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # HTTP Client
  http: ^1.1.0
  dio: ^5.4.0
  
  # Signature Pad
  signature: ^5.4.1
  # หรือ
  syncfusion_flutter_signaturepad: ^24.1.41
  
  # UI Components
  flutter_typeahead: ^5.2.0      # Autocomplete
  dropdown_search: ^5.0.6        # Dropdown with search
  
  # Date & Time
  intl: ^0.19.0
  
  # Image
  path_provider: ^2.1.1
  image: ^4.1.4
  
  # State Management (optional)
  provider: ^6.1.1
  # หรือ
  flutter_riverpod: ^2.4.9
  
  # Loading & Toast
  flutter_spinkit: ^5.2.0
  fluttertoast: ^8.2.4
  
  # Storage
  shared_preferences: ^2.2.2
```

---

## 🏗️ Project Structure

```
lib/
├── main.dart
├── config/
│   └── api_config.dart          # API configuration
├── models/
│   ├── student_model.dart
│   └── subject_model.dart
├── services/
│   └── api_service.dart         # API calls
├── screens/
│   └── attendance_screen.dart   # หน้าจอหลัก
├── widgets/
│   ├── signature_pad_widget.dart
│   ├── student_dropdown.dart
│   ├── subject_dropdown.dart
│   └── custom_button.dart
└── utils/
    └── date_formatter.dart
```

---

## 💻 Code Examples

### API Config
```dart
// lib/config/api_config.dart
class ApiConfig {
  static const String BASE_URL = 'http://34.87.33.138:81/api';
  static const String API_KEY = 'infinite@2022'; // API Key
  
  static const String SEARCH_STUDENT = '/searchstudent';
  static const String SEARCH_SUBJECT = '/searchsubject';
  static const String SAVE_HISTORIES = '/savehistories';
}
```

### Student Model
```dart
// lib/models/student_model.dart
class StudentModel {
  final int id;
  final String code;
  final String name;
  
  StudentModel({
    required this.id,
    required this.code,
    required this.name,
  });
  
  factory StudentModel.fromJson(Map<String, dynamic> json) {
    return StudentModel(
      id: json['id'],
      code: json['code'] ?? '',
      name: json['name'] ?? '',
    );
  }
  
  String get displayName => '$code - $name';
}
```

### Subject Model
```dart
// lib/models/subject_model.dart
class SubjectModel {
  final int id;
  final String title;
  
  SubjectModel({
    required this.id,
    required this.title,
  });
  
  factory SubjectModel.fromJson(Map<String, dynamic> json) {
    return SubjectModel(
      id: json['id'],
      title: json['title'] ?? '',
    );
  }
}
```

### API Service
```dart
// lib/services/api_service.dart
import 'dart:io';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/student_model.dart';
import '../models/subject_model.dart';

class ApiService {
  final Dio _dio = Dio();
  
  ApiService() {
    _dio.options.baseUrl = ApiConfig.BASE_URL;
    _dio.options.headers = {
      'X-Auth-Key': ApiConfig.API_KEY,
    };
  }
  
  // ดึงรายชื่อนักเรียน
  Future<List<StudentModel>> GetStudents() async {
    try {
      final response = await _dio.get(ApiConfig.SEARCH_STUDENT);
      List<dynamic> data = response.data;
      return data.map((json) => StudentModel.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to load students: $e');
    }
  }
  
  // ดึงรายชื่อวิชาตามนักเรียน
  Future<List<SubjectModel>> GetSubjects(int student_id) async {
    try {
      final response = await _dio.get(
        ApiConfig.SEARCH_SUBJECT,
        queryParameters: {'std': student_id},
      );
      List<dynamic> data = response.data;
      return data.map((json) => SubjectModel.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to load subjects: $e');
    }
  }
  
  // บันทึกข้อมูลการเข้าเรียน
  Future<bool> SaveHistory({
    required int student_id,
    required int subject_id,
    required String date,
    required String stime,
    required int hour,
    required File signature_file,
  }) async {
    try {
      FormData form_data = FormData.fromMap({
        'student': student_id,
        'subject': subject_id,
        'date': date,
        'stime': stime,
        'hour': hour,
        'file': await MultipartFile.fromFile(
          signature_file.path,
          filename: 'signature.png',
        ),
      });
      
      final response = await _dio.post(
        ApiConfig.SAVE_HISTORIES,
        data: form_data,
      );
      
      return response.statusCode == 200;
    } catch (e) {
      throw Exception('Failed to save history: $e');
    }
  }
}
```

### Main Screen Widget
```dart
// lib/screens/attendance_screen.dart
import 'package:flutter/material.dart';
import 'package:signature/signature.dart';
import 'dart:io';
import 'dart:typed_data';
import 'package:path_provider/path_provider.dart';
import 'package:intl/intl.dart';

class AttendanceScreen extends StatefulWidget {
  @override
  _AttendanceScreenState createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  final SignatureController _signature_controller = SignatureController(
    penStrokeWidth: 3,
    penColor: Colors.black,
    exportBackgroundColor: Colors.white,
  );
  
  StudentModel? _selected_student;
  SubjectModel? _selected_subject;
  DateTime _selected_date = DateTime.now();
  TimeOfDay _selected_time = TimeOfDay.now();
  int _selected_hour = 1;
  
  List<StudentModel> _students = [];
  List<SubjectModel> _subjects = [];
  bool _is_loading = false;
  
  final ApiService _api_service = ApiService();
  
  @override
  void initState() {
    super.initState();
    _LoadStudents();
  }
  
  Future<void> _LoadStudents() async {
    try {
      final students = await _api_service.GetStudents();
      setState(() => _students = students);
    } catch (e) {
      _ShowError('ไม่สามารถโหลดข้อมูลนักเรียนได้');
    }
  }
  
  Future<void> _LoadSubjects(int student_id) async {
    try {
      final subjects = await _api_service.GetSubjects(student_id);
      setState(() {
        _subjects = subjects;
        _selected_subject = null;
      });
    } catch (e) {
      _ShowError('ไม่สามารถโหลดข้อมูลวิชาได้');
    }
  }
  
  Future<void> _HandleSubmit() async {
    // Validation
    if (_selected_student == null) {
      _ShowError('กรุณาเลือกนักเรียน');
      return;
    }
    if (_selected_subject == null) {
      _ShowError('กรุณาเลือกวิชา');
      return;
    }
    if (_signature_controller.isEmpty) {
      _ShowError('กรุณาเซ็นลายเซ็น');
      return;
    }
    
    setState(() => _is_loading = true);
    
    try {
      // Export signature to file
      final Uint8List? signature_data = await _signature_controller.toPngBytes();
      if (signature_data == null) throw Exception('ไม่สามารถสร้างไฟล์ลายเซ็นได้');
      
      final temp_dir = await getTemporaryDirectory();
      final signature_file = File('${temp_dir.path}/signature.png');
      await signature_file.writeAsBytes(signature_data);
      
      // Format date and time
      final date_str = DateFormat('yyyy-MM-dd').format(_selected_date);
      final time_str = '${_selected_time.hour.toString().padLeft(2, '0')}:'
          '${_selected_time.minute.toString().padLeft(2, '0')}:00';
      
      // Save to API
      final is_success = await _api_service.SaveHistory(
        student_id: _selected_student!.id,
        subject_id: _selected_subject!.id,
        date: date_str,
        stime: time_str,
        hour: _selected_hour,
        signature_file: signature_file,
      );
      
      if (is_success) {
        _ShowSuccess('บันทึกข้อมูลสำเร็จ');
        _HandleClearAll();
      }
    } catch (e) {
      _ShowError('เกิดข้อผิดพลาด: $e');
    } finally {
      setState(() => _is_loading = false);
    }
  }
  
  void _HandleClearSignature() {
    _signature_controller.clear();
  }
  
  void _HandleClearAll() {
    setState(() {
      _selected_student = null;
      _selected_subject = null;
      _subjects = [];
      _selected_date = DateTime.now();
      _selected_time = TimeOfDay.now();
      _selected_hour = 1;
    });
    _signature_controller.clear();
  }
  
  void _ShowError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }
  
  void _ShowSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.green),
    );
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Color(0xFF4CAF50),
        title: Row(
          children: [
            Image.asset('assets/logo.png', height: 40),
            SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('InfiniteBrain', style: TextStyle(fontSize: 16)),
                Text('โปรแกรมบันทึกเวลาเรียน', style: TextStyle(fontSize: 12)),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(icon: Icon(Icons.more_vert), onPressed: () {}),
        ],
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            // Signature Pad
            Container(
              height: 150,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Signature(
                controller: _signature_controller,
                backgroundColor: Colors.white,
              ),
            ),
            SizedBox(height: 12),
            
            // Clear Signature Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _HandleClearSignature,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Color(0xFF4CAF50),
                  padding: EdgeInsets.symmetric(vertical: 14),
                ),
                child: Text('ลบลายเซ็นต์', style: TextStyle(fontSize: 16)),
              ),
            ),
            SizedBox(height: 16),
            
            // Student Dropdown
            // ... (implement dropdown widgets)
            
            // Subject Dropdown
            // ...
            
            // Date Picker
            // ...
            
            // Time Picker
            // ...
            
            // Hour Dropdown
            // ...
            
            SizedBox(height: 20),
            
            // Clear All Button
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                onPressed: _HandleClearAll,
                style: OutlinedButton.styleFrom(
                  foregroundColor: Color(0xFF4CAF50),
                  side: BorderSide(color: Color(0xFF4CAF50)),
                  padding: EdgeInsets.symmetric(vertical: 14),
                ),
                child: Text('ล้างข้อมูล', style: TextStyle(fontSize: 16)),
              ),
            ),
            SizedBox(height: 12),
            
            // Submit Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _is_loading ? null : _HandleSubmit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Color(0xFF4CAF50),
                  padding: EdgeInsets.symmetric(vertical: 14),
                ),
                child: _is_loading
                    ? CircularProgressIndicator(color: Colors.white)
                    : Text('บันทึกข้อมูล', style: TextStyle(fontSize: 16)),
              ),
            ),
          ],
        ),
      ),
    );
  }
  
  @override
  void dispose() {
    _signature_controller.dispose();
    super.dispose();
  }
}
```

---

## ✅ Features Checklist

- [ ] แสดง Logo และชื่อแอพใน AppBar
- [ ] Signature Pad สำหรับวาดลายเซ็น
- [ ] ปุ่มลบลายเซ็น
- [ ] Dropdown เลือกนักเรียน (พร้อมค้นหา)
- [ ] Dropdown เลือกวิชา (โหลดตามนักเรียนที่เลือก)
- [ ] Date Picker เลือกวันที่
- [ ] Time Picker เลือกเวลา
- [ ] Dropdown เลือกจำนวนชั่วโมง
- [ ] ปุ่มล้างข้อมูลทั้งหมด
- [ ] ปุ่มบันทึกข้อมูล พร้อม validation
- [ ] แสดง Loading indicator ขณะบันทึก
- [ ] แสดง Error/Success message
- [ ] รองรับ API Key authentication (Header: `X-Auth-Key`)

---

## 🔐 Security Notes

1. **API Key:** เก็บ API Key ใน secure storage หรือ environment variable
2. **HTTPS:** แนะนำให้ใช้ HTTPS แทน HTTP ใน production
3. **Input Validation:** ตรวจสอบข้อมูลก่อนส่งไป API

---

## 📝 Additional Notes

- รูปภาพลายเซ็นจะถูกบันทึกไว้ที่ server ใน folder `public/form_images`
- API ใช้ middleware `auth.apikey` ต้องส่ง `X-Auth-Key: infinite@2022` ใน header ทุกครั้ง
- เวลาสิ้นสุด (etime) จะถูกคำนวณอัตโนมัติจาก stime + hour

---

## 🚀 Getting Started

```bash
# สร้างโปรเจค Flutter ใหม่
flutter create infinitebrain_attendance

# เข้าไปในโฟลเดอร์โปรเจค
cd infinitebrain_attendance

# ติดตั้ง dependencies
flutter pub get

# รันแอพ
flutter run
```

---

**สร้างโดย:** Cascade AI  
**วันที่:** December 13, 2025
