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

    const TYPES = [
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
       
    ];

    const TYPE_PREFIX = [
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        
    ];


    public static function generate(string $type)
    {
        $type = strtoupper($type);
        if (!in_array($type, self::TYPES)) {
            throw new \Exception('Undefined running number type.');
        }
        $number = 0;
        $year = date('Y');
        $month = date('m');
        //$month = "01";

        if (! Receipt_School_RunningNumber::where('type', $type)->where('year', $year)->where('month', $month)->exists()) {
            Receipt_School_RunningNumber::create([
                'type' => $type,
                'number' => $number,
                'year' => $year,
                'month' => $month,
            ]);
        }

        $running_number = Receipt_School_RunningNumber::where('type', $type)->where('year', $year)->where('month', $month)->first();
        //print_r($running_number);
        //exit();
        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);

        // A-21-00001
        return Receipt_School_RunningNumber::TYPE_PREFIX[$type] . '' . date('y') . '' . date('m') . '' . $number;
        //return Receipt_RunningNumber::TYPE_PREFIX[$type] . '' . date('y') . '01' . $number;
    }

}
