<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt_School_RunningNumber extends Model
{
    use HasFactory;
    protected $table = 'receipt_school_running_numbers';

    protected $fillable = [
        'number',
        'year',
        'month',
    ];

    


    public static function generate()
    {
        // $type = strtoupper($type);
        // if (!in_array($type, self::TYPES)) {
        //     throw new \Exception('Undefined running number type.');
        // }
        $number = 0;
        $year = date('Y');
        $month = date('m');
        //$month = "01";

        if (! Receipt_School_RunningNumber::where('year', $year)->where('month', $month)->exists()) {
            Receipt_School_RunningNumber::create([
                'number' => $number,
                'year' => $year,
                'month' => $month,
            ]);
        }

        $running_number = Receipt_School_RunningNumber::where('year', $year)->where('month', $month)->first();
        //print_r($running_number);
        //exit();
        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);
        // echo(date('y') . '' . date('m') . '' . $number);
        // exit();
        $result = date('y') . '' . date('m') . '' . $number;
        // A-21-00001
        return  $result;
        //return Receipt_RunningNumber::TYPE_PREFIX[$type] . '' . date('y') . '01' . $number;
    }

}
