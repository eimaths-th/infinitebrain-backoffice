<?php

namespace App\Http\Controllers;

use App\Models\LottoMain;
use App\Models\LottoRecord;
use App\Models\lottoReport;
use App\Models\LottoResult;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Validators\Failure;

class LottoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $types = LottoMain::orderBy("id", "desc")->get();
            return datatables()->of($types)
                ->editColumn('type', function (LottoMain $type) {
                    $text = '';
                    if ($type->type === '0') {
                        # code...
                        $text = 'โต๊ด';
                    } elseif ($type->type === '1') {
                        # code...
                        $text = 'เต็ง';
                    } else {
                        # code...
                        $text = 'Normal';
                    }

                    return $text;
                })
                ->editColumn('max', function (LottoMain $max) {
                    return number_format($max->max) . ' บาท';
                })
                ->editColumn('remain', function (LottoMain $remain) {
                    return number_format($remain->remain) . ' บาท';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })
                ->toJson();
        }

        return view('lottoes.main');
    }
    public function result(Request $request)
    {
        if ($request->ajax()) {
            $types = LottoResult::orderBy("id", "desc")->get();
            return datatables()->of($types)

                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })
                ->toJson();
        }

        return view('lottoes.results');
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

    function normalizeNumber($number)
    {
        $digits = str_split($number);
        sort($digits);
        return implode('', $digits);
    }

    public function store(Request $request)
    {
        //
        $type = $request->type;

        if ($type === '0') {

            $validatedData = $request->validate([
                'type' => 'required',
                'max' => 'required',
                'length' => 'required|integer|min:0|max:3',
            ]);
        } else {

            $validatedData = $request->validate([
                'type' => 'required',
                'max' => 'required',
                'numbers' => 'required',
            ]);
        }


        if ($type === '0') {

            $uniqueNumbers = array();
            $lengthNumbers = $request->length;

            // $loop = 10 ** $lengthNumbers;
            $loop = pow(10, $lengthNumbers);

            for ($i = 0; $i < $loop; $i++) {
                $number = str_pad($i, $lengthNumbers, '0', STR_PAD_LEFT);
                $normalized = $this->normalizeNumber($number); // Use method call with $this
                $uniqueNumbers[$normalized] = true;
            }

            // Get the array keys to show results
            $uniqueNumbers = array_keys($uniqueNumbers);
            sort($uniqueNumbers); // Sort numbers

            // dd('bug');
            foreach ($uniqueNumbers as $key => $uniqueNumber) {
                # code...
                $length = strlen($uniqueNumber);

                $lotto = LottoMain::create([
                    'numbers' => $uniqueNumber,
                    'length' => $length,
                    'max' => $request->max,
                    'remain' => $request->max,
                    'date' => $request->date,
                    'type' => $request->type,
                ]);
            }
        } else {
            $length = strlen($request->numbers);
            $lotto = LottoMain::create([
                'numbers' => $request->numbers,
                'length' => $length,
                'max' => $request->max,
                'remain' => $request->max,
                'date' => $request->date,
                'type' => $request->type,
            ]);
        }


        return response()->json(['success' => 'success !']);
    }
    public function addresult(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'add1st' => 'required|digits:6',
            'add3front_1' => 'required|digits:3',
            'add3front_2' => 'required|digits:3',
            'add3back_1' => 'required|digits:3',
            'add3back_2' => 'required|digits:3',
            'add2numbers' => 'required|digits:2',
        ]);


        $lotto = LottoResult::create([
            'date' => $validatedData['date'],
            '1st' => $validatedData['add1st'],
            '3front_1' => $validatedData['add3front_1'],
            '3front_2' => $validatedData['add3front_2'],
            '3back_1' => $validatedData['add3back_1'],
            '3back_2' => $validatedData['add3back_2'],
            '2numbers' => $validatedData['add2numbers'],
        ]);

        // Extract the last 3 digits of the 1st prize
        $lastThreeDigits = substr($lotto->{'1st'}, -3);

        $treeNumbersType1 = [
            '0' => $validatedData['add3front_1'],
            '1' => $validatedData['add3front_2'],
            '2' => $validatedData['add3back_1'],
            '3' => $validatedData['add3back_2'],
        ];

        $twoNumbersType1 = $validatedData['add2numbers'];

        // Retrieve all lotto reports where the status is 0
        $lottoReport = lottoReport::where('status', 0)->get();

        foreach ($lottoReport as $record) {
            $length = strlen($record->numbers);

            if ($record->type === '0' && $length === 3) {
                if ($record->numbers === $lastThreeDigits) {
                    $record->update([
                        'status' => 1,
                        'win_lose' => 'win',
                        'result' => $record->bet * 65,
                    ]);
                } else {
                    $record->update([
                        'status' => 1,
                        'win_lose' => 'lose',
                        'result' => ($record->bet) - ($record->bet * 2),
                    ]);
                }
            } elseif ($record->type === '1') {
                if ($length === 3) {
                    foreach ($treeNumbersType1 as $checktreeNumbersType1) {
                        if ($record->numbers === $checktreeNumbersType1) {
                            $record->update([
                                'status' => 1,
                                'win_lose' => 'win',
                                'result' => $record->bet * 65,
                            ]);
                            break; // Exit the loop since we've found a match
                        }
                    }
                } elseif ($length === 2) {
                    if ($record->numbers === $twoNumbersType1) {
                        $record->update([
                            'status' => 1,
                            'win_lose' => 'win',
                            'result' => $record->bet * 65,
                        ]);
                    } else {
                        $record->update([
                            'status' => 1,
                            'win_lose' => 'lose',
                            'result' => ($record->bet) - ($record->bet * 2),
                        ]);
                    }
                } else {
                    // Handle case where length is not 2 or 3
                    $record->update([
                        'status' => 1,
                        'win_lose' => 'lose',
                        'result' => ($record->bet) - ($record->bet * 2),
                    ]);
                }
            }
        }

        return response()->json(['success' => 'success!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function income(Request $request)
    {

        if ($request->ajax()) {
            $types = LottoRecord::orderBy("id", "desc")->get();
            return datatables()->of($types)
                ->editColumn('type', function (LottoRecord $type) {
                    $text = '';
                    if ($type->type === '0') {
                        # code...
                        $text = 'โต๊ด';
                    } elseif ($type->type === '1') {
                        # code...
                        $text = 'เต็ง';
                    } else {
                        # code...
                        $text = 'Normal';
                    }

                    return $text;
                })
                ->editColumn('create_at', function (LottoRecord $create_at) {
                    $date = date('d-m-Y', $create_at->create_at);


                    return $date;
                })

                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    $html = '';
                    return $html;
                })
                ->toJson();
        }

        return view('lottoes.income');
    }
    public function income_create()
    {
        //
        return view('lottoes.income-create');
    }
    public function income_store(Request $request)
    {
        //'

        // dd($request->all());
        $note = '';
        $name = $request->name;
        $income_numbers = $request->numbers;
        $income_value = $request->value;
        // dd(
        //     $request->all(),
        //     $income_numbers
        // );

        foreach ($income_numbers as $key => $income_number) {
            # code...

            $number = $income_number['numbers'];
            $income_type = $income_number['type'];

            if ($number) {
                # code...
                $income_length = strlen($number);

                $sorted_income_numbers = implode('', collect(str_split($number))->sort()->toArray());

                // Query the LottoMain table
                $check_main = LottoMain::where('type', $income_type)
                    ->where('length', $income_length)
                    ->get()
                    ->filter(function ($item) use ($sorted_income_numbers) {
                        // Sort the digits of each number in the database and compare
                        $sorted_numbers_in_db = implode('', collect(str_split($item->numbers))->sort()->toArray());
                        return $sorted_income_numbers === $sorted_numbers_in_db;
                    });

                if ($check_main->isEmpty()) {

                    $note .= 'ไม่พบหมายเลขนี้ : ' . $number . ' | ';

                    return response()->json([
                        'success' => false,
                        'status' => 'Error',
                        'remain' => '0',
                        'note' => $note,

                    ]);
                }

                $max = $check_main->max('remain');
                $income_remain = (int) $max - (int) $income_value;
                $remain_lotto = 0;
                $income_remain_out = 0;
                $status = '';
                $statusText = '';

                foreach ($check_main as $item) {
                    if ($item->remain > 0) {
                        # code...
                        if ($income_remain >= 0) {
                            $item->update(['remain' => $income_remain]);
                            $status = 'success';
                            $statusText = 'สำเร็จ';
                            $note .= $number . ': สำเร็จ | ';
                        } else {
                            $item->update(['remain' => $remain_lotto]);
                            $income_remain_out = abs((int) $income_remain);
                            $note .= $number . ' ยอดเกิน : ' . $income_remain_out . ' บาท | ';
                            $statusText = 'ยอดเกิน';
                            $status = 'over';
                        }
                    } else {

                        $status = 'not';
                        $statusText = 'ไม่รับ';

                        $income_remain_out = abs((int) $income_remain);
                        $note .=  $number . ' : ยอดเต็มจำนวน | ';
                    }
                }

                $bet = $income_value - $income_remain_out;
                LottoRecord::create([
                    'name' => $name,
                    '3numbers' => $number,
                    'type' => $income_type,
                    'status' => $status,
                    'bet' => $bet,
                    'value' => $income_value,
                    'remain' => $income_remain_out,
                ]);

                if ($status === 'success' || $status === 'over') {
                    # code...
                    lottoReport::create([
                        'name' => $name,
                        'numbers' => $number,
                        'type' => $income_type,
                        'bet' => $bet,
                        'status' => 0,
                        'result' => 0,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => $statusText,
            'note' => $note,
            'remain' => $income_remain_out,
        ]);
    }


    public function income_storeAll(Request $request)
    {
        // dd($request->all());
        $rows = $request['rows'];
        $results = [];

        // dd($rows);
        foreach ($rows as $key => $row) {
            $name = $row['name'];
            $income_value = $row['value'];
            $note = '';
            $status = '';
            $statusText = '';

            $remain_lotto = 0;
            $income_remain_out = 0;

            $income_numbers = $row['numbers'];

            foreach ($income_numbers as $income_number) {
                # code...
                $number = $income_number['numbers'];
                $income_type = $income_number['type'];
                if ($number) {
                    # code...

                    $income_length = strlen($number);
                    $sorted_income_numbers = implode('', collect(str_split($number))->sort()->toArray());

                    $check_main = LottoMain::where('type', $income_type)
                        ->where('length', $income_length)
                        ->get()
                        ->filter(function ($item) use ($sorted_income_numbers) {
                            $sorted_numbers_in_db = implode('', collect(str_split($item->numbers))->sort()->toArray());
                            return $sorted_income_numbers === $sorted_numbers_in_db;
                        });

                    $max = $check_main->max('remain');
                    $income_remain = (int) $max - (int) $income_value;

                    foreach ($check_main as $item) {
                        if ($item->remain > 0) {
                            if ($income_remain >= 0) {
                                $item->update(['remain' => $income_remain]);
                                $status = 'success';
                                $statusText = 'สำเร็จ';
                                $note .= $number . ': สำเร็จ | ';
                            } else {
                                $item->update(['remain' => $remain_lotto]);
                                $income_remain_out = abs($income_remain);
                                $note = $number . ' ยอดเกิน : ' . $income_remain_out . ' บาท | ';
                                $statusText = 'ยอดเกิน';
                                $status = 'over';
                            }
                        } else {
                            $status = 'not';
                            $statusText = 'ไม่รับ';
                            $income_remain_out = abs($income_remain);
                            $note .=  $number . ' : ยอดเต็มจำนวน | ';
                        }
                    }
                    $bet = $income_value - $income_remain_out;
                    if ($name && $number && $income_value) {
                        # code...
                        LottoRecord::create([
                            'name' => $name,
                            '3numbers' => $number,
                            'type' => $income_type,
                            'status' => $status,
                            'value' => $income_value,
                            'bet' => $bet,
                            'remain' => $income_remain_out,
                        ]);
                    }

                    if ($check_main->isEmpty()) {
                        $note .= 'ไม่พบหมายเลขนี้ : ' . $number . ' | ';
                        $results[$key] = [
                            'success' => false,
                            'status' => 'Error',
                            'remain' => 0,
                            'note' => $note,
                            'row' => $key
                        ];
                        continue; // Skip to the next iteration if no matching record is found
                    }

                    $results[$key] = [
                        'success' => true,
                        'status' => $statusText,
                        'remain' => $income_remain_out,
                        'note' => $note,
                        'row' => $key
                    ];
                    if ($status === 'success' || $status === 'over') {
                        # code...
                        lottoReport::create([
                            'name' => $name,
                            'numbers' => $number,
                            'type' => $income_type,
                            'bet' => $bet,
                            'status' => 0,
                            'result' => 0,
                        ]);
                    }
                }
            }
        }

        return response()->json(['results' => $results]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    public function report(Request $request)
    {
        //

        if ($request->ajax()) {
            $types = lottoReport::orderBy("id", "desc")->get();
            return datatables()->of($types)
                ->editColumn('type', function (lottoReport $type) {
                    $text = '';
                    if ($type->type === '0') {
                        # code...
                        $text = 'โต๊ด';
                    } elseif ($type->type === '1') {
                        # code...
                        $text = 'เต็ง';
                    } else {
                        # code...
                        $text = 'Normal';
                    }

                    return $text;
                })
                ->editColumn('create_at', function (lottoReport $create_at) {
                    $date = date('d-m-Y', $create_at->create_at);


                    return $date;
                })
                ->editColumn('status', function (lottoReport $status) {
                    $text = '';
                    if ($status->status === '0') {
                        # code...
                        $text = 'รอผล';
                    } elseif ($status->status === '1') {
                        # code...
                        $text = 'สำเร็จ';
                    } else {
                        # code...
                        $text = 'error';
                    }

                    return $text;
                })

                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    $html = '';
                    return $html;
                })
                ->toJson();
        }

        return view('lottoes.report');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
