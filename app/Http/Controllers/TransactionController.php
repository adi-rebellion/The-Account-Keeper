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

 /**
 * @OA\Post(
 *     path="/api/transaction",
 *     summary="Make a new transaction",
 *     description="This endpoint allows the logged-in user to make a debit or credit transaction. The transaction will only be processed if the user has sufficient balance for debit transactions.",
 *     operationId="makeTransaction",
 *     tags={"Transaction"},
 *     security={{"bearer": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"trans_type", "trans_amount"},
 *                 @OA\Property(
 *                     property="trans_type",
 *                     type="string",
 *                     enum={"debit", "credit"},
 *                     description="Type of transaction: either debit or credit"
 *                 ),
 *                 @OA\Property(
 *                     property="trans_amount",
 *                     type="number",
 *                     format="float",
 *                     description="The amount for the transaction"
 *                 ),
 *                 @OA\Property(
 *                     property="category_id",
 *                     type="integer",
 *                     description="The ID of the transaction category"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     description="Description of the transaction"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="The transaction has been completed successfully.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="boolean",
 *                 description="Status of the transaction operation"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Success message"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="transaction",
 *                     type="object",
 *                     @OA\Property(property="trans_user_id", type="integer", description="User ID associated with the transaction"),
 *                     @OA\Property(property="trans_date", type="string", format="date-time", description="Date and time of the transaction"),
 *                     @OA\Property(property="trans_amount", type="number", format="float", description="Amount of the transaction"),
 *                     @OA\Property(property="trans_type", type="string", description="Type of the transaction (debit or credit)"),
 *                     @OA\Property(property="category_id", type="integer", description="ID of the transaction category"),
 *                     @OA\Property(property="description", type="string", description="Description of the transaction"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of when the transaction was last updated"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of when the transaction was created"),
 *                     @OA\Property(property="id", type="integer", description="ID of the transaction")
 *                 ),
 *                 @OA\Property(
 *                     property="balance_after_transaction",
 *                     type="number",
 *                     format="float",
 *                     description="User's balance after the transaction"
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 nullable=true,
 *                 description="Errors, if any"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request due to validation errors or insufficient balance",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the transaction operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the transaction operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     )
 * )
 */


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

    /**
 * @OA\Post(
 *     path="/api/daily-closing-bal",
 *     summary="Get daily closing balance",
 *     description="This endpoint returns the daily closing balance for a specified number of days. If no number of days is specified, it defaults to 90 days.",
 *     operationId="dailyClosingBalance",
 *     tags={"Transaction"},
 *     security={{"bearer": {}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="requested_days",
 *                     type="integer",
 *                     description="The number of days to fetch the closing balance for. Defaults to 90 days if not provided."
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="The transaction has been completed successfully. Returns the closing balance for the requested number of days.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="boolean",
 *                 description="Status of the operation"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Success message"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="requested_for_days",
 *                     type="integer",
 *                     description="The number of days for which the closing balance was requested"
 *                 ),
 *                 @OA\Property(
 *                     property="closing_balance",
 *                     type="object",
 *                     description="Key-value pairs of date and closing balance"
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 nullable=true,
 *                 description="Errors, if any"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request due to validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     )
 * )
 */

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
/**
 * @OA\Post(
 *     path="/api/average-bal",
 *     summary="Get average balance",
 *     description="This endpoint calculates and returns the average balance for a specified number of days. If no number of days is specified, it defaults to 90 days.",
 *     operationId="averageBalance",
 *     tags={"Transaction"},
 *     security={{"bearer": {}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="requested_days",
 *                     type="integer",
 *                     description="The number of days to calculate the average balance for. Defaults to 90 days if not provided."
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="The average balance has been calculated successfully.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="boolean",
 *                 description="Status of the operation"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Success message"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="requested_for_days",
 *                     type="integer",
 *                     description="The number of days for which the average balance was calculated"
 *                 ),
 *                 @OA\Property(
 *                     property="average_balance",
 *                     type="number",
 *                     format="float",
 *                     description="The calculated average balance"
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 nullable=true,
 *                 description="Errors, if any"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request due to validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     )
 * )
 */
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
/**
 * @OA\Post(
 *     path="/api/average-segment-bal",
 *     summary="Get average segment balance",
 *     description="This endpoint calculates and returns the average balance for two segments: the first N days and the last N days within a specified total number of days. If no number of days is specified, it defaults to 90 total days, with the first 30 days and the last 30 days being compared.",
 *     operationId="averageSegmentBalance",
 *     tags={"Transaction"},
 *     security={{"bearer": {}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="totalNDays",
 *                     type="integer",
 *                     description="The total number of days to consider. Defaults to 90 days if not provided."
 *                 ),
 *                 @OA\Property(
 *                     property="firstNDays",
 *                     type="integer",
 *                     description="The number of days from the start to calculate the average balance. Defaults to 30 days if not provided."
 *                 ),
 *                 @OA\Property(
 *                     property="lastNDays",
 *                     type="integer",
 *                     description="The number of days from the end to calculate the average balance. Defaults to 30 days if not provided."
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="The average segment balances have been calculated successfully.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="boolean",
 *                 description="Status of the operation"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Success message"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="first_n_days",
 *                     type="number",
 *                     format="float",
 *                     description="The average balance for the first N days"
 *                 ),
 *                 @OA\Property(
 *                     property="last_n_days",
 *                     type="number",
 *                     format="float",
 *                     description="The average balance for the last N days"
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 nullable=true,
 *                 description="Errors, if any"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request due to validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     )
 * )
 */
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

