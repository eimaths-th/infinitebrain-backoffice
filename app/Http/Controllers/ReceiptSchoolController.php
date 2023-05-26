<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
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
use App\Models\ParentRegister;
use App\Models\Receipt_School_RunningNumber;

class ReceiptSchoolController extends Controller
{

    const BAHT_TEXT_NUMBERS = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
    const BAHT_TEXT_UNITS = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
    const BAHT_TEXT_ONE_IN_TENTH = 'เอ็ด';
    const BAHT_TEXT_TWENTY = 'ยี่';
    const BAHT_TEXT_INTEGER = 'ถ้วน';
    const BAHT_TEXT_BAHT = 'บาท';
    const BAHT_TEXT_SATANG = 'สตางค์';
    const BAHT_TEXT_POINT = 'จุด';
    // const sfielda = array('2' => 'GMEC', '4' => 'IMOCSEA', '5' => 'INFINITE Spelling Bee', '6' => 'GELOSEA', '7' => 'LIMOC', '8' => 'STEM', '9' => 'OLPCS');
    // const sfieldas = array('2' => 'G', '4' => 'J', '5' => 'I', '6' => 'L', '7' => 'M', '8' => 'N', '9' => 'O');
    const thmonth = array(
        "",
        "มกราคม",
        "กุมภาพันธ์",
        "มีนาคม",
        "เมษายน",
        "พฤษภาคม",
        "มิถุนายน",
        "กรกฎาคม",
        "สิงหาคม",
        "กันยายน",
        "ตุลาคม",
        "พฤศจิกายน",
        "ธันวาคม"
    );

    //payment
    const datap = array(array("id" => "1", "name" => "เงินสด/Cash"), array("id" => "2", "name" => "โอนเงิน/Transfer"), array("id" => "3", "name" => "บัตรเครดิต/Credit Card"));



    private function convertDigit($digit)
    {
        switch ($digit) {
            case '0':
                return 'zero';
            case '1':
                return 'one';
            case '2':
                return 'two';
            case '3':
                return 'three';
            case '4':
                return 'four';
            case '5':
                return 'five';
            case '6':
                return 'six';
            case '7':
                return 'seven';
            case '8':
                return 'eight';
            case '9':
                return 'nine';
        }
    } //convertDigit


    /**
     * convert group of number scale.
     *
     * @param integer $index
     * @return string
     */
    private function convertGroup($index)
    {
        switch ($index) {
            case 11:
                return ' decillion';
            case 10:
                return ' nonillion';
            case 9:
                return ' octillion';
            case 8:
                return ' septillion';
            case 7:
                return ' sextillion';
            case 6:
                return ' quintrillion';
            case 5:
                return ' quadrillion';
            case 4:
                return ' trillion';
            case 3:
                return ' billion';
            case 2:
                return ' million';
            case 1:
                return ' thousand';
            case 0:
                return '';
        }
    } // convertGroup

    /**
     * convert the number (and with dot).
     *
     * @param number $num number integer or decimal. negative or positive.
     * @return string translated number to text in Thai language.
     */
    public function convertNumber($num)
    {
        $num = strval($num);

        if (strpos($num, '.') !== false) {
            list($num, $dec) = explode('.', $num);
        } else {
            $dec = 0;
        }

        $output = '';

        if ($num[0] == '-') {
            $output = 'negative ';
            $num = ltrim($num, '-');
        } else if ($num[0] == '+') {
            $output = 'positive ';
            $num = ltrim($num, '+');
        }

        if ($num == '0') {
            $output .= 'zero';
        } else {
            if (strlen($num) > 36) {
                return 'Error!';
            }

            $num = str_pad($num, 36, '0', STR_PAD_LEFT);
            $group = rtrim(chunk_split($num, 3, ' '), ' ');
            $groups = explode(' ', $group);

            $groups2 = array();
            foreach ($groups as $g) {
                $groups2[] = $this->convertThreeDigit($g[0], $g[1], $g[2]);
            } // endforeach;

            for ($z = 0; $z < count($groups2); $z++) {
                if ($groups2[$z] != '') {
                    $output .= $groups2[$z] . $this->convertGroup(11 - $z) . ($z < 11 && !array_search('', array_slice($groups2, $z + 1, -1)) && $groups2[11] != '' && $groups[11][0] == '0' ? ' and ' : ', ');
                }
            } // endfor;

            $output = rtrim($output, ', ');
        } // endif;

        if ($dec > 0) {
            $output .= ' point';
            for ($i = 0; $i < strlen($dec); $i++) {
                $output .= ' ' . $this->convertDigit($dec[$i]);
            } // endfor;

        } // endif;
        else {
            $output .= ' Bath only ';
        }

        return $output;
    } // convertNumber


    /**
     * convert three digit.
     *
     * @param integer $dig1
     * @param integer $dig2
     * @param integer $dig3
     * @return string
     */
    private function convertThreeDigit($dig1, $dig2, $dig3)
    {
        $output = '';

        if ($dig1 == '0' && $dig2 == '0' && $dig3 == '0') {
            return '';
        }

        if ($dig1 != '0') {
            $output .= $this->convertDigit($dig1) . ' hundred';
            if ($dig2 != '0' || $dig3 != '0') {
                $output .= ' and ';
            }
        }

        if ($dig2 != '0') {
            $output .= $this->convertTwoDigit($dig2, $dig3);
        } else if ($dig3 != '0') {
            $output .= $this->convertDigit($dig3);
        }

        return $output;
    } // convertThreeDigit


