<?php

namespace App\Imports;

use App\Models\Receipt;
use App\Models\Receipt_RunningNumber;
use App\Models\Receipt_Customer_RunningNumber;
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
        $rnumber = Receipt_RunningNumber::generate($this->sfield_check);
        $rcnumber = Receipt_Customer_RunningNumber::generate($this->sfield_check);

        $description = explode("(",$row[4]);
        //exit();
        return new Receipt([
            //
            /*'sfield' => $this->sfield_check,
            'receipt_number' => $rnumber,
            'receipt_customer_number' => $rcnumber,
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
            'file_att' => $row[11],*/
            'sfield' => $this->sfield_check,
            'receipt_number' => $rnumber,
            'receipt_customer_number' => $rcnumber,
            'submission_date' => date('Y-m-d', $rsdate),
            'first_name' => $row[1],
            'last_name' => $row[2],
            'street_address' => $row[6],
            'name_parent' => $row[5],
            'postcode' => $row[7],
            'mobile' => $row[8],
            'package' => $row[4],
            'grade' => $row[3],
            'file_att' => $row[9],
            'description' => $description[0],
            'description2' => $row[11],
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
