<?php

namespace App\Http\Controllers;

use App\Helpers\LogActivity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
// use File;
use Illuminate\Support\Str;
use App\Models\FileUpload;
use App\Models\History;
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
use App\Models\Statuslog;
use Carbon\Carbon;
use App\Services\StatusService;
use Illuminate\Support\Facades\File;

class ReceiptSchoolController extends Controller
{

    //test host
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

    protected $statusService;
    public function __construct(StatusService $statusService)
    {
        // parent::__construct();
        $this->statusService = $statusService;
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
                "parent.name_parent as name_parent",
                "receipt_school.mobile as mobile",
                "receipt_school.receipt_number as receipt_number",
                "receipt_school.pid as pid",
            )

                //->join("students", "students.id", "=", "receipts.student_id")
                ->join("parent", "parent.id", "=", "receipt_school.name_parent")
                //->join("levels", "levels.id", "=", "receipts.level_id")

                ->orderBy("receipt_school.id", "desc")
                ->get();

            // echo "<pre>";
            // print_r($receipt);
            // echo "</pre>";
            // exit();
            return datatables()->of($receipt)
                ->editColumn('pid', function ($row) {
                    $student = "";
                    $stu = explode('|', $row->pid);
                    // dd($stu);
                    $numname = 0;

                    if ($row->pid !== "") {
                        foreach ($stu as $stus) {

                            if (Student::where('id', $stus)->exists()) {
                                $stusq = Student::where('id', $stus)->get();
                                //$student .= $stusq[0]->nick_name . " ," . $stusq[0]->last_name . ' ';
                                if ($numname < 1) {
                                    # code...
                                    $student .= $stusq[0]->nick_name;
                                } else {

                                    $student .= " , " . $stusq[0]->nick_name;
                                }
                            } else {
                                $student .= '-';
                            }

                            $numname++;
                        }
                    } else {
                        $student = '-';
                    }
                    return $student;
                })
                ->editColumn('name_parent', function ($row) {
                    //$parent = ParentRegister::where('id', $row->name_parent)->get();
                    //$parents = "";
                    //foreach ($parent as $value) {
                    //$parents .= $value->name_parent . ",";
                    //}

                    //return $parents;
                    return $row->name_parent;
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
                ->editColumn('type2', function (Receipt_School $receipt) {
                    if ($receipt->type == 9) {
                        $mes = "yes";
                    } else {

                        $mes = "no";
                    }
                    return $mes;
                })

                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href=" ' . route('receipt_school.show', ['type' => 'view', 'id' => $row->id]) . '" class="btn btn-xs btn-success btn-view" id="ViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View </a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    if (auth()->user()->id === 16) {
                        # code...
                        $html .= '<a href="#" class="btn btn-xs btn-danger btn-delete" id="DeleteData" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete</a> ';
                    }
                    //$html .= '<a href="#" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }



        $student = Student::orderBy("first_name", "asc")->get();
        $subject = Subject::orderBy("id", "asc")->get();
        $parent = ParentRegister::orderBy("id", "asc")->get();

        $school_picei_update_group = SchoolPrice::all();
        // $subject_ids = $school_picei_update_group->pluck('subject')->toArray();
        foreach ($school_picei_update_group as $upg) {
            # code...
            $group = $upg->school_sub->group;
            $upg->update(['group' => $group]);
        }

        return view('receipt_school.receipt')->with(['data2' => $student])->with(['data3' => $subject])->with(['data4' => $parent])
            ->with(['datap' => self::datap]);
    }