    /**
     * convert two digits
     *
     * @param integer $dig1
     * @param integer $dig2
     * @return string
     */
    private function convertTwoDigit($dig1, $dig2)
    {
        if ($dig2 == '0') {
            switch ($dig1) {
                case '1':
                    return 'ten';
                case '2':
                    return 'twenty';
                case '3':
                    return 'thirty';
                case '4':
                    return 'forty';
                case '5':
                    return 'fifty';
                case '6':
                    return 'sixty';
                case '7':
                    return 'seventy';
                case '8':
                    return 'eighty';
                case '9':
                    return 'ninety';
            }
        } else if ($dig1 == '1') {
            switch ($dig2) {
                case '1':
                    return 'eleven';
                case '2':
                    return 'twelve';
                case '3':
                    return 'thirteen';
                case '4':
                    return 'fourteen';
                case '5':
                    return 'fifteen';
                case '6':
                    return 'sixteen';
                case '7':
                    return 'seventeen';
                case '8':
                    return 'eighteen';
                case '9':
                    return 'nineteen';
            }
        } else {
            $temp = $this->convertDigit($dig2);
            switch ($dig1) {
                case '2':
                    return "twenty-$temp";
                case '3':
                    return "thirty-$temp";
                case '4':
                    return "forty-$temp";
                case '5':
                    return "fifty-$temp";
                case '6':
                    return "sixty-$temp";
                case '7':
                    return "seventy-$temp";
                case '8':
                    return "eighty-$temp";
                case '9':
                    return "ninety-$temp";
            }
        }
    } // convertTwoDigit

    public function URL_exists($url)
    {
        $headers = get_headers($url);
        return stripos($headers[0], "200 OK") ? true : false;
    }

    public function get_img_url($url)
    {
        //$url = "https://www.jotform.com/uploads/metaknowledge2022/230067598816466/5503526223327192231/receipt_20230124135338.png";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $a = curl_exec($ch); // $a will contain all headers

        $url_return = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL

        return $url_return;
    }

    /**
     * Convert baht number to Thai text
     * @param double|int $number
     * @param bool $include_unit
     * @param bool $display_zero
     * @return string|null
     */
    public function baht_text($number, $include_unit = true, $display_zero = true)
    {

        if (!is_numeric($number)) {
            return null;
        }

        $log = floor(log($number, 10));
        if ($log > 5) {
            $millions = floor($log / 6);
            $million_value = pow(1000000, $millions);
            $normalised_million = floor($number / $million_value);
            $rest = $number - ($normalised_million * $million_value);
            $millions_text = '';
            for ($i = 0; $i < $millions; $i++) {
                $millions_text .= self::BAHT_TEXT_UNITS[6];
            }
            return $this($normalised_million, false) . $millions_text . $this($rest, true, false);
        }

        $number_str = (string)floor($number);
        $text = '';
        $unit = 0;

        if ($display_zero && $number_str == '0') {
            $text = self::BAHT_TEXT_NUMBERS[0];
        } else for ($i = strlen($number_str) - 1; $i > -1; $i--) {
            $current_number = (int)$number_str[$i];

            $unit_text = '';
            if ($unit == 0 && $i > 0) {
                $previous_number = isset($number_str[$i - 1]) ? (int)$number_str[$i - 1] : 0;
                if ($current_number == 1 && $previous_number > 0) {
                    $unit_text .= self::BAHT_TEXT_ONE_IN_TENTH;
                } else if ($current_number > 0) {
                    $unit_text .= self::BAHT_TEXT_NUMBERS[$current_number];
                }
            } else if ($unit == 1 && $current_number == 2) {
                $unit_text .= self::BAHT_TEXT_TWENTY;
            } else if ($current_number > 0 && ($unit != 1 || $current_number != 1)) {
                $unit_text .= self::BAHT_TEXT_NUMBERS[$current_number];
            }

            if ($current_number > 0) {
                $unit_text .= self::BAHT_TEXT_UNITS[$unit];
            }

            $text = $unit_text . $text;
            $unit++;
        }

        if ($include_unit) {
            $text .= self::BAHT_TEXT_BAHT;

            $satang = explode('.', number_format($number, 2, '.', ''))[1];
            $text .= $satang == 0
                ? self::BAHT_TEXT_INTEGER
                : $this($satang, false) . self::BAHT_TEXT_SATANG;
        } else {
            $exploded = explode('.', $number);
            if (isset($exploded[1])) {
                $text .= self::BAHT_TEXT_POINT;
                $decimal = (string)$exploded[1];
                for ($i = 0; $i < strlen($decimal); $i++) {
                    $text .= self::BAHT_TEXT_NUMBERS[$decimal[$i]];
                }
            }
        }

        return $text;
    }
    public function fileStore(Request $request)
    {
        $filecount = count($request->file('file'));
        $newname = "";
        for ($i = 0; $i <= ($filecount - 1); $i++) {
            $image = $request->file('file')[$i];

            $imageName = $image->getClientOriginalName();
            $oldfile = FileUpload::where('oldname', $imageName)->get();
            if (!is_array($oldfile)) {

                $newname = Str::random(40) . "_" . $imageName;
                $image->move(public_path('receipt_form_school'), $newname);

                $imageUpload = new FileUpload();
                $imageUpload->filename = $newname;
                $imageUpload->oldname = $imageName;
                $imageUpload->save();
            }
        }
        return response()->json(['success' => $newname]);
    }
    public function fileDestroy(Request $request)
    {

        $filename =  $request->get('name');
        $oldfile = FileUpload::where('oldname', $filename)->get();
        $path = public_path() . '/receipt_form_school/' . $oldfile[0]->filename;
        if (file_exists($path)) {
            unlink($path);
            FileUpload::where('oldname', $filename)->delete();
        }
        return $filename;
    }

