<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt_Customer_RunningNumber extends Model
{
    use HasFactory;

    protected $table = 'receipt_customer_running_numbers';

    protected $fillable = [
        'type',
        'number',
        'year',
    ];

    const TYPES = [
        '0',
        'TT',
        '2',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '10',
        '11',
        '13',
        '14',
        '15',
        '16',
    ];

    const TYPE_PREFIX = [
        '0' => '0',
        'TT' => 'TT',
        '2' => '2',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
        '9' => '9',
        '10' => '10',
        '11' => '11',
        '13' => '13',
        '14' => '14',
        '15' => '15',
        '16' => '16',

    ];


    public static function generate(string $type)
    {
        $type = strtoupper($type);
        if (!in_array($type, self::TYPES)) {
            throw new \Exception('Undefined running number type.');
        }
        $number = 0;
        $year = date('Y');


        if (! Receipt_Customer_RunningNumber::where('type', $type)->where('year', $year)->exists()) {
            Receipt_Customer_RunningNumber::create([
                'type' => $type,
                'number' => $number,
                'year' => $year,
            ]);
        }

        $running_number = Receipt_Customer_RunningNumber::where('type', $type)->where('year', $year)->first();
        //print_r($running_number);
        //exit();
        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);

        // A-21-00001
        if (strlen(Receipt_Customer_RunningNumber::TYPE_PREFIX[$type]) > 1) {
            return Receipt_Customer_RunningNumber::TYPE_PREFIX[$type] . '' . $number;
        } else {
            return "0".Receipt_Customer_RunningNumber::TYPE_PREFIX[$type] . '' . $number;
        }

    }
}
