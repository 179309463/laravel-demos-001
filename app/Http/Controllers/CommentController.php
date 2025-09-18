<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * 存储新评论
     */
    public function store(Request $request, Link $link)
    {
        $request->validate([
            'content' => 'required|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ], [
            'content.required' => '评论内容不能为空',
            'content.max' => '评论内容不能超过1000个字符',
            'parent_id.exists' => '回复的评论不存在',
        ]);

        Comment::create([
            'content' => $request->content,
            'user_id' => Auth::id(),
            'link_id' => $link->id,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('links.show', $link)->with('success', '评论发表成功！');
    }

    /**
     * 更新评论
     */
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);
        
        $request->validate([
            'content' => 'required|max:1000',
        ], [
            'content.required' => '评论内容不能为空',
            'content.max' => '评论内容不能超过1000个字符',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return redirect()->route('links.show', $comment->link)->with('success', '评论更新成功！');
    }

    /**
     * 删除评论
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        
        $link = $comment->link;
        $comment->delete();
        
        return redirect()->route('links.show', $link)->with('success', '评论删除成功！');
    }
}
