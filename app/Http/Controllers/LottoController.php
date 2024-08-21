<?php

namespace App\Http\Controllers;

use App\Models\LottoMain;
use App\Models\LottoRecord;
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
                    return $type->type == 0 ? "โต๊ด" : "Normal";
                })
                ->editColumn('max', function (LottoMain $max) {
                    return number_format($max->max) . ' บาท';
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
    public function store(Request $request)
    {
        //
        // dd($request->all());
        $length = strlen($request->numbers);
        $lotto = LottoMain::create([
            'numbers' => $request->numbers,
            'length' => $length,
            'max' => $request->max,
            'remain' => $request->max,
            'date' => $request->date,
            'type' => $request->type,
        ]);
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

        try {
            // Insert the validated data into the database
            $lotto = LottoResult::create([
                'date' => $validatedData['date'],
                '1st' => $validatedData['add1st'],
                '3front_1' => $validatedData['add3front_1'],
                '3front_2' => $validatedData['add3front_2'],
                '3back_1' => $validatedData['add3back_1'],
                '3back_2' => $validatedData['add3back_2'],
                '2numbers' => $validatedData['add2numbers'],
            ]);

            return response()->json(['success' => 'success!']);
        } catch (\Exception $e) {
            // Handle any errors that might occur
            return response()->json(['error' => 'Failed to save result.'], 500);
        }
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

        $name = $request->name;
        $income_numbers = $request->numbers;
        $income_type = $request->type;
        $income_value = $request->value;
        $income_length = strlen($income_numbers);

        $sorted_income_numbers = implode('', collect(str_split($income_numbers))->sort()->toArray());

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

            return response()->json([
                'success' => false,
                'status' => 'Error',
                'remain' => '0',
                'note' => 'ไม่พบหมายเลขนี้',

            ]);
        }

        $max = $check_main->max('remain');
        $income_remain = (int) $max - (int) $income_value;
        $remain_lotto = 0;
        $note = '';
        $income_remain_out = 0;
        $status = '';

        foreach ($check_main as $item) {
            if ($item->remain > 0) {
                # code...
                if ($income_remain > 0) {
                    $item->update(['remain' => $income_remain]);
                    $status = 'สำเร็จ';
                } else {
                    $item->update(['remain' => $remain_lotto]);
                    $income_remain_out = abs((int) $income_remain);
                    $note = 'ยอดเกิน : ' . $income_remain_out . ' บาท';
                    $status = 'ยอดเกิน';
                }
            } else {

                $status = 'ไม่รับ';
                $income_remain_out = abs((int) $income_remain);
                $note = 'ยอดเต็มจำนวน';
            }
        }

        LottoRecord::create([
            'name' => $name,
            '3numbers' => $income_numbers,
            'type' => $income_type,
            'status' => $status,
            'value' => $income_value,
            'remain' => $income_remain_out,
        ]);

        return response()->json([
            'success' => true,
            'status' => $status,
            'note' => $note,
            'remain' => $income_remain_out
        ]);
    }
    public function income_storeAll(Request $request)
    {
        //'

        // dd($request->all());
        $rows = $request['rows'];
        $results = [];
        foreach ($rows as $key => $row) {
            // dd($row);

            $name = $row['name'];
            $income_numbers = $row['numbers'];
            $income_type = $row['type'];
            $income_value = $row['value'];
            $income_length = strlen($income_numbers);
            $note = '';
            $status = '';
            $remain_lotto = 0;
            $income_remain_out = 0;
            $success = null;

            $sorted_income_numbers = implode('', collect(str_split($income_numbers))->sort()->toArray());

            $check_main = LottoMain::where('type', $income_type)
                ->where('length', $income_length)
                ->get()
                ->filter(function ($item) use ($sorted_income_numbers) {

                    $sorted_numbers_in_db = implode('', collect(str_split($item->numbers))->sort()->toArray());
                    return $sorted_income_numbers === $sorted_numbers_in_db;
                });
            if ($check_main->isEmpty()) {
                $success = false;
                $status = 'Error';
                $note = 'ไม่พบหมายเลขนี้';
            }

            $max = $check_main->max('remain');
            $income_remain = (int) $max - (int) $income_value;

            foreach ($check_main as $item) {
                if ($item->remain > 0) {
                    # code...
                    if ($income_remain > 0) {
                        $item->update(['remain' => $income_remain]);
                        $status = 'สำเร็จ';
                    } else {
                        $item->update(['remain' => $remain_lotto]);
                        $income_remain_out = abs((int) $income_remain);
                        $note = 'ยอดเกิน : ' . $income_remain_out . ' บาท';
                        $status = 'ยอดเกิน';
                    }
                } else {

                    $status = 'ไม่รับ';
                    $income_remain_out = abs((int) $income_remain);
                    $note = 'ยอดเต็มจำนวน';
                }
            }

            LottoRecord::create([
                'name' => $name,
                '3numbers' => $income_numbers,
                'type' => $income_type,
                'status' => $status,
                'value' => $income_value,
                'remain' => $income_remain_out,
            ]);
            $success = true;
            $results[$key][] = [
                'success' => $success,
                'status' => $status,
                'remain' => $income_remain_out,
                'note' => $note,
                'row' => $key
            ];
        }
        return response()->json(['results' => $results]);
        return response()->json(['success' => true]);
        return response()->json([
            'success' => true,
            'status' => $status,
            'note' => $note,
            'remain' => $income_remain_out
        ]);
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