/**
 * @OA\Post(
 *     path="/api/last-n-days-income",
 *     summary="Get last N days income",
 *     description="This endpoint calculates and returns the total income for the last N days, excluding a specific category ID if provided. Defaults to the last 30 days and excludes category ID 18020004 if not specified.",
 *     operationId="lastNDaysIncome",
 *     tags={"Transaction"},
 *     security={{"bearer": {}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="lastNDays",
 *                     type="integer",
 *                     description="The number of days to calculate income for. Defaults to 30 days if not provided."
 *                 ),
 *                 @OA\Property(
 *                     property="exceptCatID",
 *                     type="integer",
 *                     description="Category ID to exclude from the income calculation. Defaults to 18020004 if not provided."
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="The income amount has been calculated successfully.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="boolean",
 *                 description="Status of the operation"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Success message"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="income_amount",
 *                     type="number",
 *                     format="float",
 *                     description="The total income amount for the specified period"
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 nullable=true,
 *                 description="Errors, if any"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request due to validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     )
 * )
 */
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
/**
 * @OA\Post(
 *     path="/api/debit-trans-count",
 *     summary="Get debit transaction count for last N days",
 *     description="This endpoint returns the count of debit transactions for the last N days for the authenticated user. Defaults to the last 30 days if not specified.",
 *     operationId="debitTransactionCountLastNDays",
 *     tags={"Transaction"},
 *     security={{"bearer": {}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="lastNDays",
 *                     type="integer",
 *                     description="The number of days to count debit transactions for. Defaults to 30 days if not provided."
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="The debit transaction count has been retrieved successfully.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="boolean",
 *                 description="Status of the operation"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Success message"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="debit_count",
 *                     type="integer",
 *                     description="The count of debit transactions for the specified period"
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 nullable=true,
 *                 description="Errors, if any"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request due to validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     )
 * )
 */
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
/**
 * @OA\Post(
 *     path="/api/income-over-n",
 *     summary="Get income sum for transactions over a specified amount",
 *     description="This endpoint calculates and returns the sum of income (credit transactions) where the transaction amount is greater than a specified value for the authenticated user. Defaults to transaction amounts greater than 15 if not specified.",
 *     operationId="incomeOverN",
 *     tags={"Transaction"},
 *     security={{"bearer": {}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="transAmtGreaterThan",
 *                     type="number",
 *                     format="float",
 *                     description="The minimum transaction amount to filter income. Defaults to 15 if not provided."
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="The sum of income has been calculated successfully.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="boolean",
 *                 description="Status of the operation"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Success message"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="income_sum",
 *                     type="number",
 *                     format="float",
 *                     description="The sum of income where transaction amount is greater than the specified value"
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 nullable=true,
 *                 description="Errors, if any"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request due to validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", description="Status of the operation"),
 *             @OA\Property(property="message", type="string", description="Error message"),
 *             @OA\Property(property="errors", type="object", description="Details of the error")
 *         )
 *     )
 * )
 */
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
