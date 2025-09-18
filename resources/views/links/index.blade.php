@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">热门链接</h1>
        @auth
            <a href="{{ route('links.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                发布新链接
            </a>
        @endauth
    </div>

    @if($links->count() > 0)
        <div class="space-y-4">
            @foreach($links as $link)
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start space-x-4">
                        <!-- 投票区域 -->
                        <div class="flex flex-col items-center space-y-1">
                            @auth
                                <form action="{{ route('links.vote', $link) }}" method="POST" class="vote-form">
                                    @csrf
                                    <input type="hidden" name="type" value="up">
                                    <button type="submit" class="vote-btn {{ $link->votes->where('user_id', auth()->id())->where('type', 'up')->count() > 0 ? 'text-green-600' : 'text-gray-400 hover:text-green-600' }}">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                            @endauth
                            
                            <span class="font-bold text-lg net-votes">{{ $link->net_votes }}</span>
                            
                            @auth
                                <form action="{{ route('links.vote', $link) }}" method="POST" class="vote-form">
                                    @csrf
                                    <input type="hidden" name="type" value="down">
                                    <button type="submit" class="vote-btn {{ $link->votes->where('user_id', auth()->id())->where('type', 'down')->count() > 0 ? 'text-red-600' : 'text-gray-400 hover:text-red-600' }}">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            @endauth
                        </div>

                        <!-- 链接内容 -->
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold mb-2">
                                <a href="{{ route('links.show', $link) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $link->title }}
                                </a>
                            </h2>
                            
                            @if($link->description)
                                <p class="text-gray-600 mb-3">{{ Str::limit($link->description, 200) }}</p>
                            @endif
                            
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>由 <strong>{{ $link->user->name }}</strong> 发布</span>
                                <span>{{ $link->created_at->diffForHumans() }}</span>
                                <a href="{{ $link->url }}" target="_blank" class="text-blue-500 hover:text-blue-700">
                                    访问链接 ↗
                                </a>
                                <a href="{{ route('links.show', $link) }}" class="text-gray-500 hover:text-gray-700">
                                    {{ $link->comments_count }} 条评论
                                </a>
                            </div>
                        </div>

                        <!-- 操作按钮 -->
                        @can('update', $link)
                            <div class="flex flex-col space-y-2">
                                <a href="{{ route('links.edit', $link) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                    编辑
                                </a>
                                <form action="{{ route('links.destroy', $link) }}" method="POST" onsubmit="return confirm('确定要删除这个链接吗？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                        删除
                                    </button>
                                </form>
                            </div>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 分页 -->
        <div class="mt-8">
            {{ $links->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">暂无链接</h3>
            <p class="mt-1 text-sm text-gray-500">开始分享第一个有趣的链接吧！</p>
            @auth
                <div class="mt-6">
                    <a href="{{ route('links.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        发布新链接
                    </a>
                </div>
            @endauth
        </div>
    @endif
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
                    const netVotesElement = form.closest('.bg-white').querySelector('.net-votes');
                    netVotesElement.textContent = data.net_votes;
                    
                    // 更新按钮状态
                    const voteButtons = form.closest('.bg-white').querySelectorAll('.vote-btn');
                    voteButtons.forEach(btn => {
                        btn.classList.remove('text-green-600', 'text-red-600');
                        btn.classList.add('text-gray-400');
                    });
                    
                    if (data.user_vote === 'up') {
                        form.closest('.bg-white').querySelector('input[value="up"]').closest('form').querySelector('.vote-btn').classList.remove('text-gray-400');
                        form.closest('.bg-white').querySelector('input[value="up"]').closest('form').querySelector('.vote-btn').classList.add('text-green-600');
                    } else if (data.user_vote === 'down') {
                        form.closest('.bg-white').querySelector('input[value="down"]').closest('form').querySelector('.vote-btn').classList.remove('text-gray-400');
                        form.closest('.bg-white').querySelector('input[value="down"]').closest('form').querySelector('.vote-btn').classList.add('text-red-600');
                    }
                }
            })
            .catch(error => {
                console.error('投票失败:', error);
            });
        });
    });
});
</script>
@endsection