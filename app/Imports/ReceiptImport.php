<?php

namespace App\Imports;

use App\Models\Receipt;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ReceiptImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Receipt([
            //
            'submission_date' => $row[0],
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
    }
}
