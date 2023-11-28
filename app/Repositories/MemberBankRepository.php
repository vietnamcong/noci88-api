<?php

namespace App\Repositories;

use App\Models\MemberBank;
use App\Repositories\Concerns\CustomQuery;
use Illuminate\Support\Facades\DB;

class MemberBankRepository extends CustomRepository
{
    use CustomQuery;

    protected $model = MemberBank::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function getListBankForUser()
    {
        $table = $this->getTable();
        return DB::table($table)->select([$table . '.*', 'banks.name', 'banks.logo'])
            ->join('banks', 'banks.key', '=', $table . '.bank_type')
            ->where($table . '.member_id', getGuard()->user()->id)
            ->orderBy($table . '.id', 'desc')
            ->get();
    }

    public function getMemberBank($id)
    {
        $table = $this->getTable();
        return DB::table($table)->select(["$table.*", 'banks.name', 'banks.logo'])
            ->join('banks', 'banks.key', '=', "$table.bank_type")
            ->where("$table.id", $id)
            ->first();
    }
}
