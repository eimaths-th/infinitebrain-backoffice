<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt_School_RunningNumber extends Model
{
    use HasFactory;
    protected $table = 'receipt_school_running_numbers';

    protected $fillable = [
        'type',
        'number',
        'year',
        'month',
    ];

    public static function generate($group)
    {
        // $type = strtoupper($type);
        // if (!in_array($type, self::TYPES)) {
        //     throw new \Exception('Undefined running number type.');
        // }
        $number = 0;
        $year = date('Y');
        $month = date('m');
        //$month = "01";
        if ($group == 'Sci') {
            $type = 'Sci';
        }elseif ($group == 'CHN') {
            $type = 'CHN';
        }else {
            $type = 'def';
        }
        // dd($type);

        if (! Receipt_School_RunningNumber::where('year', $year)->where('month', $month)->where('type', $type)->exists()) {
            Receipt_School_RunningNumber::create([
                'number' => $number,
                'year' => $year,
                'month' => $month,
                'type' => $type,
            ]);
        }

        $running_number = Receipt_School_RunningNumber::where('year', $year)->where('month', $month)->where('type', $type)->first();
        //print_r($running_number);
        //exit();
        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);
        // echo(date('y') . '' . date('m') . '' . $number);
        // exit();
        if ($type === 'Sci') {
            $typenumber = "03";
        }elseif ($type === 'CHN') {
            $typenumber = "01";

        }else {
            $typenumber = "00";

        }
        $result = $typenumber.date('y') . '' . date('m') . '' . $number;
        // A-21-00001
        return  $result;
        //return Receipt_RunningNumber::TYPE_PREFIX[$type] . '' . date('y') . '01' . $number;
    }

}