    public function textWatermark($image, $text)
    {
        $img = Image::make(public_path('receipts_form_school/' . $image));

        $img->text($text, 610, 670, function ($font) {
            $font->file(public_path('font/amandasignature.ttf'));
            $font->size(60);
            $font->color('#ff0000');
            $font->align('center');
            $font->valign('bottom');
            $font->angle(80);
        });

        $img->save(public_path('receipt_form_watermark_images/' . $image));

        $img->encode('png');
        $type = 'png';
        $new_image = 'data:image/' . $type . ';base64,' . base64_encode($img);

        //return view('show_watermark', compact('new_image'));
        return  $new_image;
    }


    public function index(Request $request)
    {



        if ($request->ajax()) {


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
                    $stu = explode('|', $row->pid);
                    if ($row->pid != "") {


                        foreach ($stu as $stus) {
                            $stus = Student::where('id', $stus)->get();
                            $student .= $stus[0]->first_name . " " . $stus[0]->last_name . ' ';
                        }
                    }
                    return $student;
                })
                ->editColumn('name_parent', function ($row) {
                    $parent = ParentRegister::where('id', $row->name_parent)->get();
                    $parents = "";
                    foreach ($parent as $value) {
                        $parents .= $value->name_parent . ",";
                    }

                    return $parents;
                })
                // ->editColumn('receipt_number', function ($row) {

                //     $receipt = SchoolPrice::where('rid',$row->id)->get();
                //     $receipts = "";
                //     foreach($receipt as $value){
                //         $receipts .= $value->receipt_number .",";

                //     }

