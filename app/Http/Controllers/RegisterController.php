<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Student;
use App\Models\Register;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FileUpload;
// use App\Models\Receipt_RunningNumber;
use App\Models\RunningNumber;

class RegisterController extends Controller
{ 
    
    public function register()
    {
        return view("register.register");
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        //
        $validator =  Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9 ]+$/',
                'min:4',             // must be at least 10 characters in length
                // 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
                // 'regex:/[a-z]/',      // must contain at least one lowercase letter
                // 'regex:/[A-Z]/',      // must contain at least one uppercase letter
                // 'regex:/[0-9]/',      // must contain at least one digit
                // 'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'parent' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'postcode' => 'required|string',
            // 'firstname' => 'required|string',
            // 'lastname' => 'required|string',
            // 'nickname' => 'required|string',
            // 'birthdate' => 'required|string',
            // 'schoolname' => 'required|string',
            // 'grade' => 'required|string',
            
        ]);
        
        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()->all()]);
            return back()->withErrors(['errors' => $validator->errors()->all()]);
        }
        $filesa = $request->get('imgFiles');
        // $filesb = $request->get('imgFiles2');

        // print_r($filesa);
        // print_r($filesb);
        // exit();
        $fileas = [];
        foreach ($filesa as $filea) {
            //echo $filea;
            $oldfile = FileUpload::where('oldname', $filea)->get();
            $fileas[] .= $oldfile[0]->filename ;
            // $file2 = $oldfile[1]->filename ;
            File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);
            FileUpload::where('filename', $oldfile[0]->filename)->delete();
        } 
        // $fileas = rtrim($fileas, "|");
        
//         print_r($fileas) ;
// //         echo $filea;

//         exit();
        
        // $rnumber = RunningNumber::generate(new Register,'code',4,"");
        

        $registerForm = new Register([
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'name_parent' => $request->get('parent'),
            'mobile' => $request->get('phone'),
            'street_address' => $request->get('address'),
            'postcode' => $request->get('postcode'),
            'code'=> "",

        ]);
        // echo $request->get('val');
        // exit();
        // $error = 1 ;
        for($x=0; $x<$request->get('val');$x++){
            // $rnumber1 = RunningNumber::generate(new Student,'code',4,"");
            $rcnumber = RunningNumber::generate();
            $data = [];
            $data[]=[
                'code'=> $rcnumber ,
                'first_name' => $request->get('firstname')[$x],
                'last_name' => $request->get('lastname')[$x],
                'nick_name' => $request->get('nickname')[$x],
                'birth_date' => $request->get('birthdate')[$x],
                'school_name' => $request->get('schoolname')[$x],
                'grade' => $request->get('grade')[$x],
                'mobile'=> "" ,
                'photo_att' => $fileas[$x],
            ];
            // $data = [];

            // print_r($data);
            Student::upsert($data,['code','first_name','last_name','nick_name'
            ,'birth_data','school_name','grade','photo_att']);
        }
        // if(count($data)>0){
        //     $error = '';
        // }
       
        // $student = new Student([
        //     'code'=> $rnumber1 ,
        //     'first_name' => $request->get('firstname'),
        //     'last_name' => $request->get('lastname'),
        //     'nick_name' => $request->get('nickname'),
        //     'birth_date' => $request->get('birthdate'),
        //     'school_name' => $request->get('schoolname'),
        //     'grade' => $request->get('grade'),
        //     'mobile'=> "" ,
        //     'photo_att' =>"",
        // ]);
        // $student->save();
        $registerForm->save();
        return redirect('/register/complete');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Register  $register
     * @return \Illuminate\Http\Response
     */
    public function show(Register $register)
    {
        //
            return view("register.complete");
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Register  $register
     * @return \Illuminate\Http\Response
     */
    public function edit(Register $register)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Register  $register
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Register $register)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Register  $register
     * @return \Illuminate\Http\Response
     */
    public function destroy(Register $register)
    {
        //
    }
    
}
