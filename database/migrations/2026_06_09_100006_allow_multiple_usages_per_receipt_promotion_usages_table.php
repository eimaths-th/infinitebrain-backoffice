<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $table = 'receipt_promotion_usages';
    private $singleUnique = 'receipt_promotion_usages_receipt_school_id_unique';
    private $compositeUnique = 'rpu_receipt_campaign_unique';

    /**
     * Allow more than one calculated promotion campaign per receipt.
     *
     * The original table had a single-column unique on receipt_school_id, which
     * limited each receipt to one usage. Replace it with a composite unique on
     * (receipt_school_id, promotion_campaign_id) so multiple campaigns can be
     * combined while still preventing the same campaign twice on one receipt.
     */
    public function up()
    {
        // Add composite unique first so the foreign key on receipt_school_id
        // keeps a usable leftmost index before the single unique is dropped.
        if (!$this->indexExists($this->compositeUnique)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unique(['receipt_school_id', 'promotion_campaign_id'], $this->compositeUnique);
            });
        }

        if ($this->indexExists($this->singleUnique)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropUnique($this->singleUnique);
            });
        }
    }

    public function down()
    {
        if (!$this->indexExists($this->singleUnique)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unique('receipt_school_id');
            });
        }

        if ($this->indexExists($this->compositeUnique)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropUnique($this->compositeUnique);
            });
        }
    }

    private function indexExists($index_name)
    {
        $result = DB::select(
            'SELECT COUNT(1) AS total FROM information_schema.statistics '
            . 'WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$this->table, $index_name]
        );

        return !empty($result) && (int) $result[0]->total > 0;
    }
};
