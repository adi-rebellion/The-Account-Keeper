<?php
namespace App\Http\Controllers;

use App\Http\Requests\TransactionCreateRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use ResponseTrait;

    protected $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function makeTransaction(TransactionCreateRequest $request): JsonResponse
    {
        try {
            $loggedInUser = Auth::guard()->user();

            $balanceBeforeTransaction = $this->calculateBalance($loggedInUser, Carbon::now());

            if ($request->trans_type === 'debit' && $request->trans_amount > $balanceBeforeTransaction) {
                return $this->responseError([
                    "trans_user_id" => $loggedInUser->id,
                    "attempted_trans_date" => Carbon::now(),
                    "attempted_trans_amount" => $request->trans_amount,
                    "available_balance" => $balanceBeforeTransaction,
                ], 'The debit transaction could not be completed due to insufficient balance.');
            }


            $requestedTransactionData = $this->transactionRepository->create($request->all(),$loggedInUser);
            $balanceAfterTransaction = $this->updateBalance($balanceBeforeTransaction, $request->trans_amount, $request->trans_type);

            return $this->responseSuccess([
                'transaction' => $requestedTransactionData,
                'balance_after_transaction' => $balanceAfterTransaction,
            ], 'The transaction has been completed successfully.');

        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function dailyClosingBalance(Request $request)
    {

        try{
            $today = Carbon::today();
            $loggedInUser = Auth::guard()->user();
            $balances = [];
            for ($i = 0; $i <  ( $request->requested_days ?? 90); $i++) {
                $date = $today->copy()->subDays($i);
                $balances[$date->toDateString()] = $this->calculateBalance($loggedInUser, $date);
            }
            return $this->responseSuccess([
                'requested_for_days' => ($request->requested_days ?? 90 ),
                'colsing_balance' => $balances,
            ], 'The transaction has been completed successfully | '. ($request->requested_days ?? 90 ).' days daily closing balance.');

        }catch (Exception $e){
            return $this->responseError([], $e->getMessage());
        }
    }

    public function averageBalance(Request $request)
    {

        try{
            $today = Carbon::today();
            $loggedInUser = Auth::guard()->user();
            $totalBalance = 0;
            for ($i = 0; $i <  ( $request->requested_days ?? 90); $i++) {
                $date = $today->copy()->subDays($i);
                $totalBalance += $this->calculateBalance($loggedInUser, $date);
            }
            $averageBalance = $totalBalance / ( $request->requested_days ?? 90);
            return $this->responseSuccess([
                'requested_for_days' => ($request->requested_days ?? 90 ),
                'average_balance' => $averageBalance,
            ], 'The transaction has been completed successfully | '. ($request->requested_days ?? 90 ).' days average balance.');

        }catch (Exception $e){
            return $this->responseError([], $e->getMessage());
        }
    }

    public function averageSegmentBalance(Request $request)
    {
        try{

            $today = Carbon::today();
            $loggedInUser = Auth::guard()->user();
            $totalNDays = $request->totalNDays ?? 90;
            $firstNDays = $request->firstNDays ?? 30;
            $lastNDays = $request->lastNDays ?? 30;
            $firstNDaysBalance = 0;
            $lastNDaysBalance = 0;

            for ($i = 0; $i < $firstNDays; $i++) {
                $date = $today->copy()->subDays($i);
                $lastNDaysBalance += $this->calculateBalance($loggedInUser, $date);
            }

            for ($i = ($totalNDays - $lastNDays); $i < $totalNDays; $i++) {
                $date = $today->copy()->subDays($i);
                $firstNDaysBalance += $this->calculateBalance($loggedInUser, $date);
            }

            $firstNDaysAverage = $firstNDaysBalance / $firstNDays;
            $lastNDaysAverage = $lastNDaysBalance / $lastNDays;

            return $this->responseSuccess([
                'first_n_days' => $firstNDaysAverage,
                'last_n_days' => $lastNDaysAverage,
            ], 'The transaction has been completed successfully | First '.$firstNDays .' days and last '.$lastNDays.' days average closing balance');


        }catch (Exception $e){
            return $this->responseError([], $e->getMessage());
        }

    }


    public function lastNDaysIncome(Request $request)
    {
        try{
            $lastNdays = $request->lastNDays ?? 30;
            $exceptCatID = $request->exceptCatID ?? 18020004;
            $loggedInUser = Auth::guard()->user();
            $date = Carbon::today()->subDays($lastNdays);
            $income = Transaction::where('trans_user_id', $loggedInUser->id)
                                 ->where('trans_type', 'credit')
                                 ->where('trans_date', '>=', $date)
                                 ->where('category_id', '!=', $exceptCatID)
                                 ->sum('trans_amount');

            return $this->responseSuccess([
                'income_amount' => $income,
            ], 'The transaction has been completed successfully | '.'Last '.$lastNdays.' days income except category id '.$exceptCatID);

        }catch (Exception $e){
            return $this->responseError([], $e->getMessage());
        }
    }

    public function debitTransactionCountLastNDays(Request $request)
    {
        try{
            $loggedInUser = Auth::guard()->user();
            $lastNdays = $request->lastNDays ?? 30;
            $date = Carbon::today()->subDays($lastNdays);

            $debitCount = Transaction::where('trans_user_id', $loggedInUser->id)
                                     ->where('trans_type', 'debit')
                                     ->where('trans_date', '>=', $date)
                                     ->count();

            return $this->responseSuccess([
               'debit_count' => $debitCount,
            ], 'The transaction has been completed successfully | last '.$lastNdays.' days debit count.');

        }catch (Exception $e){
            return $this->responseError([], $e->getMessage());
        }
    }

    public function incomeOverN(Request $request)
    {
        try{
            $loggedInUser = Auth::guard()->user();
            $transAmtGreaterThan = $request->transAmtGreaterThan ?? 15;
            $income = Transaction::where('trans_user_id', $loggedInUser->id)
            ->where('trans_type', 'credit')
            ->where('trans_amount', '>', $transAmtGreaterThan)
            ->sum('trans_amount');

            return $this->responseSuccess([
                'income_sum' => $income,
             ], 'The transaction has been completed successfully | Sum of income with transaction amount > '.$transAmtGreaterThan);

        }catch (Exception $e){
            return $this->responseError([], $e->getMessage());
        }

    }


    private function calculateBalance(User $user, Carbon $requestedDate): float
    {
        $transactions = Transaction::where('trans_user_id', $user->id)
            ->where('trans_date', '<=', $requestedDate)
            ->orderBy('trans_date', 'asc')
            ->get();

        $balance = $user->initial_balance;

        foreach ($transactions as $transaction) {
            $balance += $transaction->trans_type === 'credit' ? $transaction->trans_amount : -$transaction->trans_amount;
        }

        return $balance;
    }

    private function updateBalance(float $currentBalance, float $transactionAmount, string $transactionType): float
    {
        return $transactionType === 'credit'
            ? $currentBalance + $transactionAmount
            : $currentBalance - $transactionAmount;
    }
}