                //     return $receipts ;
                // })
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
                    $html = '<a href="receipt_school/receipt/show/view/' . $row->id . '" class="btn btn-xs btn-success btn-view" id="ViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View </a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    //$html .= '<a href="#" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }



        $student = Student::orderBy("first_name", "asc")->get();
        $subject = Subject::orderBy("id", "asc")->get();
        $parent = ParentRegister::orderBy("id", "asc")->get();

        return view('receipt_school.receipt')->with(['data2' => $student])->with(['data3' => $subject])->with(['data4' => $parent])
            ->with(['datap' => self::datap]);
    }
    public function edit($id, $type)
    {
        $data = Receipt_School::find($id);
        $data2 = ParentRegister::find($data->name_parent);

        //parent list
        $select_list_parent = "";
        $parent = ParentRegister::orderBy("id", "asc")->get();
        foreach ($parent as $keys) {
            if ($data->name_parent == $keys->id) {

                $select_list_parent .= '<option value="' . $keys->id . '" selected>' . $keys->code . ' ' . $keys->name_parent . '</option>';
            } else {
                $select_list_parent .= '<option value="' . $keys->id . '" >' . $keys->code . ' ' . $keys->name_parent. '</option>';
            }
        }


        //student list
        $student = "";
        $stu = explode('|', $data->pid);
        if ($data->pid != "") {
            foreach ($stu as $stus) {
                $stus = Student::where('id', $stus)->get();
                $student .= '<option value="' . $stus[0]->id . '" selected> ' . $stus[0]->first_name . " " . $stus[0]->last_name . ' </option>';
            }
        }

        $payment = '';

        foreach (self::datap as $pay) {

            if ($pay['id'] == $data->payment_type) {
                $selected_pay = 'selected';
            } else {
                $selected_pay = '';
            }

            $payment .= '<option value="' . $pay['id'] . '" ' . $selected_pay . '> ' . $pay['name'] . ' </option>';
        }


        $receipts_detail = SchoolPrice::where('rid', $id)
            ->orderBy("id", "asc")
            ->get();

        $x = 1;
        $data_html = '';

        foreach ($receipts_detail as $dresult) {
            $subject = Subject::where('id', $dresult['subject'])->orderBy("id", "asc")->get();
            $level = Level::where('subject', $dresult['subject'])->where('id', $dresult['level'])->orderBy("id", "asc")->get();
            $datalevel = Level::where('levels.subject', $dresult['subject'])
                ->orderBy("subject", "asc")->get();
            $subjectall = Subject::orderBy("id", "asc")->get();
            $select_list = "";
            $subject_list = "";
            foreach ($subjectall as $key) {
                $subject_list .= '<option value="' . $key->id . '" >  ' . $key->title . '</option>';
            }


            foreach ($datalevel as $key) {

                $select_list .= '<option value="' . $key->id . '" > (' . $key->level_sort . ') ' . $key->title . '</option>';
            }

            $course_type_1 = '';
            if ($dresult['course_type'] == 1) {
                $course_type_1 = 'selected';
            }
            $course_type_2 = '';
            if ($dresult['course_type'] == 2) {
                $course_type_2 = 'selected';
            } else {
                $course_type_1 = 'selected';
            }
            $course_type_select = '
            <option value="1" ' . $course_type_1 . '>Course+Book
            </option>
            <option value="2" ' . $course_type_2 . '>Book
            </option>';


            $message = "";
            if ($dresult['type'] == 1) {
                $message = "One Tpye แบบเดี่ยว";
            } else if ($dresult['type'] == 2) {
                $message = "Couple แบบคู่";
            } else {
                $message = "No type";
            }

            $messageprice = "";
            if ($dresult['price'] == 1) {
                $messageprice = "Old Price";
            } else if ($dresult['price'] == 2) {
                $messageprice = "New Price";
            }

            $onoff = "";
            if ($subject[0]->id == 3) {
                $onoff = "";
            } else {
                $onoff = "disabled";
            }

            $weekstaus = "";
            if ($dresult['week'] == 4) {
                $weekstaus = "";
            } else {
                $weekstaus = "disabled";
            }

            if ($dresult['course_type'] == 2) {
                $weekstaus = "disabled";
            }


            $data_html .= '<tr class="firstTr3">
        <td width="5%">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <input type="text" id="rno[]" name="rno[]"
                    class="form-control has-feedback-left" value="' . $x . '">
                    <input name="rrid[]"  id="rrid" type="hidden" value="' . $dresult['id'] . '" />
            </div>
        </td>
        <td width="10%">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <select style="width: 100%;" class="ctype form-control"
                id="course_type" name="course_type[]" required>
                ' . $course_type_select . '
            </select>
        </div>
    </td>
        <td width="10%">
            <div class="col-md-12 col-sm-12 col-xs-12 ">

                <select style="width: 100%;" class=" form-control" id="rrsub" name="rrsub[]" value="' . $subject[0]->id . '" >
                <option value="'  . $subject[0]->id . '" selected> ' . $subject[0]->title  . ' </option>
                ' . $subject_list . '
            </select>

            </div>
        </td>

        <td width="10%">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <select style="width: 100%;" class=" form-control" id="rrlevel" name="rrlevel[]" value="' . $level[0]->id . '">
            <option value="' . $level[0]->id .  '" selected> (' . $level[0]->level_sort . ')' . $level[0]->title  . ' </option>
            ' . $select_list . '

            </select>
        </div>
    </td>

        <td width="7%">
            <div class="col-md-12 col-sm-12 col-xs-12">
            <select style="width: 100%;" class=" form-control" id="rrweek" name="rrweek[]" >
                                                        <option class="weekh" value="' . $dresult['week'] . '" selected>' . $dresult['week'] . " " . "week" . '</option>

                                                        <option value="1">1 week
                                                        </option>
                                                        <option value="2">2 week
                                                        </option>
                                                        <option value="3">3 week
                                                        </option>
                                                        <option value="4">Month
                                                        </option>
                                                    </select>

            </div>
        </td>
        <td width="5%">
        <div class="col-md-12 col-sm-12 col-xs-12">
        <select style="width: 100%;" class=" form-control" id="rrqty" name="rrqty[]" ' . $weekstaus . '>
                                                        <option class="qtyh" value="' . $dresult['qty'] . '" selected>' . $dresult['qty'] . '</option>

                                                        <option value="1">1
                                                        </option>
                                                        <option value="2">2
                                                        </option>
                                                        <option value="3">3
                                                        </option>
                                                        <option value="4">4
                                                        </option>
                                                        <option value="5">5
                                                        </option>
                                                        <option value="6">6
                                                        </option>
                                                        <option value="7">7
                                                        </option>
                                                        <option value="8">8
                                                        </option>
                                                        <option value="9">9
                                                        </option>
                                                        <option value="10">10
                                                        </option>
                                                        <option value="11">11
                                                        </option>
                                                        <option value="12">12
                                                        </option>

                                                    </select>

    </div>
        </td>

        <td width="7%">
            <div class="col-md-12 col-sm-12 col-xs-12 ffff">
                <input type="number" id="rrbook" name="rrbook[]"
                class="form-control has-feedback-left" value="' . $dresult['book'] . '">
            </div>
        </td>
        <td width="7%">
        <div class="col-md-12 col-sm-12 col-xs-12 cc">
            <input type="text" id="rrbookprice" name="rrbookprice[]"
            class="form-control has-feedback-left" value="' . $dresult['bookprice'] . '" disabled>
        </div>
    </td>
    <td width="10%">
            <div class="col-md-12 col-sm-12 col-xs-12 ">
            <select style="width: 100%;" class=" form-control" id="rrtype" name="rrtype[]" ' . $onoff . '>
            <option value="' . $dresult['type'] . '" selected>' . $message . '</option>
            <option value="1">One Tpye แบบเดี่ยว
            </option>
            <option value="2">Couple แบบคู่
            </option>


        </select>

            </div>
        </td>
        <td width="7%">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <input type="number" id="rrdiscount" name="rrdiscount[]"
                class="form-control has-feedback-left" value="' . $dresult['discount'] . '">
            </div>
        </td>
        <td width="7%">
            <div class="col-md-12 col-sm-12 col-xs-12">
            <select style="width: 100%;" class="bbb form-control" id="rrprice" name="rrprice[]">
                                                        <option class="priceh" value="' . $dresult['price'] . '" selected>' . $messageprice . '</option>

                                                        <option value="1">Old Price
                                                        </option>
                                                        <option value="2">New Price
                                                        </option>

                                                    </select>

            </div>
        </td>
        <td width="7%">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <input type="text" id="rrperiod" name="rrperiod[]"
                class="form-control has-feedback-left" value="' . $dresult['period'] . '">
            </div>
        </td>
        <td width="20%">
            <div class="col-md-12 col-sm-12 col-xs-12 acdc">
            <input type="text" id="rrtotal" name="rrtotal[]"
            class="form-control has-feedback-left" value="' . $dresult['total'] . '">

            </div>
        </td>
        <!--<td><button type="button" id="removeRowt"
                class="btn btn-sm btn-danger btnRemoveg"><i
                    class="fa fa-minus"></i></button></td>-->

    </tr>';
            $x++;
        }


        $html = "";


        $pic = explode('|', $data->file_att);
        $x = 0;
        foreach ($pic as $pics) {
            if ($pic != Null || $pic != "") {
                if ($pics != Null || $pics != "") {
                    // echo("1");
                    ///$this->textWatermark($pics, 'Receipt Number : ' . $data->receipt_number);

                    //$retu = str_replace('receipt_form_images', 'receipt_form_watermark_images', $urla);
                    //$html .= '<img src="' . url('/') . '/receipt_form_watermark_images/' . $pics . '" class="me-3 mt-5 mb-5" height="400" >';
                    $html .= '
                <div class="col-md-4 text-center mb-3 ">
                <img src="' . url('/') . '/receipts_form_school/' . $pics . '" class=" mt-5 mb-3" height="400" width="300">
                <a href="#" class="btn btn-xs btn-danger btn-edit" id="getDeleteData" data-id2="' . $data->id . '" data-id="' . $pics . '">delete </a>
                </div>
                ';
                }
            } else {
                // echo("2");
                $html = "";
            }
        }

        return response()->json([
            'data' =>  $data, 'data2' => $data2, 'student' => $student,  'data_html' => $data_html, 'imgs' => $html, 'payment' => $payment,
            'select_list_parent'=> $select_list_parent
        ]);


    }

    public function show($type, $id)
    {
        $data = Receipt_School::find($id);

        if ($type == "view") {
            $viewr = "receipt.view1";
        } else if ($type == "print") {
            $viewr = "receipt.print1";
        }

        $receipts_detail = SchoolPrice::where('rid', $data->id)
            ->orderBy("rid", "asc")
            ->get();
        $all_price = 0;
        $receipt_detail_arr = array();
        $no = 1;
        $x = 0;
        $booksum = 0;
        $pricesub = 0;
        $discount = 0;
        foreach ($receipts_detail as $dresult) {
            if ($dresult['course_type'] == 2) {
                $price = $dresult['bookprice'];
            } else {
                $price = ($dresult['total'] - $dresult['bookprice']);
            }

            $message = "";
            if ($dresult['week'] == 1 || $dresult['week'] == 2 || $dresult['week'] == 3) {
                $message = " สัปดาห์";
                $qty = $dresult['week'];
            } else {
                $message = "เดือน";
                $qty = $dresult['qty'];
            }

            if ($price == 0) {
                $receipt_detail_arr[$x]['no'] = '';
                $receipt_detail_arr[$x]['price'] = '';
                $receipt_detail_arr[$x]['unit'] = '';
                $receipt_detail_arr[$x]['unitprice'] = '';
                $receipt_detail_arr[$x]['quantity'] = '';
                $receipt_detail_arr[$x]['discount'] = '';
                $receipt_detail_arr[$x]['book'] = '';
                $receipt_detail_arr[$x]['bookprice'] = '';
                $receipt_detail_arr[$x]['receipt_number'] = '';
                $receipt_detail_arr[$x]['period'] = '';
                $receipt_detail_arr[$x]['course_type'] = $dresult['course_type'];
            } else {

                $receipt_detail_arr[$x]['no'] = $no;
                $receipt_detail_arr[$x]['price'] = number_format($price, 2);
                $receipt_detail_arr[$x]['unit'] = $message;
                $receipt_detail_arr[$x]['course_type'] = $dresult['course_type'];
                if ($dresult['course_type'] == 2) {
                    $receipt_detail_arr[$x]['unitprice'] = number_format(($dresult['total'] - $dresult['discount']) / $dresult['book'], 2);
                } else {
                    $receipt_detail_arr[$x]['unitprice'] = number_format(($dresult['total'] + $dresult['discount'] - $dresult['bookprice']) / $dresult['qty'], 2);
                }
                $receipt_detail_arr[$x]['quantity'] =  $qty; //$dresult['qty'];
                $receipt_detail_arr[$x]['discount'] = number_format($dresult['discount'], 2);
                $receipt_detail_arr[$x]['book'] = ($dresult['book']);
                $receipt_detail_arr[$x]['bookprice'] = number_format($dresult['bookprice'], 2);
                $receipt_detail_arr[$x]['receipt_number'] = ($dresult['receipt_number']);
                $receipt_detail_arr[$x]['period'] = $dresult['period'];
            }


            $subject = Subject::where('id', $dresult['subject'])
                ->get();

            foreach ($subject as $sresult) {
                $sub = $sresult;
            }

            $level = Level::where('subject', $sub->id)->where('id', $dresult['level'])
                ->get();
            foreach ($level as $titles) {
                $titless = $titles;
            }

            $data0 = SubjectPrice::Where('subject_id', $sub->id)->Where('level_id', $dresult['level'])
                ->get();

            foreach ($data0 as $bookp) {
                $booktotal = $bookp;
            }
            $pricesub = $pricesub + (($dresult['total'] - $dresult['discount'])- $dresult['bookprice']);
            $receipt_detail_arr[$x]['books'] = $bookp->book_price;
            $receipt_detail_arr[$x]['description'] = $sub->title . "  " . "Level " . $titless->level_sort;
            $booksum = $booksum + $dresult['bookprice'];
            $discount = $discount + $dresult['discount'];
            $all_price =  $booksum + $pricesub - $discount;
            $no++;
            $x++;
        }


        $price_text = $this->baht_text($all_price);
        $price_texten = $this->convertNumber($all_price);
        $vat_price = ($pricesub * 100) / 107;
        $vat_total = $pricesub - $vat_price;
        $vat_price_val = number_format($vat_price, 2);
        $vat_val = number_format($vat_total, 2);

        $price_val = number_format(($vat_price+$vat_total)+$booksum, 2);




        $mes = "";
        $text = "";
        $file_att = "";

        $student = "";
        $stu = explode('|', $data->pid);
        if ($data->pid != "") {


            foreach ($stu as $stus) {
                $stus = Student::where('id', $stus)->get();
                $student .= $stus[0]->nick_name . " , ";
            }
        }


        $data2 = ParentRegister::find($data->name_parent);
        $html = "";
        $pic = explode('|', $data->file_att);


        foreach ($pic as $pics) {

            //$this->textWatermark($pics, 'Receipt Number : ' . $data->receipt_number);

            //$retu = str_replace('receipt_form_images', 'receipt_form_watermark_images', $urla);
            $html .= '<img src="' . url('/') . '/receipts_form_school/' . $pics . '" class="me-3 mt-5 mb-5" height="400" >';
        }

        $payment = '';
        $i = 1;
        foreach (self::datap as $pay) {

            if ($pay['id'] == $data->payment_type) {
                $selected_pay = '<img src="' . asset('images/close.png') . '" alt="..."
                height="14" style="padding-bottom: 1px">';
            } else {
                $selected_pay = '';
            }

            if ($i == 1) {
                $tc = 'activeleft activetop activebottom';
            } else {
                $tc = 'activeleft activebottom';
            }
            $payment .= ' <tr>
            <td class="active ' . $tc . '" width="10%" align="center">' . $selected_pay . '</td>
            <td class=" grey">' . $i . '.' . $pay['name'] . '</td>
            <td class="  grey"></td>

        </tr>';
            $i++;
        }


        return view($viewr)->with(['id' => $id])
            ->with(['discount' => $discount])
            ->with(['vat_price' => $vat_price])
            ->with(['booksum' => $booksum])
            ->with(['html' => $html])
            ->with(['data' => $data])
            ->with(['data2' => $data2])
            ->with(['student' => $student])
            ->with(['bookprice' => $booktotal->book_price])
            ->with(['receipt_detail_arr' => $receipt_detail_arr])
            ->with(['price_val' => $price_val])
            ->with(['mes' => $mes])
            ->with(['text' => $text])
            ->with(['price_texten' => $price_texten])
            ->with(['price_text' => $price_text])
            ->with(['vat_price_val' => $vat_price_val])
            ->with(['vat_val' => $vat_val])
            ->with(['payment' => $payment])
            ->with(['course_type' => $data->course_type])
            ->with(['file_att' => $file_att]);
    }

    public function find($parent)
    {
        $data = Student::where('pid', $parent)->orderBy("id", "asc")->get();
        $select_list = "";
        $data2 = ParentRegister::where('id', $parent)->orderBy("id", "asc")->get();
        $mobile = "";
        $address = "";
        $postcode = "";
        foreach ($data2 as $key) {
            $mobile = $key->mobile;
            $address = $key->street_address;
            $postcode = $key->postcode;
        }
        foreach ($data as $key) {

            $select_list .= '<option value="' . $key->id . '" > ' . $key->first_name . ' ' . $key->last_name . '</option>';

        }

        return response()->json([
            'html' =>  $select_list, 'html2' => $mobile, 'html3' => $address, 'html4' => $postcode
        ]);
    }

    public function search($sub_id)
    {

        $data = Level::where('levels.subject', $sub_id)
            ->orderBy("subject", "asc")->get();
        $select_list = "";

        foreach ($data as $key) {

            $select_list .= '<option value="' . $key->id . '" > (' . $key->level_sort . ') ' . $key->title . '</option>';
        }

        return response()->json([
            'html' =>  $select_list
        ]);
    }

    public function searchs($sub_id, $level_id, $week, $price, $qty, $discount, $book_price, $type, $course_type)
    {
        $select_list = "";
        if ($price == 1) {
            $data = SubjectPrice::Where('subject_prices.subject_id', $sub_id)->Where('subject_prices.type', $type)->Where('subject_prices.level_id', $level_id)->Where('subject_prices.week', $week)->get();
            foreach ($data as $key) {

                $select_list .= $key->old_price;
            }
        } else {
            $data = SubjectPrice::Where('subject_prices.subject_id', $sub_id)->Where('subject_prices.type', $type)->Where('subject_prices.level_id', $level_id)->Where('subject_prices.week', $week)->get();
            foreach ($data as $key) {

                $select_list .= $key->price;
            }
        }


        $output = "";
        $outputsum = "";
        if ($course_type == 2) {
            $pricet = (int)$book_price - (int)$discount;
        } else {
            $pricet = (((int)$select_list * (int)$qty) + (int)$book_price) - (int)$discount;
        }
        $output .= '<input type="text" id="pp" name="pp" class="form-control has-feedback-left" value="' . $pricet . '" >
        ';
        $outputsum .= '' . $pricet . '';
        return response()->json([
            'html' =>  $output, 'outputsum' => $outputsum
        ]);
    }

    public function sumbook($sub_id, $level_id, $book)
    {
        $data = SubjectPrice::Where('subject_prices.subject_id', $sub_id)->Where('subject_prices.level_id', $level_id)->get();
        $select_list = "";
        foreach ($data as $key) {

            $select_list = $key->book_price;
        }
        $output = "";
        $output = '<input type="text" id="book_price" name="book_price" class="form-control has-feedback-left" value="' . (int)$select_list * (int)$book . ' "readonly >
        ';
        $sum = "";
        $sum = '' . (int)$select_list * (int)$book . ' ';
        return response()->json([
            'html' =>  $output, 'sum' => $sum
        ]);
    }

    public function store(Request $request)
    {



        $validator =  Validator::make(
            $request->all(),
            [
                // 'type' => 'required|string',
                'submission_date' => 'required|string',
                'payment_type' => 'required|string',
                //'city' => 'required|string',
                //'province' => 'required|string',

                'name_parent' => 'required|string',
                'mobile' => 'required|string',
                'receipt.*.subject' => 'required',
                'receipt.*.level' => 'required',
                'receipt.*.week' => 'required',
                //'receipt.*.qty' => 'required',
                'receipt.*.price' => 'required',
                'receipt.*.pp' => 'required',
                //'receipt.*.period' => 'required',

                // 'level' => 'required|string',
                // 'week' => 'required|string',
                // 'qty' => 'required|string',
                // 'book' => 'required|string',
                // 'bookprice' => 'required|string',
                // 'discount' => 'required|string',
                // 'price' => 'required|string',
                // 'total' => 'required|string',

                // 'file_att' => 'required|string

            ],
            [
                'receipt.*.subject.required' => 'The Subject field is required.  !',
                'receipt.*.level.required' => 'The Start Level field is required.  !',
                'receipt.*.week.required' => 'The Week field is required.  !',
                //'receipt.*.qty.required' => 'The QTY field is required. !',
                'receipt.*.price.required' => 'The Price field is required.  !',
                'receipt.*.pp.required' => 'The Book field is required.  !',
                //'receipt.*.period.required' => 'The Period field is required.  !',

            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }


        $filesb = $request->get('img');

        $filebs = "";
        foreach ($filesb as $fileb) {
            //echo $fileb;

            $oldfile = FileUpload::where('oldname', $fileb)->get();
            $filebs .= $oldfile[0]->filename . "|";
            File::move(public_path() . '/receipt_form_school/' . $oldfile[0]->filename, public_path() . '/receipts_form_school/' . $oldfile[0]->filename);
            FileUpload::where('filename', $oldfile[0]->filename)->delete();
        }

        $filebs = rtrim($filebs, "|");


        $st = $request->get('student');

        $stu = "";
        foreach ($st as $sss) {
            //echo $filea;
            $stu .= $sss . "|";
        }

        $stu = rtrim($stu, "|");
        $rnumber = Receipt_School_RunningNumber::generate();

        $data = new Receipt_School([
            'submission_date' => $request->get('submission_date'),
            'payment_type' => $request->get('payment_type'),
            'street_address' => $request->get('street_address'),
            'postcode' => $request->get('postcode'),
            'mobile' => $request->get('mobile'),
            'pid' => $stu,
            'name_parent' => $request->get('name_parent'),
            'type' => 0,
            // 'sid' => $request->get('student'),
            'receipt_number' => $rnumber,
            'file_att' => $filebs,

        ]);


        $data->save();
        $rid = $data->id;
        // print_r($request->get('receipt'));

        foreach ($request->get('receipt') as $value) {

            $data2 = new SchoolPrice([
                'subject' => $value['subject'],
                'course_type' => $value['course_type'],
                'level' => $value['level'],
                'week' => $value['week'],
                'qty' => $value['qty'],
                'book' => $value['book'],
                'bookprice' => $value['book_price'],
                'discount' => $value['discount'],
                'price' => $value['price'],
                'total' => $value['pp'],
                'rid' => $rid,
                'type' => $value['type'],
                'receipt_number' => 0,
                'period' => $value['period'],

            ]);
            // print_r($data2['subject']);
            // exit();
            $data2->save();
        }


        return response()->json(['success' => 'Receive Add successfully']);
    }

    public function deleteImg($id, $id2)
    {
        $dataimg = Receipt_School::find($id2);
        $img = $dataimg->file_att;
        $img = explode("|", $img);

        if (($key = array_search($id, $img)) !== false) {
            unset($img[$key]);
        }
        $dataNew = "";
        foreach ($img as $fileb) {
            $dataNew .= $fileb . "|";
        }
        $dataNew = rtrim($dataNew, "|");


        Receipt_School::find($id2)->update(['file_att' => $dataNew]);


        $link = (url('/') . '/receipts_form_school/' . $id);


        $html = "";
        $pic = explode('|', $dataNew);
        foreach ($pic as $pics) {
            if ($pics != NULL && $pics != "") {
                $html .= '
                <div class="col-md-4 text-center mb-3 ">
                <img src="' . url('/') . '/receipts_form_school/' . $pics . '" class=" mt-5 mb-3" height="400" width="300">
                <a href="#" class="btn btn-xs btn-danger btn-edit" id="getDeleteData" data-id2="' . $id2 . '" data-id="' . $pics . '">delete </a>
                </div>
                ';
            } else {
                $html = "";
            }
        }



        return response()->json(['imgs' => $html]);
    }

    public function update(Request $request, $id)
    {

        $validator =  Validator::make($request->all(), [
            'submission_date' => 'required|string',
            'payment_type' => 'required|string',
            // 'first_name' => 'required|string',
            // 'last_name' => 'required|string',
            //'street_address' => 'required|string',
            //'city' => 'required|string',
            //'province' => 'required|string',
            //'postcode' => 'required|string',
            //'mobile' => 'required|string',

            //'package' => 'required|string',
            //'file_att' => 'required|string',
            //'description' => 'required|string',
            // 'grade' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }


        $img = $request->get('img');
        $stu = "";
        if ($img != Null || $img != "") {


            foreach ($img as $fileb) {
                //echo $filea;
                // $stu .= $sss . "|";
                $oldfile = FileUpload::where('oldname', $fileb)->get();
                $stu .= $oldfile[0]->filename . "|";
                File::move(public_path() . '/receipt_form_school/' . $oldfile[0]->filename, public_path() . '/receipts_form_school/' . $oldfile[0]->filename);
                FileUpload::where('filename', $oldfile[0]->filename)->delete();
            }
        }

        //parent //student
        $id_parent = $request->get('id_parent');
        $st = $request->get('student');

        $stud = "";
        foreach ($st as $sss) {
            $stud .= $sss . "|";
        }

        $stud = rtrim($stud, "|");
        //$id_parent = $request->get('name_parent');

        $dataimg = Receipt_School::find($id_parent);
        if ($dataimg->file_att != Null && $dataimg->file_att != "") {
            $stu = $dataimg->file_att . "|" . $stu;

            $stu = rtrim($stu, "|");
            Receipt_School::find($id_parent)->update(['file_att' => $stu]);
        } else {
            Receipt_School::find($id_parent)->update(['file_att' => $stu]);
            return response()->json(['success' => 'Receipt updated successfully']);
        }
        // echo($dataimg->file_att);
        // exit();
        // $filesb = $request->get('img');

        // $filebs = "";
        // foreach ($dataimg as $fileb) {
        //     //echo $fileb;

        //     $oldfile = FileUpload::where('oldname', $fileb)->get();
        //     $filebs .= $oldfile[0]->filename . "|";
        //     File::move(public_path() . '/receipt_form_school/' . $oldfile[0]->filename, public_path() . '/receipts_form_school/' . $oldfile[0]->filename);
        //     FileUpload::where('filename', $oldfile[0]->filename)->delete();
        // }

        // $filebs = rtrim($filebs, "|");


        // Receipt_School::find($id)->save('file_att' => $stu);
        // $data = new Receipt_School([

        //     'file_att' => $stu,

        // ]);
        // print_r($data);
        // exit();


        // $data->update();

        // echo $request->get('name_parent2');
        // exit();
        $rrid  = $request->get('rrid');
        $rrsub  = $request->get('rrsub');
        $rrcourse  = $request->get('rrcourse');
        $rrlevel  = $request->get('rrlevel');
        $rrweek  = $request->get('rrweek');
        $rrqty  = $request->get('rrqty');
        $rrbook  = $request->get('rrbook');
        $rrbookprice  = $request->get('rrbookprice');
        $rrtype  = $request->get('rrtype');
        $rrdiscount  = $request->get('rrdiscount');
        $rrprice  = $request->get('rrprice');
        $rrtotal  = $request->get('rrtotal');
        $rrperiod = $request->get('rrperiod');

        //add student to request
        $request->request->add(['pid' => $stud]);

        Receipt_School::find($id)->update($request->all());
        $rdata = array();

        SchoolPrice::where('rid', $id)->delete();
        for ($xx = 0; $xx < count($rrsub); $xx++) {

            if ($rrtype[$xx] != "" && $rrtype[$xx] != NULL) {

                $rdata = new SchoolPrice([
                    'rid' => $id,
                    'subject' => $rrsub[$xx],
                    'course_type' => $rrcourse[$xx],
                    'level' => $rrlevel[$xx],
                    'week' => $rrweek[$xx],
                    'qty' => $rrqty[$xx],
                    'book' => $rrbook[$xx],
                    'bookprice' => $rrbookprice[$xx],
                    'type' => $rrtype[$xx],
                    'discount' => $rrdiscount[$xx],
                    'price' => $rrprice[$xx],
                    'total' => $rrtotal[$xx],
                    'receipt_number' => 0,
                    'period' => $rrperiod[$xx],
                    //'amount' => $request->get('book_old_price'),
                ]);
            } else {

                $rdata = new SchoolPrice([
                    'rid' => $id,
                    'subject' => $rrsub[$xx],
                    'course_type' => $rrcourse[$xx],
                    'level' => $rrlevel[$xx],
                    'week' => $rrweek[$xx],
                    'qty' => $rrqty[$xx],
                    'book' => $rrbook[$xx],
                    'bookprice' => $rrbookprice[$xx],
                    'type' => 0,
                    'discount' => $rrdiscount[$xx],
                    'price' => $rrprice[$xx],
                    'total' => $rrtotal[$xx],
                    'receipt_number' => 0,
                    'period' => $rrperiod[$xx],

                    //'amount' => $request->get('book_old_price'),
                ]);
            }

            // print_r($rrtype);
            // exit();
            $rdata->save();
        }

        // print_r ($rrid[1]);
        // for ($x = 0; $x < count($request->get('rrsub')); $x++) {


        //     if($rrid != null){
        //         SchoolPrice::find($rrid[$x])
        //         ->update([
        //             'subject' => $rrsub[$x],
        //             'level' => $rrlevel[$x],
        //             'week' => $rrweek[$x],
        //             'qty' => $rrqty[$x],
        //             'book' => $rrbook[$x],
        //             'bookprice' => $rrbookprice[$x],
        //             'type' => $rrtype[$x],
        //             'discount' => $rrdiscount[$x],
        //             'price' => $rrprice[$x],
        //             'total' => $rrtotal[$x],

        //         ]);
        //     }else{
        //         $rdata = array();
        //         $rdata = new SchoolPrice([
        //             'subject' => $rrsub[$x],
        //             'level' => $rrlevel[$x],
        //             'week' => $rrweek[$x],
        //             'qty' => $rrqty[$x],
        //             'book' => $rrbook[$x],
        //             'bookprice' => $rrbookprice[$x],
        //             'type' => $rrtype[$x],
        //             'discount' => $rrdiscount[$x],
        //             'price' => $rrprice[$x],
        //             'total' => $rrtotal[$x],
        //             'rid'=> 0,
        //             //'amount' => $request->get('book_old_price'),
        //         ]);
        //         $rdata->save();

        //     }




        // }
        return response()->json(['success' => 'Receipt updated successfully']);
    }
}
