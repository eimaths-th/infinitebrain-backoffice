<?php

namespace App\Exports;

use App\Models\ReceiptForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ExportForm implements FromCollection, WithHeadings
{

    var $sfield_check = 0;
    var $export_id = array();

    public function  __construct($sfield, $export_id)
    {
        $this->sfield_check = $sfield;
        $wsql = '';
        foreach ($export_id as $arr) {
            $wsql .= "'" . $arr . "',";
        }
        $this->export_id = $export_id;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ReceiptForm::select(
            "submission_date",
            "first_name",
            "last_name",
            "nick_name",
            "email",
            "school_name",
            "grade",
            "birth_date",
            "street_address",
            "postcode",
            "name_parent",
            "mobile",
            "package",
            "contest",
            DB::raw("CONCAT('" . url('/') . "/receipt_form_images/',photo_att) AS photo"),
            DB::raw("CONCAT('" . url('/') . "/receipt_form_images/',file_att) AS slip"),
            "news_form"
        )->whereIn('id', $this->export_id)->where([['sfield', $this->sfield_check]])->get();
    }

    public function headings(): array
    {
        return [
            'Submission Date',
            'First Name',
            'Last Name',
            'Nick Name',
            'Email',
            'School Name',
            'Grade',
            'Birth Date',
            'Street Address',
            'Postcode',
            'Name Parent',
            'Mobile',
            'Package',
            'Contest',
            'Photo',
            'Slip',
            'News From',
        ];
    }
}
