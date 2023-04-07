<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\Comment;

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
        if (!Auth::guard('parent')->check()) {
            return redirect("/login/parent/");
        }
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
                                class="form-control" name="code"
                                value="' . $parent->code . '" readonly></div>
                    </div>
                 
                    <div class="row mt-2">
                        <div class="col-md-12"><label
                                class="labels">ชื่อ - นามสกุล</label><input type="text"
                                class="form-control" placeholder="ชื่อ - นามสกุล" name="name_parent"
                                value="' . $parent->name_parent . '">
                                <input type="text"
                                class="form-control" placeholder="ชื่อ" id="id" name="id"
                                value="' . $parent->id . '" hidden>
                                </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12"><label class="labels">เบอร์มือถือ
                               </label><input type="text"
                                class="form-control"
                                placeholder="เบอร์มือถือ" name="mobile"
                                value="' . $parent->mobile . '"></div>
                        <div class="col-md-12"><label class="labels">ที่อยู่
                                </label><input type="text"
                                class="form-control"
                                placeholder="ที่อยู่" name="street_address"
                                value="' . $parent->street_address . '"></div>
                        <div class="col-md-12"><label
                                class="labels">รหัสไปรษณีย์</label><input
                                type="text" class="form-control"
                                placeholder="รหัสไปรษณีย์" name="postcode"
                                value="' . $parent->postcode . '"></div>
                        <div class="col-md-12"><label class="labels">Email
                                </label><input type="text"
                                class="form-control" name="email"
                                placeholder="enter email id" value="' . $parent->email . '" readonly>
                        </div>
                        <div class="col-md-12"><label class="labels">Password
                        </label><input type="password"
                        class="form-control"  name="password"
                        placeholder="enter password" value="">
                         </div>
                
                    </div>
                    <div class="mt-5"><button
                            class="btn btn-primary profile-button"
                            type="submit">บันทึกข้อมูล</button></div>
                </div>
            </div>
        </div>
    </div>
            ';
        $course_html = '';
        $student_html = "";
        $student = Student::where([['pid', $id]])
            ->orderBy("id", "asc")
            ->get();
          
            $i = 1;

        foreach ($student as $stu_result) {

          

            $select_grade = "";
            $grade = array(
                "Kindergarten" => "Kindergarten", "Grade 1" => "Grade 1", "Grade 2" => "Grade 2", "Grade 3" => "Grade 3", "Grade 4" => "Grade 4", "Grade 5" => "Grade 5", "Grade 6" => "Grade 6", "Grade 7" => "Grade 7", "Grade 8" => "Grade 8", "Grade 9" => "Grade 9", "Grade 10" => "Grade 10", "Grade 11" => "Grade 11", "Grade 12" => "Grade 12"
            );
            foreach ($grade as $key_grade => $value_grade) {
                if ($stu_result->grade == $key_grade) {

                    $select_grade .= '<option value="' . $key_grade . '" selected>' . $value_grade . '</option>';
                } else {
                    $select_grade .= '<option value="' . $key_grade . '" >' . $value_grade . '</option>';
                }
            }


            $select_sex = "";
            $sex = array("Male" => "Male", "Female" => "Female");
            foreach ($sex as $key_sex => $value_sex) {
                if ($stu_result->sex == $key_sex) {

                    $select_sex .= '<option value="' . $key_sex . '" selected>' . $value_sex . '</option>';
                } else {
                    $select_sex .= '<option value="' . $key_sex . '" >' . $value_sex . '</option>';
                }
            }

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
                        <h4 class="text-right">ข้อมูลนักเรียน'.$i.'</h4>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6"><label
                                class="labels">รหัสนักเรียน </label><input type="text"
                                class="form-control"
                                value="' . $stu_result->code . '" readonly>
                        </div>
                        <div class="col-md-6"><label
                                class="labels">เพศ</label>
                                <select class="form-control" id="sex[]" name="sex[]">' .
                                 $select_sex . '
                                </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6"><label
                                class="labels">ชื่อ </label><input type="text"
                                class="form-control" placeholder="ชื่อ" id="first_name[]" name="first_name[]"
                                value="' . $stu_result->first_name . '">
                                <input type="text"
                                class="form-control" placeholder="ชื่อ" id="id[]" name="id[]"
                                value="' . $stu_result->id . '" hidden></div>
                                <div class="col-md-6"><label
                                class="labels">นามสกุล </label><input type="text"
                                class="form-control" placeholder="นามสกุล" id="last_name[]" name="last_name[]"
                                value="' . $stu_result->last_name . '"></div>
                    </div>
                    <div class="row mt-3">
                                <div class="col-md-6">
                                <label
                                class="labels">ชื่อเล่น </label><input type="text"
                                class="form-control" placeholder="ชื่อ" id="nick_name[]" name="nick_name[]"
                                value="' . $stu_result->nick_name . '"></div>
                                <div class="col-md-6"><label
                                class="labels">วันเกิด </label><input type="text"
                                class="form-control AddDate" placeholder="วันเกิด" name="birth_date[]"
                                value="' . $stu_result->birth_date . '"></div>
                                
                
                    </div>
                    <div class="row mt-3">
                                <div class="col-md-6">
                                <label
                                class="labels">ชื่อโรงเรียน </label><input type="text"
                                class="form-control" placeholder="ชื่อ" id="school_name[]" name="school_name[]"
                                value="' . $stu_result->school_name . '"></div>
                                <div class="col-md-6"><label
                                class="labels">ระดับชั้น</label>
                                <select class="form-control" id="grade[]" name="grade[]">' .
                                  $select_grade . '
                                </select>
                        </div>
                
                    </div>
                    <div class="mt-5 "><button
                            class="btn btn-primary profile-button"
                            type="submit">บันทึกข้อมูล</button></div>
                </div>
            </div>
        </div>
    </div>
            ';
            
            if ($student) {
                $course[] = Course::where([['student_id', $stu_result->id]])
                    ->orderBy("id", "asc")
                    ->get();
            }


            if ($course) {
                $course_html = array();
                //$i = 0;
                foreach ($course as $course_data) {
                    foreach ($course_data as $course_data_val) {
                        //$course_html = CourseController::show('html','th',$course_data->id);
                        $course_html[] = (new CourseController)->show('html', 'th', $course_data_val->id);
                        //$i++;
                    }
                }
            }
            $i++;

        }
     $comment_html = "";
        foreach ($student as $stu_result) {

   
        // echo $stu_result;
        
        $comment = Comment::where([['sid', $stu_result->id]])
            ->orderBy("created_at", "desc")
            ->get();
        // $comment = Comment::orderBy("id", "desc")
        // ->get();
        

        
        foreach ($comment as $comment_result) {
            // <div class="col-md-4 border-right">
            //     <div class="d-flex flex-column align-items-center text-center p-3 py-5">
            //         <span class="time"><i class="fas fa-clock"></i>   ' .date_format($comment_result->created_at,"d M Y  H:i:s "). '</span>
            //         <h3 class="timeline-header"><a href="#">'.$comment_result->user.'</a>
            //            </h3>
            //         <div class="timeline-body">
            //         '.$comment_result->comment.'
            //         </div>
            //     </div>
            // </div>
            $comment_html2="";
            $pic = explode('|',$comment_result->photo);
            foreach($pic as $pics){
                $comment_html2 .= '
     
            
    <img src="'.url('/').'/comments_images/'. $pics .'" height="100" width="100">
   
                ';
            }
            $img = "";
            $cm ="";

            if($comment_result->photo != ""){
                $img.='
                <div>
                <i class="fa fa-camera bg-purple"></i>
                <div class="timeline-item">
                    <span class="time"><i class="fas fa-clock"></i>' .date_format($comment_result->created_at,"H:i "). '</span>
                    <h3 class="timeline-header"><a href="#">'.$comment_result->user.'</a>        
                        </h3>
                    <div class="timeline-body">
                    '.$comment_html2.'
                    </div>
                </div>
            </div>
                ';
            }else{
                $img = '';
            }
            if($comment_result->comment != ""){
                $cm .= '
                <div>
                    <i class="fas fa-envelope bg-blue"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="fas fa-clock"></i>' .date_format($comment_result->created_at,"H:i "). '</span>
                        <h3 class="timeline-header"><a href="#">'.$comment_result->user.'</a>
                            </h3>
                        <div class="timeline-body">
                        <span style="color:black; font-size:16px;">'.$comment_result->comment.'</span>
                        </div>
                        
                    </div>
                </div>
                ';
            }
            $comment_html .= '

            <div class="tab-pane " id="custom-tabs-three-settings" role="tabpanel"
            aria-labelledby="custom-tabs-three-settings-tab">
            <div class="timeline">

            <div class="time-label">
            <span  style="background-color:black;color:gold !important; font-size:20px;             box-shadow: 5px 5px 5px grey;
            ">            '.$stu_result->first_name.'  '.$stu_result->last_name.'            </span>
        </div>
                <div class="time-label">
                    <span class="bg-green" style=" box-shadow: 5px 5px 5px grey;">' .date_format($comment_result->created_at,"d M Y "). '</span>
                </div>

                '.$cm.'
                

               '.$img.'


                </div>
                </div>
                '; 
               

        }
        
    }

        return view('register.home')->with(['parent_html' =>  $parent_html])
            ->with(['student_html' =>  $student_html])
            ->with(['comment_html' =>  $comment_html])
            ->with(['student_chart' =>  $course_html]);
    }

    public function update(Request $request)
    {
        // $validator =  Validator::make($request->all(),[
        //     'sex' => 'required|string',
        //     'first_name' => 'required|string',
        //     'last_name' => 'required|string',
        //     'nick_name' => 'required|string',
        //     'birth_date' => 'required|string',
        //     'school_name' => 'required|string',
        //     'grade' => 'required|string',

        // ]); 
        // print_r($request);
        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()->all()]);
        // }
        for ($x = 0; $x < count($request->get('id')); $x++) {
            // $data = [];
            // $data[$x] = [
            //     'first_name' => $request->get('first_name')[$x],
            //     'last_name' => $request->get('last_name')[$x],
            //     'nick_name' => $request->get('nick_name')[$x],
            //     'birth_date' => $request->get('birth_date')[$x],
            //     'school_name' => $request->get('school_name')[$x],
            //     'grade' => $request->get('grade')[$x],
            //     'sex' => $request->get('sex')[$x],
                
            // ];
            $id=[];
            $id[$x] = $request->get('id')[$x];
            
            // print_r($request['birth_date']);
            // exit();

            // echo "<pre>";
            // print_r($request->all());
            // echo "</pre>";
            // exit();

            Student::find($id[$x])
            ->update([
                'first_name'=>$request['first_name'][$x],
                'last_name'=>$request['last_name'][$x],
                'nick_name'=>$request['nick_name'][$x],
                'birth_date'=>$request['birth_date'][$x],
                'school_name'=>$request['school_name'][$x],
                'grade'=>$request['grade'][$x],
                'sex'=>$request['sex'][$x],

            ]);
        }

        // print_r($id);
        // exit();
        // Student::upsert($data, [
        //     'code', 'first_name', 'last_name', 'nick_name', 'birth_data', 'school_name', 'grade', 'photo_att', 'sex', 'pid'
        // ]);

        // $id[] = $request->get('id');
        // Student::find($id)->update($request->all());
        
        // return response()->json(['success' => 'Parent updated successfully']);
        // return redirect('/parent');
        return back()->with(['success' => 'Student updated successfully']);

        // return redirect('/parent')->with('success', 'Student updated successfully.');

    
}
function updateParent(Request $request){
    // $validator =  Validator::make($request->all(),[
    //         'name_parent' => 'required|string',
    //         'mobile' => 'required|string',
    //         'street_address' => 'required|string',
    //         'postcode' => 'required|string',
    //         // 'password' => [
    //         //     'required',
    //         //     'string',
    //         //     'regex:/^[a-zA-Z0-9 ]+$/',
    //         //     'min:8',            

    //         // ],
    //     ]); 
        $password =  Hash::make($request->get('password'));

        // print_r($request);

        
        $id=$request->get('id');

        if($request->get('password')== ""){
            ParentRegister::find($id)
            ->update([
                'name_parent'=>$request['name_parent'],
                'mobile'=>$request['mobile'],
                'street_address'=>$request['street_address'],
                'postcode'=>$request['postcode'],
       
    
            ]);
        }else{
            $validator =  Validator::make($request->all(),[
                'password' => [
                        'string',
                        'regex:/^[a-zA-Z0-9 ]+$/',
                        'min:8',            
        
            ],
        ]); 
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        ParentRegister::find($id)
            ->update([
                'name_parent'=>$request['name_parent'],
                'mobile'=>$request['mobile'],
                'street_address'=>$request['street_address'],
                'postcode'=>$request['postcode'],
                'password'=>$password,
       
    
            ]);
        }
   

//                 echo "<pre>";
// print_r($request->all());
// echo "</pre>";
// exit();
        // ParentRegister::find($id)->update($request->all());
        
        return back()->with(['success' => 'Parent updated successfully']);

} 
}
