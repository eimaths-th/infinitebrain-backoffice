<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunningNumber extends Model
{
  
    
    // public static function generate($model, $trow, $length = 4 , $prefix)
    // {
    //     $data = $model::orderBy('code','desc')->first();
    //    if(!$data){
    //         $og_length = $length;
    //         $last_number = '';
    //    }else{
    //     $code = (int)substr($data->$trow, strlen($prefix)+1);
    //     $actial_last_number = ($code/1)*1;
    //     $increment_last_number = $actial_last_number+1;
    //     $last_number_length = strlen($increment_last_number);
    //     $og_length = $length - $last_number_length;
    //     $last_number = $increment_last_number;
    //    }
    //    $zeros = "" ;
    //    for($i=0;$i<$og_length;$i++){
    //     $zeros.="0";
    //    }
    //    return $prefix.$zeros.$last_number;
    // }
    use HasFactory;

    protected $table = 'students_running_numbers';

    protected $fillable = [
        
        'number',
        
    ];




    public static function generate()
    {
      
        $number = 1;

            // RunningNumber::create([
            //     'number' => $number,
            // ]);
        
        $running_number = RunningNumber::first();
        // print_r($running_number);
        // exit();
        if($running_number!= null){
            $running_number->number++;
            $running_number->save();
            $number = $running_number->number;
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
            return  $number;

        }else{
            RunningNumber::create([
                'number' => $number,
                        ]);
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);

        }
        //print_r($running_number);
        //exit();
        
        return  $number;

        // A-21-00001
    }

}

?>