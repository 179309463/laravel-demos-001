<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $links = Link::with(['user', 'votes'])
            ->withCount(['comments', 'votes'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('links.index', compact('links'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('links.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|max:1000',
        ], [
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'url.required' => '链接不能为空',
            'url.url' => '请输入有效的URL地址',
            'url.max' => '链接不能超过255个字符',
            'description.max' => '描述不能超过1000个字符',
        ]);

        Link::create([
            'title' => $request->title,
            'url' => $request->url,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('links.index')->with('success', '链接创建成功！');
    }

    /**
     * Display the specified resource.
     */
    public function show(Link $link)
    {
        $link->load(['user', 'comments.user', 'comments.replies.user', 'votes']);
        
        return view('links.show', compact('link'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Link $link)
    {
        $this->authorize('update', $link);
        
        return view('links.edit', compact('link'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Link $link)
    {
        $this->authorize('update', $link);
        
        $request->validate([
            'title' => 'required|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|max:1000',
        ], [
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'url.required' => '链接不能为空',
            'url.url' => '请输入有效的URL地址',
            'url.max' => '链接不能超过255个字符',
            'description.max' => '描述不能超过1000个字符',
        ]);

        $link->update([
            'title' => $request->title,
            'url' => $request->url,
            'description' => $request->description,
        ]);

        return redirect()->route('links.show', $link)->with('success', '链接更新成功！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Link $link)
    {
        $this->authorize('delete', $link);
        
        $link->delete();
        
        return redirect()->route('links.index')->with('success', '链接删除成功！');
    }
}
