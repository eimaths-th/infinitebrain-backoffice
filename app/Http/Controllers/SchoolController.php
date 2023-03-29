<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Course;
use App\Models\Level;
use App\Models\Subject;
use App\Models\ParentRegister;
use App\Models\Student;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$id = Auth::guard('parent')->id();
        //Auth::guard('parent')->user()->email;
        $users = Auth::guard('parent')->user();
        $id = $users->id;
        $parent = ParentRegister::find($id);


        $parent_html = '<div class="container rounded bg-white mt-5 mb-5">
        <div class="row">
            <div class="col-md-4 border-right">
                <div
                    class="d-flex flex-column align-items-center text-center p-3 py-5">
                    <img class="rounded-circle mt-5" width="150px"
                        src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg"><span
                        class="font-weight-bold">' . ucfirst($parent->name_parent) . '</span><span
                        class="text-black-50">' . $parent->email . '</span><span>
                    </span>
                </div>
            </div>
            <div class="col-md-7 border-right">
                <div class="p-3 py-5">
                    <div
                        class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-right">ข้อมูลผู้ปกครอง</h4>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12"><label
                                class="labels">รหัสผู้ปกครอง</label><input type="text"
                                class="form-control"
                                value="' . $parent->code . '" readonly></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12"><label
                                class="labels">ชื่อ - นามสกุล</label><input type="text"
                                class="form-control" placeholder="ชื่อ - นามสกุล"
                                value="' . $parent->name_parent . '"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12"><label class="labels">เบอร์มือถือ
                               </label><input type="text"
                                class="form-control"
                                placeholder="เบอร์มือถือ"
                                value="' . $parent->mobile . '"></div>
                        <div class="col-md-12"><label class="labels">ที่อยู่
                                </label><input type="text"
                                class="form-control"
                                placeholder="ที่อยู่"
                                value="' . $parent->street_address . '"></div>
                        <div class="col-md-12"><label
                                class="labels">รหัสไปรษณีย์</label><input
                                type="text" class="form-control"
                                placeholder="รหัสไปรษณีย์"
                                value="' . $parent->postcode . '"></div>
                        <div class="col-md-12"><label class="labels">Email
                                </label><input type="text"
                                class="form-control"
                                placeholder="enter email id" value="' . $parent->email . '" readonly>
                        </div>
                        <div class="col-md-12"><label class="labels">Password
                        </label><input type="password"
                        class="form-control"
                        placeholder="enter password" value="">
                         </div>
                
                    </div>
                    <div class="mt-5 text-center"><button
                            class="btn btn-primary profile-button"
                            type="button">Save Profile</button></div>
                </div>
            </div>
        </div>
    </div>
            ';
        $student_html = "";
        $student = Student::where([['pid', $id]])
            ->orderBy("id", "asc")
            ->get();


        foreach ($student as $stu_result) {
            $photo = url('/') . '/receipt_form_images/' . $stu_result->photo_att;

            $student_html .= '<div class="container rounded bg-white mt-5 mb-5">
        <div class="row">
            <div class="col-md-4 border-right">
                <div
                    class="d-flex flex-column align-items-center text-center p-3 py-5">
                    <img class="rounded-circle mt-5" width="150px"
                        src="' . $photo . '"><span
                        class="font-weight-bold">' . ucfirst($stu_result->name) . '</span><span
                        class="text-black-50">' . $stu_result->code . '</span><span>
                    </span>
                </div>
            </div>
            <div class="col-md-7 border-right">
                <div class="p-3 py-5">
                    <div
                        class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-right">ข้อมูลนักเรียน</h4>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6"><label
                                class="labels">รหัสนักเรียน</label><input type="text"
                                class="form-control"
                                value="' . $stu_result->code . '" readonly></div>
                                <div class="col-md-6"><label
                                class="labels">เพศ</label><input type="text"
                                class="form-control"
                                value="' . $stu_result->sex . '" ></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6"><label
                                class="labels">ชื่อ </label><input type="text"
                                class="form-control" placeholder="ชื่อ"
                                value="' . $stu_result->first_name . '"></div>
                                <div class="col-md-6"><label
                                class="labels">นามสกุล </label><input type="text"
                                class="form-control" placeholder="นามสกุล"
                                value="' . $stu_result->last_name . '"></div>
                    </div>
                    <div class="row mt-3">
                                <div class="col-md-6">
                                <label
                                class="labels">ชื่อเล่น </label><input type="text"
                                class="form-control" placeholder="ชื่อ"
                                value="' . $stu_result->nick_name . '"></div>
                                <div class="col-md-6"><label
                                class="labels">วันเกิด </label><input type="text"
                                class="form-control" placeholder="นามสกุล"
                                value="' . $stu_result->birth_date . '"></div>
                
                    </div>
                    <div class="row mt-3">
                                <div class="col-md-6">
                                <label
                                class="labels">ชื่อโรงเรียน </label><input type="text"
                                class="form-control" placeholder="ชื่อ"
                                value="' . $stu_result->school_name . '"></div>
                                <div class="col-md-6"><label
                                class="labels">ระดับชั้น </label><input type="text"
                                class="form-control" placeholder="นามสกุล"
                                value="' . $stu_result->grade . '"></div>
                
                    </div>
                    <div class="mt-5 text-center"><button
                            class="btn btn-primary profile-button"
                            type="button">Save Profile</button></div>
                </div>
            </div>
        </div>
    </div>
            ';

            $course = Course::where([['student_id', $stu_result->id]])
            ->orderBy("id", "asc")
            ->get();

            $course_html = '';
            if ($course) {

                foreach ($course as $course_data) {
                    //$course_html = CourseController::show('html','th',$course_data->id);
                    $course_html = (new CourseController)->show('html', 'th', $course_data->id);
                }
            }

        }

        return view('register.home')->with(['parent_html' =>  $parent_html])
            ->with(['student_html' =>  $student_html])
            ->with(['student_chart' =>  $course_html]);
    }
}
