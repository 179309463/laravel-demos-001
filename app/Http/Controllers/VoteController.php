<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoteController extends Controller
{
    /**
     * 处理投票
     */
    public function vote(Request $request, Link $link)
    {
        $request->validate([
            'type' => 'required|in:up,down',
        ]);

        $userId = Auth::id();
        $requestType = $request->type;
        $voteType = $requestType === 'up' ? 'upvote' : 'downvote';

        // 查找现有投票
        $existingVote = Vote::where('user_id', $userId)
            ->where('link_id', $link->id)
            ->first();

        if ($existingVote) {
            if ($existingVote->type === $voteType) {
                // 如果是相同类型的投票，则删除（取消投票）
                $existingVote->delete();
                $message = $requestType === 'up' ? '取消点赞' : '取消踩';
            } else {
                // 如果是不同类型的投票，则更新
                $existingVote->update(['type' => $voteType]);
                $message = $requestType === 'up' ? '已点赞' : '已踩';
            }
        } else {
            // 创建新投票
            Vote::create([
                'user_id' => $userId,
                'link_id' => $link->id,
                'type' => $voteType,
            ]);
            $message = $requestType === 'up' ? '已点赞' : '已踩';
        }

        if ($request->ajax()) {
            // 返回JSON响应用于AJAX请求
            $link->load('votes');
            return response()->json([
                'success' => true,
                'message' => $message,
                'net_votes' => $link->net_votes,
                'user_vote' => $link->votes->where('user_id', $userId)->first()?->type,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
