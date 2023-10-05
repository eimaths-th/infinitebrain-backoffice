<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ParentRegister;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportCustomer implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $pid = 0;
        if (!isset($row[4])) {
            $row[4]='';
        }
        if (isset($row[5])) {
            //return null;
            $user = ParentRegister::where('name_parent', '=', $row[5])->first();
            if ($user === null) {
                // user doesn't exist
                $registerForm = new ParentRegister([
                    'email' => $row[6],
                    //'password' => Hash::make($request->get('password')),
                    'name_parent' => $row[5],
                    'mobile' => $row[4],
                    'street_address' => $row[7],
                    'postcode' => $row[8],
                    'code' => $row[0],

                ]);

                $registerForm->save();
                $pid = $registerForm->id;
            } else {
                $page = ParentRegister::find($user->id);

                // Make sure you've got the Page model
                if ($page) {
                    $page->email = $row[6];
                    $page->name_parent = $row[5];
                    $page->mobile = $row[4];
                    $page->street_address = $row[7];
                    $page->postcode = $row[8];
                    $page->code = $row[0];
                    $page->save();
                }
                $pid = $user->id;
            }
        }



        $student = Student::where('first_name', '=', $row[1])->where('last_name', '=', $row[2])->first();
        if ($student === null) {
            return new Student([
                //
                'code' => $row[0],
                'first_name' => $row[1],
                'last_name' => $row[2],
                'nick_name' => $row[3],
                'mobile' => $row[4],
                //'grade' => $row[0],
                'pid' => $pid,
            ]);
        } else {
              $stu = Student::find($student->id);

            // Make sure you've got the Page model
            if ($stu) {
                //$stu->code = $row[0];
                //$stu->first_name = $row[1];
                //$stu->last_name = $row[2];
                //$stu->nick_name = $row[3];
                //$stu->mobile = $row[4];
                $stu->pid = $pid;
                $stu->save();
            }

            return;


        }
    }
}
