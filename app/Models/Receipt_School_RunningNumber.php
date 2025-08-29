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

    // public static function generate($group, $tex)
    // {
    //     // $type = strtoupper($type);
    //     // if (!in_array($type, self::TYPES)) {
    //     //     throw new \Exception('Undefined running number type.');
    //     // }
    //     $number = 0;
    //     $year = date('Y');
    //     $month = date('m');
    //     //$month = "01";
    //     if ($group == 'Sci') {
    //         $type = 'Sci';
    //     } elseif ($group == 'CHN') {
    //         $type = 'CHN';
    //     } else {
    //         $type = 'def';
    //     }
    //     // dd($type);

    //     if (! Receipt_School_RunningNumber::where('year', $year)->where('month', $month)->where('type', $type)->exists()) {
    //         Receipt_School_RunningNumber::create([
    //             'number' => $number,
    //             'year' => $year,
    //             'month' => $month,
    //             'type' => $type,
    //         ]);
    //     }

    //     $running_number = Receipt_School_RunningNumber::where('year', $year)->where('month', $month)->where('type', $type)->first();
    //     //print_r($running_number);
    //     //exit();
    //     $running_number->number++;
    //     $running_number->save();
    //     $number = $running_number->number;
    //     $number = str_pad($number, 3, '0', STR_PAD_LEFT);
    //     // echo(date('y') . '' . date('m') . '' . $number);
    //     // exit();
    //     if ($type === 'Sci') {
    //         $typenumber = "03";
    //         if ($tex == 1) {
    //             # code...
    //             $typenumber = "73";
    //         } else {
    //             # code...
    //             $typenumber = "03";
    //         }
    //     } elseif ($type === 'CHN') {

    //         if ($tex == 1) {
    //             # code...
    //             $typenumber = "71";
    //         } else {
    //             # code...
    //             $typenumber = "01";
    //         }
    //     }  else {
    //         if ($tex == 1) {
    //             # code...
    //             $typenumber = "77";
    //         } else {
    //             # code...
    //             $typenumber = "00";
    //         }
    //     }
    //     $result = $typenumber . date('y') . '' . date('m') . '' . $number;
    //     // A-21-00001
    //     return  $result;
    //     //return Receipt_RunningNumber::TYPE_PREFIX[$type] . '' . date('y') . '01' . $number;
    // }

    // public static function generate($group, $tex)
    // {
    //     $number = 0;
    //     $year = date('Y');
    //     $month = date('m');

    //     if ($group == 'Sci' && $tex == 0) {
    //         $type = 'Sci';
    //     } elseif ($group == 'Sci' && $tex == 1) {
    //         $type = 'NTSci';
    //     } elseif ($group == 'CHN' && $tex == 0) {
    //         $type = 'CHN';
    //     } elseif ($group == 'CHN' && $tex == 1) {
    //         $type = 'NTCHN';
    //     } else {
    //         if ($tex == 0) {
    //             $type = 'def';
    //             # code...
    //         } else {
    //             # code...
    //             $type = 'NT';
    //         }
    //     }
    //     // dd($type);

    //     // Initialize or get the running number record
    //     if (!Receipt_School_RunningNumber::where('year', $year)->where('month', $month)->where('type', $type)->exists()) {
    //         Receipt_School_RunningNumber::create([
    //             'number' => $number,
    //             'year' => $year,
    //             'month' => $month,
    //             'type' => $type,
    //         ]);
    //     }

    //     $running_number = Receipt_School_RunningNumber::where('year', $year)->where('month', $month)->where('type', $type)->first();
    //     $running_number->number++;
    //     $running_number->save();

    //     $number = str_pad($running_number->number, 3, '0', STR_PAD_LEFT);

    //     // Define the lookup table for typenumber
    //     $typenumbers = [
    //         'Sci' => ['1' => '73', '0' => '03'],
    //         'CHN' => ['1' => '71', '0' => '01'],
    //         'def' => ['1' => '00', '0' => '00'],
    //         'NT'  => ['1' => '77', '0' => '77'],
    //     ];

    //     // Determine typenumber based on type and tex
    //     $texKey = $tex == 1 ? '1' : '0';
    //     $typenumber = $typenumbers[$type][$texKey];

    //     // Generate the result
    //     $result = $typenumber . date('y') . $month . $number;

    //     return $result;
    // }

    public static function generate($group, $tex)
    {
        $number = 0;
        $year = date('Y');
        $month = date('m');
        // dd($tex);
        // Define the lookup table for types
        $types = [
            'Sci' => ['0' => 'Sci', '1' => 'NTSci'],
            'CHN' => ['0' => 'CHN', '1' => 'NTCHN'],
            'TH' => ['0' => 'TH', '1' => 'NTTH'],
            'default' => ['0' => 'def', '1' => 'NT'],
        ];

        // Determine type based on group and tex
        $type = $types[$group][$tex] ?? $types['default'][$tex];

        // Initialize or get the running number record
        if (!Receipt_School_RunningNumber::where('year', $year)
            ->where('month', $month)
            ->where('type', $type)
            ->exists()) {

            Receipt_School_RunningNumber::create([
                'number' => $number,
                'year' => $year,
                'month' => $month,
                'type' => $type,
            ]);
        }

        $running_number = Receipt_School_RunningNumber::where('year', $year)
            ->where('month', $month)
            ->where('type', $type)
            ->first();

        $running_number->number++;
        $running_number->save();

        $number = str_pad($running_number->number, 3, '0', STR_PAD_LEFT);

        // Define the lookup table for typenumber
        $typenumbers = [
            'Sci' => ['1' => '73', '0' => '03'],
            'NTSci' => ['1' => '73', '0' => '03'],
            'CHN' => ['1' => '71', '0' => '01'],
            'NTCHN' => ['1' => '71', '0' => '01'],
            'def' => ['1' => '00', '0' => '00'],
            'NT'  => ['1' => '77', '0' => '77'],
            'TH' => ['1' => '72', '0' => '02'],
            'NTTH' => ['1' => '72', '0' => '02'],

        ];

        // Determine typenumber based on type and tex
        $texKey = $tex == 1 ? '1' : '0';
        $typenumber = $typenumbers[$type][$texKey] ?? '00'; // Default to '00' if $type is not found

        // Generate the result
        $result = $typenumber . date('y') . $month . $number;

        return $result;
    }
}
