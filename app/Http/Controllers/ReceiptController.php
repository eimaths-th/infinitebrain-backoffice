<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
//use Auth;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ReceiptImport;
use App\Exports\UsersExport;
use App\Models\Receipt_RunningNumber;
use App\Models\Receipt_Customer_RunningNumber;
use Illuminate\Support\Facades\DB;
use mysqli;

class ReceiptController extends Controller
{

    const BAHT_TEXT_NUMBERS = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
    const BAHT_TEXT_UNITS = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
    const BAHT_TEXT_ONE_IN_TENTH = 'เอ็ด';
    const BAHT_TEXT_TWENTY = 'ยี่';
    const BAHT_TEXT_INTEGER = 'ถ้วน';
    const BAHT_TEXT_BAHT = 'บาท';
    const BAHT_TEXT_SATANG = 'สตางค์';
    const BAHT_TEXT_POINT = 'จุด';
    const sfielda = array('2' => 'GMEC', '4' => 'IMOCSEA', '5' => 'INFINITE Spelling Bee', '6' => 'GELOSEA', '7' => 'LIMOC', '8' => 'STEM');
    const sfieldas = array('2' => 'G', '4' => 'J', '5' => 'I', '6' => 'L', '7' => 'M', '8' => 'NS');
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

    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 3) {
                return true;
            } else {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //

        $this->check_permission();
        if ($request->sfield == "") {
            abort(403, 'Please input Field');
        }
        if ($request->ajax()) {
            //sleep(2);
            $receipt = Receipt::select(
                "receipts.id as receipt_id",
                "receipts.submission_date as receipt_submission_date",
                "receipts.receipt_number as receipt_number",
                "receipts.receipt_customer_number as receipt_customer_number",
                "receipts.first_name as receipt_firstname",
                "receipts.last_name as receipt_lastname",
                "receipts.name_parent as receipt_parent_name",
                "receipts.mobile as receipt_mobile",
            )
                //->join("students", "students.id", "=", "receipts.student_id")
                //->join("subjects", "subjects.id", "=", "receipts.subject_id")
                //->join("levels", "levels.id", "=", "receipts.level_id")

                ->where('receipts.sfield', $request->sfield)
                ->orderBy("receipts.id", "desc")
                ->get();
            //print_r($levels);
            //exit();
            return datatables()->of($receipt)
                /* ->editColumn('student_name', function (Receipt $receipt) {
                    return $receipt->student_name . ' ' . $receipt->student_lastname;
                })*/
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->receipt_id . '" class="flat" name="table_records[]" value="' . $row->receipt_id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="receipts/show/view/' . $row->receipt_id . '" class="btn btn-xs btn-success btn-view" id="ViewData" data-id="' . $row->receipt_id . '"><i class="fa fa-eye"></i> View Receipt</a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->receipt_id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        //get subject
        //$subject = Subject::orderBy("id", "asc")->get();

        //get student
        //$student = Student::orderBy("name", "asc")->get();

        return view('receipt.index')
            ->with(['sfielda' =>  self::sfielda])
            ->with(['sfield' => $request->sfield]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //create
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
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'submission_date' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'street_address' => 'required|string',
            //'city' => 'required|string',
            //'province' => 'required|string',
            'postcode' => 'required|string',
            'name_parent' => 'required|string',
            'mobile' => 'required|string',
            'package' => 'required|string',
            'file_att' => 'required|string',
            'description' => 'required|string',
            'grade' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $rnumber = Receipt_RunningNumber::generate($request->get('sfield'));
        $rcnumber = Receipt_Customer_RunningNumber::generate($request->get('sfield'));
        $request->request->add(['receipt_number' => $rnumber, 'receipt_customer_number' => $rcnumber]);

        $course = new Receipt($request->all());
        $course->save();
        return response()->json(['success' => 'Receive Add successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function show($type, $id)
    {
        //
        $this->check_permission();
        $data = Receipt::find($id);

        //print_r($data);
        //exit();

        if ($type == "view") {
            $viewr = "receipt.view";
        } else if ($type == "print") {
            $viewr = "receipt.print";
        }

        if ($data->package) {
            $res = preg_replace("/[^0-9.]/", "", $data->package);
        }

        $price = (int)$res;
        $price_val = number_format($price, 2);
        $price_text = $this->baht_text($price);
        $vat_price = ($price * 100) / 107;
        $vat_total = $price - $vat_price;
        $vat_price_val = number_format($vat_price, 2);
        $vat_val = number_format($vat_total, 2);
        //exit();

        $file_att = '';
        if ($data->file_att) {

            $arrl = explode('https', $data->file_att);
            for ($i = 1; $i <= count($arrl) - 1; $i++) {
                $urla = "https" . $arrl[$i];
                $retu = $this->get_img_url($urla);
                //if ($this->URL_exists($retu)) {
                $file_att .= "<img src=\"" . $retu . "\" height=\"400\">&nbsp;&nbsp;";
                //}
            }
        }


        $des_text = nl2br($data->description);



        return view($viewr)->with(['id' => $id])->with(['data' => $data])->with(['price_val' => $price_val])
            ->with(['price_text' => $price_text])->with(['vat_price_val' => $vat_price_val])
            ->with(['vat_val' => $vat_val])->with(['file_att' => $file_att])
            ->with(['des_text' => $des_text]);
    }

    public function print_day(Request $request)
    {
        //
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'pdsfield' => 'required|string',
            'print_date' => 'required|string',
            'print_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()->all()]);
            return back()->with(['error' => 'Please input print day']);
        }

        $type = $request->get('type');

        if ($type == "view") {
            $viewr = "receipt.print_day_view";
        } else if ($type == "print") {
            $viewr = "receipt.print_day_print";
        }

        $sfield = $request->get('pdsfield');

        $a_date = explode("-", $request->get('print_date'));

        $ymcode = (($a_date[0] - 2000) + 43) . $a_date[1];
        $thai_year = $a_date[0] + 543;
        $thai_month = self::thmonth[(int)$a_date[1]];

        $receipts = Receipt::select(
            "receipts.submission_date as receipt_submission_date",
            "receipts.receipt_number as receipt_number",
            "receipts.first_name as receipt_firstname",
            "receipts.last_name as receipt_lastname",
            "receipts.package as receipt_price",
        )
            //->join("students", "students.id", "=", "receipts.student_id")
            //->join("subjects", "subjects.id", "=", "receipts.subject_id")
            //->join("levels", "levels.id", "=", "receipts.level_id")

            ->where([['receipts.sfield', $sfield], ['submission_date', $request->get('print_date')]])
            ->orderBy("receipts.receipt_number", "asc")
            ->get();
        //->toArray();
        //print_r($receipts);
        //exit();
        $i = 0;
        $ret_receipt = array();
        $total_price = 0;
        foreach ($receipts as $result) {
            $ret_receipt[$i]['receipt_submission_date'] = $result->receipt_submission_date;
            $ret_receipt[$i]['receipt_number'] = $result->receipt_number;
            $ret_receipt[$i]['receipt_firstname'] = $result->receipt_firstname;
            $ret_receipt[$i]['receipt_lastname'] = $result->receipt_lastname;

            $res = preg_replace("/[^0-9.]/", "", $result->receipt_price);
            $price = (int)$res;
            $price_val = number_format($price, 2);
            $ret_receipt[$i]['receipt_price'] = $price_val;
            $ret_receipt[$i]['receipt_price_label'] = "บาท";
            $i++;
            $total_price += $price;
        }

        if (count($ret_receipt) < 19) {
            $start = $i;
            $em = 19 - count($ret_receipt) + $start;
            for ($x = $start; $x <= $em; $x++) {
                $ret_receipt[$x]['receipt_submission_date'] = "";
                $ret_receipt[$x]['receipt_number'] = "";
                $ret_receipt[$x]['receipt_firstname'] = "";
                $ret_receipt[$x]['receipt_lastname'] = "";
                $ret_receipt[$x]['receipt_price'] = "";
                $ret_receipt[$x]['receipt_price_label'] = "";
            }
        }
        //echo $start;
        //print_r($ret_receipt);
        //exit();




        return view($viewr)->with(['sfield' => $sfield])->with(['sfielda' =>  self::sfielda])
            ->with(['sfieldas' =>  self::sfieldas])
            ->with(['ymcode' =>  $ymcode])
            ->with(['thaiyear' =>  $thai_year])
            ->with(['thaimonth' =>  $thai_month])
            ->with(['data' => $ret_receipt])
            ->with(['pdsfield' => $request->get('pdsfield')])
            ->with(['print_date' => $request->get('print_date')])
            ->with(['total_price' => number_format($total_price, 2)])
            ->with(['print_number' =>  $request->get('print_number')])
            ->with(['fprint_number' =>  sprintf("%02d", $request->get('print_number'))]);
    }

    public function print_month(Request $request)
    {
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'pmsfield' => 'required|string',
            'print_month' => 'required|string',
            'Pmdescription' => 'required|string',
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()->all()]);
            return back()->with(['error' => 'Please input all field ']);
        }

        $type = $request->get('type');

        if ($type == "view") {
            $viewr = "receipt.print_month_view";
        } else if ($type == "print") {
            $viewr = "receipt.print_month_print";
        }

        $sfield = $request->get('pmsfield');

        $a_month = explode("-", $request->get('print_month'));

        $ymcode = (($a_month[0] - 2000) + 43) . $a_month[1];
        $thai_year = $a_month[0] + 543;
        $thai_month = self::thmonth[(int)$a_month[1]];

        $receipts = Receipt::select(
            "receipts.submission_date as receipt_submission_date",
            "receipts.receipt_number as receipt_number",
            "receipts.first_name as receipt_firstname",
            "receipts.last_name as receipt_lastname",
            "receipts.package as receipt_price",
        )
            //->join("students", "students.id", "=", "receipts.student_id")
            //->join("subjects", "subjects.id", "=", "receipts.subject_id")
            //->join("levels", "levels.id", "=", "receipts.level_id")
            ->where([['receipts.sfield', $sfield], ['submission_date', 'LIKE', $request->get('print_month') . '%']])
            ->orderBy("receipts.submission_date", "asc")
            ->get();
        //->toArray();
        //print_r($receipts);
        //exit();
        $i = 0;
        $ret_receipt = array();
        $total_price = 0;
        $lastdate = "";
        $lastprice = 0;
        foreach ($receipts as $result) {

            if ($result->receipt_submission_date !== $lastdate) {
                $i = 0;
                $lastprice = 0;
            }

            $ret_receipt[$result->receipt_submission_date]['receipt_submission_date'] = $result->receipt_submission_date;
            $ret_receipt[$result->receipt_submission_date]['receipt_number'][$i] = $result->receipt_number;
            $maxdate = max(array_keys($ret_receipt[$result->receipt_submission_date]['receipt_number']));
            $ret_receipt[$result->receipt_submission_date]['receipt_numbers'] = $ret_receipt[$result->receipt_submission_date]['receipt_number'][0] . ' - ' . $ret_receipt[$result->receipt_submission_date]['receipt_number'][$maxdate];

            $res = preg_replace("/[^0-9.]/", "", $result->receipt_price);
            $price = (int)$res;

            $ret_receipt[$result->receipt_submission_date]['description'] = $request->get('Pmdescription');
            $ret_receipt[$result->receipt_submission_date]['receipt_price'] = $price;
            $ret_receipt[$result->receipt_submission_date]['receipt_price_label'] = "บาท";
            $lastprice = $lastprice + $ret_receipt[$result->receipt_submission_date]['receipt_price'];

            $ret_receipt[$result->receipt_submission_date]['total_price'] =  $lastprice;

            $lastdate = $ret_receipt[$result->receipt_submission_date]['receipt_submission_date'];
            $i++;

            $total_price += $price;
        }

        if (count($ret_receipt) < 19) {
            $start = $i;
            $em = 19 - count($ret_receipt) + $start;
            for ($x = $start; $x <= $em; $x++) {
                $ret_receipt[$x]['receipt_submission_date'] = "";
                $ret_receipt[$x]['receipt_numbers'] = "";
                $ret_receipt[$x]['total_price'] = "";
                $ret_receipt[$x]['receipt_firstname'] = "";
                $ret_receipt[$x]['receipt_lastname'] = "";
                $ret_receipt[$x]['receipt_price'] = "";
                $ret_receipt[$x]['receipt_price_label'] = "";
                $ret_receipt[$x]['description'] = "";
            }
        }

        //print_r($ret_receipt);

        return view($viewr)->with(['sfield' => $sfield])->with(['sfielda' =>  self::sfielda])
        ->with(['sfieldas' =>  self::sfieldas])
        ->with(['ymcode' =>  $ymcode])
        ->with(['thaiyear' =>  $thai_year])
        ->with(['thaimonth' =>  $thai_month])
        ->with(['data' => $ret_receipt])
        ->with(['pmsfield' => $request->get('pmsfield')])
        ->with(['pmdes' => $request->get('Pmdescription')])
        ->with(['print_month' => $request->get('print_month')])
        ->with(['total_price' => number_format($total_price, 2)]);
        //->with(['print_number' =>  $request->get('print_number')])
        //->with(['fprint_number' =>  sprintf("%02d", $request->get('print_number'))]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $this->check_permission();
        $data = Receipt::find($id);

        return response()->json([
            'data' =>  $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'submission_date' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'street_address' => 'required|string',
            //'city' => 'required|string',
            //'province' => 'required|string',
            'postcode' => 'required|string',
            'name_parent' => 'required|string',
            'mobile' => 'required|string',
            'package' => 'required|string',
            'file_att' => 'required|string',
            'description' => 'required|string',
            'grade' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        Receipt::find($id)->update($request->all());

        return response()->json(['success' => 'Receipt updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $this->check_permission();
        Receipt::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Receipt::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        //return redirect('/receipt/?id=' . $request->get('id'))->with('success', 'Deleted Successfully');
        return redirect('/receipts')->with('success', 'Deleted Successfully');
    }

    public function importExcel(Request $request)
    {
        if ($request->hasFile('import_file')) {
            //$path = $request->file('import_file')->getRealPath();

            $path = $request->file('import_file');

            //$request->file('file')->store('files')
            Excel::import(new ReceiptImport($request->get('sfield')), $path);
            return redirect()->back()->with('success', 'Import Data Complete');
        } else {
            return back()->with(['error' => 'Please Check your file, Something is wrong there.']);
        }


        return back()->with('error', 'Please Check your file, Something is wrong there.');
    }
}