    public function edit($id, $type)
    {
        $data = Receipt_School::find($id);
        $data2 = ParentRegister::find($data->name_parent);


        // dd( $stds,$data2);
        // $std_schp = SchoolPrice::where('student_id',$stds->id)->get();
        //parent list
        $select_list_parent = "";
        $parent = ParentRegister::orderBy("id", "asc")->get();
        foreach ($parent as $keys) {
            if ($data->name_parent == $keys->id) {

                $select_list_parent .= '<option value="' . $keys->id . '" selected>' . $keys->code . ' ' . $keys->name_parent . '</option>';
            } else {

                $select_list_parent .= '<option value="' . $keys->id . '" >' . $keys->code . ' ' . $keys->name_parent . '</option>';
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

        // dd($receipts_detail);
        $firstId = null;
        foreach ($receipts_detail as $value) {
            if ($value->course_type == 1) {
                $firstId = $value->id;
                break;
            }
        }
        // dd($firstId);
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
            $course_type_2 = '';
            $course_type_3 = '';

            if ($dresult['course_type'] == 1) {
                $course_type_1 = 'selected';
            } elseif ($dresult['course_type'] == 2) {
                $course_type_2 = 'selected';
            } else {
                $course_type_3 = 'selected';
            }

            $course_type_select = '

                <option value="1" ' . $course_type_1 . '>Course+Book </option>
                <option value="2" ' . $course_type_2 . '>Book </option>
                <option value="3" ' . $course_type_3 . '>สื่อการสอน </option>

            ';

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

            $msg_entran = '';
            $val_entrance = 0;
            $entrance_fee = $dresult->entrance_fee;
            if ($entrance_fee > 0) {
                # code...
                $val_entrance = 1;
                $msg_entran .= 'checked';
            }
            $entance_html = '';

            $std = $dresult->student_id;
            // dd($stds);
            $stds = Student::where('pid', $data2->id)->get();
            $std_rec = "";


            // dd($std);


            if ($std) {
                $std_rec .= '<option value="' . $dresult->student_id . '" selected> ' . $dresult->std->full_name . ' </option>';
                foreach ($stds as $key => $stds_std) {
                    $std_rec .= '<option value="' . $stds_std->id . '"> ' . $stds_std->full_name . ' </option>';
                }
            } else {
                foreach ($stds as $key => $stds_std) {
                    # code...
                    $std_rec .= '<option value="" selected> เลือกนักเรียน </option>';
                    $std_rec .= '<option value="' . $stds_std->id . '" > ' . $stds_std->full_name . ' </option>';
                }
            }




            // if ($dresult->id = $firstId && $dresult->course_type == 1) {
            if ($dresult->id == $firstId) {
                $entance_html .= '
                    <div class="col-md-12 col-sm-12 col-xs-12" id="entrance_fee_1">
                        <div class="form-check">
                            <input class="form-check-input" id="entrance_fee_val_1" type="checkbox" name="entrance_fee[]" value="535" ' . $msg_entran . ' />
                            <label class="form-check-label" for="entrance_fee" >ค่าแรกเข้า</label>
                        </div>
                    </div>
                ';
            } else {
                $entance_html .= '
                    <input class="form-check-input" id="entrance_fee_val_1" type="hidden" name="entrance_fee[]" value="0" ' . $msg_entran . ' />
                ';
            }

            $data_html .= '<tr class="firstTr3">
            <td width="4%">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <input type="text" id="rno[]" name="rno[]"
                    class="form-control has-feedback-left" value="' . $x . '">
                    <input name="rrid[]"  id="rrid" type="hidden" value="' . $dresult['id'] . '" />
            </div>

            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <select style="width: 100%;" class="select2 form-control stdrec" id="student_id[]" name="student_id[]" required>
                    ' . $std_rec . '
                    </select>
                </div>
            </td>

            </td>
            <td width="9%">
            <div class="col-md-12 col-sm-12 col-xs-12">
            <select style="width: 100%;" class="ctype rrcourse_type form-control"
                id="course_type" name="course_type[]" required>
                ' . $course_type_select . '
            </select>
            </div>

            ' . $entance_html . '

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

        <td width="5%">
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
         <td width="4%">
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

         <td width="5%">
            <div class="col-md-12 col-sm-12 col-xs-12 ffff">
                <input type="number" id="rrbook" name="rrbook[]"
                class="form-control has-feedback-left" value="' . $dresult['book'] . '">
            </div>
         </td>
         <td width="5%">
         <div class="col-md-12 col-sm-12 col-xs-12 cc">
            <input type="text" id="rrbookprice" name="rrbookprice[]"
            class="rrbookprice form-control has-feedback-left" value="' . $dresult['bookprice'] . '" disabled>
         </div>
         <div class="col-md-12 col-sm-12 col-xs-12 cc">
            <input type="text" id="rrmedia_price" name="media_price[]" class="rrmedia_price form-control has-feedback-left "
                placeholder="ราคาสื่อการสอน" value="' . $dresult['media_price'] . '" autocomplete="off">

         </div>
            </td>
            <td width="7%">
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
            <td width="5%">
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
            <td width="10%">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <input type="text" id="rrperiod" name="rrperiod[]"
                class="rrperiod form-control has-feedback-left" value="' . $dresult['period'] . '">
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                            <input type="text" class="rrmedia_name form-control" name="media_name[]"
                                                                id="rrmedia_name" value="' . $dresult['media_name'] . '"
                                                                placeholder="ชื่อสื่อการสอน" autocomplete="off">

                                                        </div>
            </td>
            <td width="8%">
            <div class="col-md-12 col-sm-12 col-xs-12 acdc">
            <input type="text" id="rrtotal" name="rrtotal[]"
            class="form-control has-feedback-left" value="' . $dresult['total'] . '">

            </div>
                    <input type="hidden" id="entrace_edit_' . $val_entrance . '" name="entrace_edit" value="' . $val_entrance . '">

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
            'data' =>  $data,
            'data2' => $data2,
            'student' => $student,
            'data_html' => $data_html,
            'imgs' => $html,
            'payment' => $payment,
            'select_list_parent' => $select_list_parent
        ]);
    }

