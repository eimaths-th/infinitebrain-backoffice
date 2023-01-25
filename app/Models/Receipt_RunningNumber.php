<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt_RunningNumber extends Model
{
    use HasFactory;

    protected $table = 'receipt_running_numbers';

    protected $fillable = [
        'type',
        'number',
        'year',
        'month',
    ];

    const TYPES = [
        '2',
        '4',
        '5',
        '6',
        '7',
        '8',
    ];

    const TYPE_PREFIX = [
        '2' => '2',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
        
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

        if (! Receipt_RunningNumber::where('type', $type)->where('year', $year)->where('month', $month)->exists()) {
            Receipt_RunningNumber::create([
                'type' => $type,
                'number' => $number,
                'year' => $year,
                'month' => $month,
            ]);
        }

        $running_number = Receipt_RunningNumber::where('type', $type)->where('year', $year)->where('month', $month)->first();
        //print_r($running_number);
        //exit();
        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);

        // A-21-00001
        return Receipt_RunningNumber::TYPE_PREFIX[$type] . '' . date('y') . '' . date('m') . '' . $number;
    }
}
