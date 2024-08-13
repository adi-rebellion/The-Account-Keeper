<?php

namespace App\Repositories;

use App\Interfaces\CrudInterface;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\ResponseTrait;
use Exception;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;


class TransactionRepository implements CrudInterface
{
    use ResponseTrait;



    public function create(array $inputData,User $userDetails): ?Transaction
    {

        return Transaction::create([
            'trans_user_id' => $userDetails->id,
            'trans_date' => Carbon::now(),
            'trans_amount' => $inputData['trans_amount'],
            'trans_type' => $inputData['trans_type'],
            'category_id' => $inputData['category_id'],
            'description' => $inputData['description'] ?? ''

        ]);
    }



}
