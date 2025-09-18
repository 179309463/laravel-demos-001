@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- 返回按钮 -->
    <div class="mb-6">
        <a href="{{ route('links.index') }}" class="text-blue-600 hover:text-blue-800">
            ← 返回链接列表
        </a>
    </div>

    <!-- 链接详情 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-start space-x-4">
            <!-- 投票区域 -->
            <div class="flex flex-col items-center space-y-1">
                @auth
                    <form action="{{ route('links.vote', $link) }}" method="POST" class="vote-form">
                        @csrf
                        <input type="hidden" name="type" value="up">
                        <button type="submit" class="vote-btn {{ $link->votes->where('user_id', auth()->id())->where('type', 'up')->count() > 0 ? 'text-green-600' : 'text-gray-400 hover:text-green-600' }}">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>
                @else
                    <svg class="w-8 h-8 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                @endauth
                
                <span class="font-bold text-2xl net-votes">{{ $link->net_votes }}</span>
                
                @auth
                    <form action="{{ route('links.vote', $link) }}" method="POST" class="vote-form">
                        @csrf
                        <input type="hidden" name="type" value="down">
                        <button type="submit" class="vote-btn {{ $link->votes->where('user_id', auth()->id())->where('type', 'down')->count() > 0 ? 'text-red-600' : 'text-gray-400 hover:text-red-600' }}">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>
                @else
                    <svg class="w-8 h-8 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                @endauth
            </div>

            <!-- 链接内容 -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold mb-4">{{ $link->title }}</h1>
                
                @if($link->description)
                    <p class="text-gray-700 mb-4 text-lg leading-relaxed">{{ $link->description }}</p>
                @endif
                
                <div class="flex items-center space-x-6 text-sm text-gray-500 mb-4">
                    <span>由 <strong class="text-gray-700">{{ $link->user->name }}</strong> 发布</span>
                    <span>{{ $link->created_at->diffForHumans() }}</span>
                </div>
                
                <div class="mb-4">
                    <a href="{{ $link->url }}" target="_blank" class="inline-flex items-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg">
                        访问原链接
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- 操作按钮 -->
            @can('update', $link)
                <div class="flex flex-col space-y-2">
                    <a href="{{ route('links.edit', $link) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center">
                        编辑
                    </a>
                    <form action="{{ route('links.destroy', $link) }}" method="POST" onsubmit="return confirm('确定要删除这个链接吗？')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full">
                            删除
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>

    <!-- 评论区域 -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">评论 ({{ $link->comments->count() }})</h2>
        
        <!-- 发表评论表单 -->
        @auth
            <form action="{{ route('comments.store') }}" method="POST" class="mb-8">
                @csrf
                <input type="hidden" name="link_id" value="{{ $link->id }}">
                
                <div class="mb-4">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">发表评论</label>
                    <textarea name="content" id="content" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="写下你的想法..." required>{{ old('content') }}</textarea>
                    @error('content')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    发表评论
                </button>
            </form>
        @else
            <div class="mb-8 p-4 bg-gray-100 rounded-lg text-center">
                <p class="text-gray-600">请 <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">登录</a> 后发表评论</p>
            </div>
        @endauth
        
        <!-- 评论列表 -->
        @if($link->comments->count() > 0)
            <div class="space-y-6">
                @foreach($link->comments->sortByDesc('created_at') as $comment)
                    <div class="border-l-4 border-blue-200 pl-4">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center space-x-2">
                                <strong class="text-gray-800">{{ $comment->user->name }}</strong>
                                <span class="text-gray-500 text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            
                            @can('update', $comment)
                                <div class="flex space-x-2">
                                    <button onclick="editComment({{ $comment->id }})" class="text-blue-600 hover:text-blue-800 text-sm">
                                        编辑
                                    </button>
                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="inline" onsubmit="return confirm('确定要删除这条评论吗？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                            删除
                                        </button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                        
                        <div id="comment-content-{{ $comment->id }}">
                            <p class="text-gray-700 leading-relaxed">{{ $comment->content }}</p>
                        </div>
                        
                        <!-- 编辑评论表单（隐藏） -->
                        <div id="edit-form-{{ $comment->id }}" class="hidden mt-3">
                            <form action="{{ route('comments.update', $comment) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <textarea name="content" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>{{ $comment->content }}</textarea>
                                <div class="mt-2 space-x-2">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                        保存
                                    </button>
                                    <button type="button" onclick="cancelEdit({{ $comment->id }})" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded text-sm">
                                        取消
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">暂无评论</h3>
                <p class="mt-1 text-sm text-gray-500">成为第一个评论的人吧！</p>
            </div>
        @endif
    </div>
</div>

<script>
// AJAX 投票功能
document.addEventListener('DOMContentLoaded', function() {
    const voteForms = document.querySelectorAll('.vote-form');
    
    voteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const url = form.action;
            
            window.ajaxPost(url, Object.fromEntries(formData))
            .then(data => {
                if (data.success) {
                    // 更新投票数
                    const netVotesElement = document.querySelector('.net-votes');
                    netVotesElement.textContent = data.net_votes;
                    
                    // 更新按钮状态
                    const voteButtons = document.querySelectorAll('.vote-btn');
                    voteButtons.forEach(btn => {
                        btn.classList.remove('text-green-600', 'text-red-600');
                        btn.classList.add('text-gray-400');
                    });
                    
                    if (data.user_vote === 'up') {
                        document.querySelector('input[value="up"]').closest('form').querySelector('.vote-btn').classList.remove('text-gray-400');
                        document.querySelector('input[value="up"]').closest('form').querySelector('.vote-btn').classList.add('text-green-600');
                    } else if (data.user_vote === 'down') {
                        document.querySelector('input[value="down"]').closest('form').querySelector('.vote-btn').classList.remove('text-gray-400');
                        document.querySelector('input[value="down"]').closest('form').querySelector('.vote-btn').classList.add('text-red-600');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});

// 评论编辑功能
function editComment(commentId) {
    document.getElementById('comment-content-' + commentId).classList.add('hidden');
    document.getElementById('edit-form-' + commentId).classList.remove('hidden');
}

function cancelEdit(commentId) {
    document.getElementById('comment-content-' + commentId).classList.remove('hidden');
    document.getElementById('edit-form-' + commentId).classList.add('hidden');
}
</script>
@endsection