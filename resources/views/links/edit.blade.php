@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('links.show', $link) }}" class="text-blue-600 hover:text-blue-800">
            ← 返回链接详情
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold mb-6">编辑链接</h1>
        
        <form action="{{ route('links.update', $link) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    标题 <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       value="{{ old('title', $link->title) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror" 
                       placeholder="输入链接标题"
                       required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                    链接地址 <span class="text-red-500">*</span>
                </label>
                <input type="url" 
                       name="url" 
                       id="url" 
                       value="{{ old('url', $link->url) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('url') border-red-500 @enderror" 
                       placeholder="https://example.com"
                       required>
                @error('url')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-1">请输入完整的URL地址，包含 http:// 或 https://</p>
            </div>
            
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    描述
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror" 
                          placeholder="简单描述一下这个链接的内容...">{{ old('description', $link->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-1">可选项，帮助其他用户了解链接内容</p>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="space-x-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                        更新链接
                    </button>
                    
                    <a href="{{ route('links.show', $link) }}" class="text-gray-600 hover:text-gray-800">
                        取消
                    </a>
                </div>
                
                <form action="{{ route('links.destroy', $link) }}" method="POST" class="inline" onsubmit="return confirm('确定要删除这个链接吗？删除后无法恢复。')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        删除链接
                    </button>
                </form>
            </div>
        </form>
    </div>
    
    <!-- 链接信息 -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3">链接信息</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700">发布时间：</span>
                <span class="text-gray-600">{{ $link->created_at->format('Y年m月d日 H:i') }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">最后更新：</span>
                <span class="text-gray-600">{{ $link->updated_at->format('Y年m月d日 H:i') }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">投票数：</span>
                <span class="text-gray-600">{{ $link->net_votes }} ({{ $link->votes->where('type', 'up')->count() }} 赞, {{ $link->votes->where('type', 'down')->count() }} 踩)</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">评论数：</span>
                <span class="text-gray-600">{{ $link->comments->count() }} 条</span>
            </div>
        </div>
        
        <div class="mt-4">
            <span class="font-medium text-gray-700">当前链接：</span>
            <a href="{{ $link->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 break-all">
                {{ $link->url }}
            </a>
        </div>
    </div>
    
    <!-- 编辑指南 -->
    <div class="mt-8 bg-yellow-50 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-yellow-900 mb-3">编辑提示</h2>
        <ul class="text-yellow-800 space-y-2">
            <li class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                修改链接地址可能会影响用户体验
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                删除链接将同时删除所有相关评论和投票
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                建议在修改前备份重要信息
            </li>
        </ul>
    </div>
</div>

<script>
// 表单验证
document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const url = document.getElementById('url').value.trim();
    
    if (!title) {
        e.preventDefault();
        alert('请输入链接标题');
        document.getElementById('title').focus();
        return;
    }
    
    if (!url) {
        e.preventDefault();
        alert('请输入链接地址');
        document.getElementById('url').focus();
        return;
    }
    
    // 简单的URL格式验证
    const urlPattern = /^https?:\/\/.+/;
    if (!urlPattern.test(url)) {
        e.preventDefault();
        alert('请输入有效的URL地址（需要包含 http:// 或 https://）');
        document.getElementById('url').focus();
        return;
    }
});

// 检测内容变化
let originalTitle = document.getElementById('title').value;
let originalUrl = document.getElementById('url').value;
let originalDescription = document.getElementById('description').value;

function checkChanges() {
    const currentTitle = document.getElementById('title').value;
    const currentUrl = document.getElementById('url').value;
    const currentDescription = document.getElementById('description').value;
    
    const hasChanges = currentTitle !== originalTitle || 
                      currentUrl !== originalUrl || 
                      currentDescription !== originalDescription;
    
    const submitBtn = document.querySelector('button[type="submit"]');
    if (hasChanges) {
        submitBtn.classList.remove('bg-gray-400');
        submitBtn.classList.add('bg-blue-500', 'hover:bg-blue-700');
        submitBtn.disabled = false;
    } else {
        submitBtn.classList.remove('bg-blue-500', 'hover:bg-blue-700');
        submitBtn.classList.add('bg-gray-400');
        submitBtn.disabled = true;
    }
}

// 监听输入变化
document.getElementById('title').addEventListener('input', checkChanges);
document.getElementById('url').addEventListener('input', checkChanges);
document.getElementById('description').addEventListener('input', checkChanges);

// 初始检查
checkChanges();
</script>
@endsection