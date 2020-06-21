<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayCardController extends Controller
{
    /**
     * Store a new user's result.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validateData = $this->validate($request,[
            'inputCards.*' => [
                'required',
                Rule::in(['2','3','4','5','6','7','8','9','10','J','Q','K','A','j','q','k','a']),
            ],
            'user_name' => 'required',
            'user_score' => 'required|numeric',
            'generated_hand_score' => 'required|numeric',
            'user_win' => 'required|numeric',
        ]);
        
        $result = DB::table('game_result')->insert($request->except('inputCards')+['created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        return response()->json(['data'=>$result], 201);
    }

    /**
     * Get all user's result to display at leaderboard
     *
     * @param  Request  $request
     * @return Response
     */
    public function leaderboard(Request $request)
    {
        $result = DB::table('game_result')->select(DB::raw('max(id) AS id'),'user_name AS userName',DB::raw('count(id) AS playedGame'),DB::raw('sum(user_win) AS wonGame'))->groupBy('user_name')->get();
        return response()->json(['data'=>$result], 200);
    }
}
