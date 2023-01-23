<?php

namespace App\Imports;

use App\Models\Receipt;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ReceiptImport implements ToModel
{

    var $sfield_check = 0;

    public function  __construct($sfield)
    {
        $this->sfield_check = $sfield;
       
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        //echo $this->sfield_check;
        ///exit();
        $rsdate = strtotime($row[0]);
        return new Receipt([
            //
            'sfield' => $this->sfield_check,
            'submission_date' => date('Y-m-d', $rsdate),
            'first_name' => $row[1],
            'last_name' => $row[2],
            'street_address' => $row[3],
            'street_address2' => $row[4],
            'city' => $row[5],
            'province' => $row[6],
            'postcode' => $row[7],
            'name_parent' => $row[8],
            'mobile' => $row[9],
            'package' => $row[10],
            'file_att' => $row[11],
        ]);

        /*
        $bin = Receipt::orderBy("id", "asc")->get();

        // Get all bin number from the $bin collection
        $bin_number = $bin->pluck('bin_number');

        // Checking if the bin number is already in the database
        if ($bin_number->contains($row[0]) == false) {
            return new Receipt([
                'bin_number' => $row[0],
                'type' => $row[1],
                'product' => $row[2],
                'category' => $row[3],
                'bank' => $row[4],
            ]);
        } else null; // if the bin number is already in the database, return null
        */
    }
}