    public function show($type, $id)
    {
        // $url = route('receipt_school.show', ['type' => 'view', 'id' => $id]);
        // dd($url);
        $data = Receipt_School::find($id);



        $dataDate = date('Y-m-d', strtotime($data->created_at));
        $fixDate = date('Y-m-d', strtotime('2024-09-09'));
        $payment_type = $data->payment_type;
        // dd(
        //     $data,
        //     $dataDate,
        //     $fixDate,
        //     $payment_type,
        // );


        if ($type == "view") {
            $viewr = "receipt.view1";
        } else if ($type == "print") {
            $viewr = "receipt.print1";
        }
        $mes = "";
        $mes2 = "";
        $text = "";
        $text2 = "";
        $file_att = "";
        $typeRec = $data->type;
        $recNumber = $data->receipt_number; // its 012405001
        $firstTwoDigits = substr($recNumber, 0, 2);
        $group_rec = '';

        if ($firstTwoDigits === '01' || $firstTwoDigits === '71') {
            # code...
            $group_rec = 'chn';
        } elseif ($firstTwoDigits === '03' || $firstTwoDigits === '73') {
            # code...
            $group_rec = 'sci';
        } else {
            # code...
            $group_rec = '';
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

        $test = [];
        // dd($receipts_detail);
        foreach ($receipts_detail as $dresult) {
            if ($dresult['course_type'] == 2) {
                $price = $dresult['bookprice'];
                $mes = "ใบกำกับภาษี/ใบเสร็จรับเงิน";

                $group_rec = '';
            } elseif ($dresult['course_type'] == 3) {
                $price = $dresult['media_price'];
                $mes = "ใบกำกับภาษี/ใบเสร็จรับเงิน";

                $group_rec = '';
            } else {
                $price = ($dresult['total'] - $dresult['bookprice']);
            }

            // dd($dresult->total);
            $message = "";

            if ($dresult['week'] == 1 || $dresult['week'] == 2 || $dresult['week'] == 3) {
                $message = " สัปดาห์";

                if ($dresult['group'] === 'Sci') {
                    # code...
                    $qty = $dresult['qty']; // fix new line

                } else {

                    $qty = $dresult['week']; // fix new line
                }

                if ($dresult['course_type'] == 3) {
                    # code...
                    $message = "ชุด";
                }
            } else {
                if ($dresult['group'] === 'CHN') {
                    $message = "คอร์ส";
                } else {
                    # code...
                    $message = "เดือน";
                }
                $qty = $dresult['qty'];
            }


            // dd($qty);

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
                $receipt_detail_arr[$x]['media_name'] = '';
                $receipt_detail_arr[$x]['entrance_fee'] = '';

                $receipt_detail_arr[$x]['course_type'] = $dresult['course_type'];
            } else {
                $receipt_detail_arr[$x]['no'] = $no;
                $receipt_detail_arr[$x]['price'] = number_format($price - $dresult['entrance_fee'], 2);
                $receipt_detail_arr[$x]['unit'] = $message;
                $receipt_detail_arr[$x]['course_type'] = $dresult['course_type'];
                $receipt_detail_arr[$x]['entrance_fee'] = $dresult['entrance_fee'];

                $receipt_detail_arr[$x]['media_name'] = $dresult['media_name'];

                if ($dresult['course_type'] == 2) {

                    $receipt_detail_arr[$x]['unitprice'] = number_format(($dresult['total'] - $dresult['discount']) / $dresult['book'], 2);
                } elseif ($dresult['course_type'] == 3) {
                    $receipt_detail_arr[$x]['unitprice'] = number_format(($dresult['media_price']), 2);
                } else {
                    $receipt_detail_arr[$x]['unitprice'] = number_format(($dresult['total'] + $dresult['discount'] - $dresult['bookprice'] - $dresult['entrance_fee']) / $dresult['qty'], 2);
                }
                $receipt_detail_arr[$x]['quantity'] =  $qty; //$dresult['qty'];
                $receipt_detail_arr[$x]['discount'] = number_format($dresult['discount'], 2);
                $receipt_detail_arr[$x]['book'] = ($dresult['book']);
                $receipt_detail_arr[$x]['bookprice'] = $dresult['bookprice'];
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


            $pricesub = $pricesub + (($dresult['total']) - $dresult['bookprice'] - $dresult['media_price']);
            //- $dresult['discount']

            // $receipt_detail_arr[$x]['books'] = $bookp->book_price;
            $receipt_detail_arr[$x]['books'] = $dresult['bookprice'] / $dresult['book'];
            $receipt_detail_arr[$x]['description'] = $sub->title . "  " . "Level " . $titless->level_sort;
            $booksum = $booksum + $dresult['bookprice'] + $dresult['media_price'];
            $discount = $discount + $dresult['discount'];
            $all_price =  $booksum + $pricesub;
            //- $discount
            $test[$x] = $dresult['bookprice'];

            $no++;
            $x++;
        }
        //exit();
        // dd($booksum , $dresult['bookprice'] , $dresult['media_price'] ,$dresult['entrance_fee'],$test);

        // dd($booksum,$dresult['bookprice'],$dresult['media_price'],$dresult['entrance_fee']);


        $price_text = $this->baht_text($all_price);
        $price_texten = $this->convertNumber($all_price);
        $vat_price = ($pricesub * 100) / 107;
        $vat_total = $pricesub - $vat_price;
        // dd($pricesub);
        $vat_price_val = number_format($vat_price, 2);
        $vat_val = number_format($vat_total, 2);

        $price_val = number_format(($vat_price + $vat_total) + $booksum, 2);

        $student = "";
        $student_code = "";
        $stu = explode('|', $data->pid);
        if ($data->pid != "") {
            foreach ($stu as $stus) {
                $stus = Student::where('id', $stus)->get();
                $student .= $stus[0]->nick_name . "/";
                $student_code .= $stus[0]->code . "-";
            }
            $student = rtrim($student, "/");
            $student_code = rtrim($student_code, "-");
        }


        $data2 = ParentRegister::find($data->name_parent);
        $html = "";
        $pic = explode('|', $data->file_att);


        foreach ($pic as $pics) {

            //$this->textWatermark($pics, 'Receipt Number : ' . $data->receipt_number);

            //$retu = str_replace('receipt_form_images', 'receipt_form_watermark_images', $urla);

            // $html .= '<img src="' . url('/') . '/receipts_form_school/' . $pics . '" class="me-3 mt-5 mb-5" height="400" >';
            $html .= '<div class="image-container">
            <img src="' . url('/') . '/receipts_form_school/' . $pics . '">
          </div>';
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

        if ($data->type == 2) {
            $mes = "ใบลดหนี้";
            $text = "Credit Note";
        } elseif ($data->type == 9 || $group_rec) {
            # code...

            if ($payment_type == 1 && $fixDate < $dataDate) {
                # code...
                $mes = "ใบเสร็จรับเงิน";
                $text = "Receipt";
                $vat_price_val = number_format(0, 2);
                $vat_val = number_format(0, 2);
                $booksum = $booksum + $pricesub;
            } else {
                # code...
                $mes = "ใบกำกับภาษี/ใบแจ้งหนี้";
                $mes2 = "ใบเสร็จรับเงิน";
                $text = "Tax Invoice/Invoice";
                $text2 = "Invoice";
            }
        } else {
            if ($payment_type == 1 && $fixDate < $dataDate) {
                # code...
                $mes = "ใบเสร็จรับเงิน";
                $text = "Receipt";
                $vat_price_val = number_format(0, 2);
                $vat_val = number_format(0, 2);
                $booksum = $booksum + $pricesub;
            } else {
                # code...
                $mes = "ใบกำกับภาษี/ใบเสร็จรับเงิน";
                $text = "Tax Invoice/Receipt";
            }
        }

        // if ($payment_type == 1) {
        //     # code...
        //     $vat_price_val = number_format(0, 2);
        //     $vat_val = number_format(0, 2);
        //     $booksum = $booksum + $pricesub;
        // }

        return view($viewr)->with(['id' => $id])
            ->with(['discount' => $discount])
            ->with(['vat_price' => $vat_price])
            ->with(['booksum' => $booksum])
            ->with(['html' => $html])
            ->with(['data' => $data])
            ->with(['data2' => $data2])
            ->with(['student' => $student])
            ->with(['student_code' => $student_code])
            ->with(['bookprice' => $booktotal->book_price])
            ->with(['receipt_detail_arr' => $receipt_detail_arr])
            ->with(['price_val' => $price_val])
            ->with(['mes' => $mes])
            ->with(['mes2' => $mes2])
            ->with(['text' => $text])
            ->with(['text2' => $text2])
            ->with(['price_texten' => $price_texten])
            ->with(['price_text' => $price_text])
            ->with(['vat_price_val' => $vat_price_val])
            ->with(['vat_val' => $vat_val])
            ->with(['payment' => $payment])
            ->with(['course_type' => $data->course_type])
            ->with(['typeRec' => $typeRec])
            ->with(['group_rec' => $group_rec])
            ->with(['file_att' => $file_att]);
    }

    public function find($parent)
    {
        $data = Student::where('pid', $parent)->orderBy("id", "asc")->get();
        $select_list = "";

        // $select_list1 = '';
        $select_list1 = '<option value="">เลือกชื่อนักเรียน</option>';
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

        foreach ($data as $key) {

            $select_list1 .= '
            <option value="' . $key->id . '" > ' . $key->first_name . ' ' . $key->last_name . '</option>
            ';
        }

        return response()->json([
            'html' =>  $select_list,
            'html2' => $mobile,
            'html3' => $address,
            'html4' => $postcode,
            'html5' => $select_list1
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

    public function searchs($sub_id, $level_id, $week, $price, $qty, $discount, $book_price, $type, $course_type, $entrance_fee)
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
            if ($sub_id < 7) {
                # code...
                $pricet = (((int)$select_list * (int)$qty) + ((int)$book_price * 1)) - (int)$discount + $entrance_fee;
            } else {
                $pricet = (((int)$select_list * (int)$qty) + ((int)$book_price * 1)) - (int)$discount + $entrance_fee;
            }
        }

        // $entrace_edit = 0;
        if ($entrance_fee > 1) {
            # code...
            $output .= '<input type="text" id="pp" name="pp" class="form-control has-feedback-left" value="' . $pricet . '" >
        <input type="hidden" id="enhide" name="enhide" class="form-control has-feedback-left" value="1" >
        ';
            $outputsum .= '' . $pricet . '';
            // $entrace_edit = 1;
        } else {
            $output .= '<input type="text" id="pp" name="pp" class="form-control has-feedback-left" value="' . $pricet . '" >
            ';
            $outputsum .= '' . $pricet . '';
            //
        }
        return response()->json([
            'html' =>  $output,
            'outputsum' => $outputsum,
            // 'entrace_edit' => $entrace_edit
        ]);
    }

    public function sumbook($sub_id, $level_id, $book, $week, $course_type)
    {


        // dd($sub_id, $level_id, $book, $course_type);
        $data = SubjectPrice::where(['subject_id' => $sub_id, 'level_id' => $level_id, 'week' => $week])->get();
        // $data1 = SubjectPrice::where(['subject_id' => $sub_id, 'level_id' => $level_id])->get();
        // dd(
        //     $sub_id,
        //     $level_id,
        //     $book,
        //     $course_type,
        //     'week : '.$week,
        //     $data,
        //     $data1
        // );
        // dd($sub_id, $level_id, $week, $course_type,$data);
        // $select_list = "";
        // $booklet = "";
        // foreach ($data as $key) {

        $select_list = $data[0]->book_price;
        $booklet = $data[0]->book;
        // }

        // dd($data, $select_list, $booklet);

        if ($course_type == '1') {
            # code...
            $output = "";
            $output = '<input type="text" id="book_price" name="book_price" class="form-control has-feedback-left" value="' . ((int)$select_list * (int)$book) * $booklet . ' "readonly >
            ';
            $bp = ((int)$select_list * (int)$book) * $booklet;
            // $sum = "";
            // $sum = '' . ((int)$select_list * (int)$book) * 15 . ' ';
        } else {
            $output = "";
            $output = '<input type="text" id="book_price" name="book_price" class="form-control has-feedback-left" value="' . (int)$select_list * (int)$book . ' "readonly >
            ';
            $bp = ((int)$select_list * (int)$book) * 1;
        }

        $sum = "";
        $sum = '' . (int)$select_list * (int)$book . ' ';
        // dd($sub_id, $level_id, $book,$sum);
        return response()->json([
            'html' =>  $output,
            'sum' => $sum,
            'bp' => $bp
        ]);
    }

    public function store(Request $request)
    {

        // dd(
        //     $request->payment_type,
        //     $request
        // );
        $payment_type = $request->payment_type;

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
                'receipt.*.std_id' => 'required',
                // 'receipt.*.period' => 'required',

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
                'receipt.*.std_id.required' => 'ระบุชื่อ นร ในรายการใบเสร็จ',
                // 'receipt.*.period.required' => 'The period field is required.  !',
                //'receipt.*.period.required' => 'The Period field is required.  !',

            ]
        );
        $subjects = collect($request->input('receipt'))->pluck('subject')->toArray();

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        $groupsub = Subject::whereIn('id', $subjects)->get();
        $group = $groupsub->first()->group;

        $hasPromotion = $groupsub->contains('promotion', 'yes');
        $hasNonPromotion = $groupsub->contains('promotion', 'no');

        if ($hasPromotion && $hasNonPromotion) {
            # code...
            return response()->json(['errors' => ["ไม่สามารถทำรายการโปรโมชั่นร่วมกับรายการปกติได้"]]);
        }

        $typeRec = (bool)$hasPromotion ? 9 : 0;

        // dd($typeRec);
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
        // dd($payment_type);
        $rnumber = Receipt_School_RunningNumber::generate($group, $payment_type == 1 ? '1' : '0');


        $data = new Receipt_School([
            'submission_date' => $request->get('submission_date'),
            'payment_type' => $request->get('payment_type'),
            'street_address' => $request->get('street_address'),
            'postcode' => $request->get('postcode'),
            'mobile' => $request->get('mobile'),
            'pid' => $stu,
            'name_parent' => $request->get('name_parent'),
            'type' => $typeRec,
            // 'sid' => $request->get('student'),
            'receipt_number' => $rnumber,
            'file_att' => $filebs,
            'approv_id' => auth()->user()->id,
            'approv_name' => auth()->user()->name,

        ]);
        $data->save();
        $dataArray = $data->toArray();
        $dataArray2 = [];
        $rid = $data->id;
        // print_r($request->get('receipt'));
        foreach ($request->get('receipt') as $value) {

            if (isset($value['entrance_fee'])) {
                $entranceFee = $value['entrance_fee'];
            } else {
                $entranceFee = null;
            }

            $grp = Subject::find($value['subject']);

            $data2 = new SchoolPrice([
                'subject' => $value['subject'],
                'group' => $grp->group,
                'course_type' => $value['course_type'],
                'student_id' => $value['std_id'],
                'level' => $value['level'],
                'week' => $value['week'],
                'qty' => $value['qty'],
                'book' => $value['book'],
                'bookprice' => $value['book_price'],
                'discount' => $value['discount'],
                'price' => $value['price'],
                'media_name' => $value['media_name'],
                'media_price' => $value['media_price'],
                'entrance_fee' => $entranceFee,
                'total' => $value['pp'],
                'rid' => $rid,
                'type' => $value['type'],
                'receipt_number' => 0,
                'period' => $value['period'],
                'approv_id' => auth()->user()->id,
                'approv_name' => auth()->user()->name,

            ]);
            // print_r($data2['subject']);
            // exit();
            $data2->save();
            //merge $data2 to data dataArray
            $dataArray2[] = $data2->toArray();

            # code...
            $group = $data2->school_sub->group;
            $data2->update(['group' => $group]);


            $period = explode(' - ', $value['period']);
            // dd($last_period,$period);

            if (count($period) == 1) {
                $periods = explode('-', $value['period']);
                $startMonth = Carbon::parse("$periods[0]-$periods[1]")->format('Y-m');
                $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;
            } else {
                $startMonth = Carbon::parse($period[0])->format('Y-m');
                $endMonth = Carbon::parse($period[1])->format('Y-m');
            }
            // $endMonth = Carbon::parse($endMonth)->addMonth()->format('Y-m');

            if ($startMonth === $endMonth) {
                $startMonth = Carbon::parse($endMonth)->startOfMonth()->format('Y-m-d');
                $endMonth = Carbon::parse($endMonth)->endOfMonth()->format('Y-m-d');
            }

            $his_status = History::where('student_id', $value['std_id'])
                ->whereBetween('datetime', [$startMonth, $endMonth])
                ->get();
            if ($his_status->count() > 0) {
                # code...
                foreach ($his_status as  $his_statuss) {
                    # code...
                    $his_statuss->update(['status' => 'reciept', 'school_price_id' => $data2->id]);
                }
            }

            //update status
            $students = Student::where('id', $value['std_id'])->get();

            // $updateStatus = $this->updateStatus($students);
            $updateStatus = $this->statusService->updateStatus($students);

        }

        //merge $dataArray and $dataArray2 ;
        $dataMerge = array_merge($dataArray, ['receipts' => $dataArray2]);

        LogActivity::modelEvent($data, 'receipt created', null, $dataMerge);

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
        // dd($request);
        $validator =  Validator::make(
            $request->all(),
            [
                'submission_date' => 'required|string',
                'payment_type' => 'required|string',
                'student_id' => 'required|array', // Validate that it's an array
                'student_id.*' => 'required', // Validate each element in the array

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
            ],
            [

                'student_id.*.required' => 'ระบุชื่อ นร ในรายการใบเสร็จ',

            ]
        );
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
        $subjects = collect($request->get('rrsub', []))->toArray();

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $groupsub = Subject::whereIn('id', $subjects)->get();

        $hasPromotion = $groupsub->contains('promotion', 'yes');
        $hasNonPromotion = $groupsub->contains('promotion', 'no');
        $typeRec = (bool)$hasPromotion ? 9 : 0;

        if ($hasPromotion && $hasNonPromotion) {
            # code...
            return response()->json(['errors' => ["ไม่สามารถทำรายการโปรโมชั่นร่วมกับรายการปกติได้"]]);
        }


        $dataimg = Receipt_School::find($id_parent);
        if ($dataimg->file_att != Null && $dataimg->file_att != "") {
            $stu = $dataimg->file_att . "|" . $stu;

            $stu = rtrim($stu, "|");
            Receipt_School::find($id_parent)->update(['file_att' => $stu, 'type' => $typeRec]);
        } else {
            Receipt_School::find($id_parent)->update(['file_att' => $stu, 'type' => $typeRec]);
            return response()->json(['success' => 'Receipt updated successfully']);
        }
        // 
        $rrid  = $request->get('rrid');
        $std_id  = $request->get('student_id');
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
        $media_price = $request->get('media_price');
        $media_name = $request->get('media_name');
        $entrance_fee = $request->get('entrance_fee');


        //add student to request
        // media_price
        $request->request->add(['pid' => $stud]);

        $Receipt_School =  Receipt_School::find($id);
        $Receipt_School_old = $Receipt_School->toArray();
        $Receipt_School->update($request->all());
        $Receipt_School_arry = $Receipt_School->toArray();

        $rdata = array();

        $SchoolPrice = SchoolPrice::where('rid', $id)->get();
        $SchoolPrice_old = $SchoolPrice->toArray();
        foreach ($SchoolPrice as $price) {
            $price->delete();
        }
        $SchoolPrice_new = [];
        for ($xx = 0; $xx < count($rrsub); $xx++) {
            $grp = Subject::find($rrsub[$xx]);

            if ($rrcourse[$xx] === '3') {
                $rrbookprice[$xx] = null;
            } else {
                $media_name[$xx] = null;
                $media_price[$xx] = null;
            }

            if ($rrtype[$xx] != "" && $rrtype[$xx] != NULL) {

                $rdata = new SchoolPrice([
                    'rid' => $id,
                    'student_id' => $std_id[$xx],
                    'subject' => $rrsub[$xx],
                    'course_type' => $rrcourse[$xx],
                    'group' => $grp->group,
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
                    'media_name' => $media_name[$xx],
                    'media_price' => $media_price[$xx],
                    'entrance_fee' => $entrance_fee[$xx],
                    'approv_id' => auth()->user()->id,
                    'approv_name' => auth()->user()->name,

                    //'amount' => $request->get('book_old_price'),
                ]);
            } else {

                $rdata = new SchoolPrice([
                    'rid' => $id,
                    'student_id' => $std_id[$xx],
                    'subject' => $rrsub[$xx],
                    'course_type' => $rrcourse[$xx],
                    'level' => $rrlevel[$xx],
                    'group' => $grp->group,
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
                    'media_name' => $media_name[$xx],
                    'media_price' => $media_price[$xx],
                    'entrance_fee' => $entrance_fee[$xx],
                    'approv_id' => auth()->user()->id,
                    'approv_name' => auth()->user()->name,

                    //'amount' => $request->get('book_old_price'),
                ]);
            }
            // dd($entrance_fee);

            // print_r($rrtype);
            // exit();
            $rdata->save();
            $SchoolPrice_new[] = $rdata->toArray();
            $students = Student::where('id', $std_id[$xx])->get();

            $updateStatus = $this->statusService->updateStatus($students);
        }
        $dataMergeOld = array_merge($Receipt_School_old, ['receipts' => $SchoolPrice_old]);
        // dd($dataMergeOld);
        $dataMergeNew = array_merge($Receipt_School_arry, ['receipts' => $SchoolPrice_new]);
        LogActivity::modelEvent($Receipt_School, 'receipt updated', $dataMergeOld, $dataMergeNew);

        return response()->json(['success' => 'Receipt updated successfully']);
    }

    public function destroy($id)
    {
        $data = Receipt_School::find($id);

        if (!$data) {
            return response()->json(['error' => 'Receipt not found'], 404);
        }

        SchoolPrice::where('rid', $id)->delete(); // Delete related data
        $data->delete(); // Delete main receipt

        return response()->json(['success' => 'Receipt deleted successfully']);
    }
}
