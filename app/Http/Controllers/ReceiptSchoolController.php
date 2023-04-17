<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use File;
use Illuminate\Support\Str;
use App\Models\FileUpload;
use Illuminate\Support\Facades\Auth;
use App\Models\Receipt;
use App\Models\Receipt_School;
use App\Models\Student;
use App\Models\ReceiptDetail;
use App\Models\Subject;
use App\Models\Level;
use App\Models\SchoolPrice;
use App\Models\SubjectPrice;
use App\Models\Receipt_School_RunningNumber;
class ReceiptSchoolController extends Controller
{
    public function index(Request $request)
    {
    
        //
       
    // $sss ="";

    // foreach ($student as $keys) {
    //     $sss = $keys;
    // }
    // $stu = Student::where('students.id',$sss)->orderBy("id", "asc")->get();

    //    echo "<pre>";
    //    print_r($stu);
    //    echo "</pre>";
    //    exit();

    if ($request->ajax()) {
            //sleep(2);
            // $pid = Receipt_School::select(
            //     "receipt_school.pid as pid",
         
            //     )->get();
            //     $student = explode('|',$pid);
         
               
            $receipt = Receipt_School::select(
                "receipt_school.type as type",
                "receipt_school.id as id",
                "receipt_school.submission_date as submission_date",
                "receipt_school.street_address as street_address",
                "receipt_school.postcode as postcode",
                "receipt_school.name_parent as name_parent",
                "receipt_school.mobile as mobile",
                "receipt_school.receipt_number as receipt_number",
                "receipt_school.pid as pid",
            )
            
                //->join("students", "students.id", "=", "receipts.student_id")
                //->join("subjects", "subjects.id", "=", "receipts.subject_id")
                //->join("levels", "levels.id", "=", "receipts.level_id")

                ->orderBy("receipt_school.id", "desc")
                ->get();
                
                // echo "<pre>";
                // print_r($receipt);
                // echo "</pre>";
                // exit();
            //print_r($levels);
            //exit(); 
           

            return datatables()->of($receipt)
            ->editColumn('pid', function ($row) {
                $student = "";
                $stu = explode('|',$row->pid);
                if($row->pid!=""){

                
                foreach($stu as $stus){
                    $stus = Student::where('id', $stus)->get();
                    $student .= $stus[0]->first_name." ".$stus[0]->last_name.' ';

                }
                
                }
                return $student;
            })
            
                ->editColumn('type', function (Receipt_School $receipt) {
                    if ($receipt->type == 2) {
                        $mes = "ใบลดหนี้";
                        $text = "Credit Note";
                    } else {

                        $mes = "ใบกำกับภาษี/ใบเสร็จรับเงิน";
                        $text = "Tax Invoice/Receipt";
                    }
                    return $mes;
                })

                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="receipts/show/view/' . $row->id . '" class="btn btn-xs btn-success btn-view" id="ViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View </a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    //$html .= '<a href="#" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        //get subject
        //$subject = Subject::orderBy("id", "asc")->get();

        //get student
        //$student = Student::orderBy("name", "asc")->get();
        // print_r($level) ;

       
        $student = Student::orderBy("first_name", "asc")->get();
        $subject = Subject::orderBy("id", "asc")->get();


        return view('receipt_school.receipt')->with(['data2' => $student])->with(['data3' => $subject]);
      

    }
    public function search($sub_id)
    {
        // echo $sub_id;
        $data = Level::where('levels.subject',$sub_id)
        ->orderBy("subject", "asc")->get();
        $select_list = "";

        foreach ($data as $key) {

            $select_list .= '<option value="' . $key->id . '" > (' . $key->level_sort . ') ' . $key->title . '</option>';
        }

        return response()->json([
            'html' =>  $select_list
        ]);
    }
    public function searchs($sub_id,$level_id,$week,$price,$qty,$discount,$book_price,$type){
        
        $select_list = "";
        if($price==1){
            $data = SubjectPrice::Where('subject_prices.subject_id',$sub_id)->Where('subject_prices.type',$type)->Where('subject_prices.level_id',$level_id)->Where('subject_prices.week',$week)->get();
            foreach ($data as $key) {

                $select_list .= $key->old_price;
            }
        }else{
            $data = SubjectPrice::Where('subject_prices.subject_id',$sub_id)->Where('subject_prices.type',$type)->Where('subject_prices.level_id',$level_id)->Where('subject_prices.week',$week)->get();
            foreach ($data as $key) {

                $select_list .= $key->price;
            }
          
        }
        $output = "";
        $output .= '<input type="text" id="pp" name="pp" class="form-control has-feedback-left" value="'.(($select_list+$book_price)*$qty)-$discount.'" >
        ';

        return response()->json([
            'html' =>  $output
        ]);

    }
    public function sumbook($sub_id,$level_id,$book){
        $data = SubjectPrice::Where('subject_prices.subject_id',$sub_id)->Where('subject_prices.level_id',$level_id)->get();
        $select_list = "";
        foreach ($data as $key) {

            $select_list = $key->book_price;
        }
        $output = "";
        $output = '<input type="text" id="book_price" name="book_price" class="form-control has-feedback-left" value="'.$select_list*$book.' "readonly >
        ';

        return response()->json([
            'html' =>  $output
        ]);
    }
    public function store(Request $request)
    {

        //
        $validator =  Validator::make($request->all(), [
            // 'type' => 'required|string',
            'submission_date' => 'required|string',
            
            'street_address' => 'required|string',
            //'city' => 'required|string',
            //'province' => 'required|string',
            'postcode' => 'required|string',
            'name_parent' => 'required|string',
            'mobile' => 'required|string',
            // 'subject' => 'required|string',
            // 'level' => 'required|string',
            // 'week' => 'required|string',
            // 'qty' => 'required|string',
            // 'book' => 'required|string',
            // 'bookprice' => 'required|string',
            // 'discount' => 'required|string',
            // 'price' => 'required|string',
            // 'total' => 'required|string',

            // 'file_att' => 'required|string

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        

        $st = $request->get('student');

        $stu = "";
        foreach ($st as $sss) {
            //echo $filea;
            $stu.= $sss . "|";
        }
       
        $stu = rtrim($stu, "|");

        $data = new Receipt_School([
            'submission_date' => $request->get('submission_date'),
            'street_address' => $request->get('street_address'),
            'postcode' => $request->get('postcode'),
            'mobile' => $request->get('mobile'),
            'pid' => $stu,
            'name_parent' => $request->get('name_parent'),
            'type'=>0,
            // 'sid' => $request->get('student'),

        ]);


        $data->save();
        $rid = $data->id;
        // print_r($request->get('receipt'));

        foreach ($request->get('receipt') as $value) {
            $rnumber = Receipt_School_RunningNumber::generate($value['subject']);

            $data2 = new SchoolPrice([
                'subject' => $value['subject'],
                'level' => $value['level'],
                'week' => $value['week'],
                'qty' => $value['qty'],
                'book' => $value['book'],
                'bookprice' => $value['book_price'],
                'discount' => $value['discount'],
                'price' => $value['price'],
                'total' => $value['pp'],
                'rid' => $rid,
                'type'=> $value['type'],
                'receipt_number'=>$rnumber,

            ]);
            $data2->save();
        
        }
        

        return response()->json(['success' => 'Receive Add successfully']);

    }
}
