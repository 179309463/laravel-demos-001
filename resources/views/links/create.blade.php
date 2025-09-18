@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('links.index') }}" class="text-blue-600 hover:text-blue-800">
            ← 返回链接列表
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold mb-6">发布新链接</h1>
        
        <form action="{{ route('links.store') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    标题 <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       value="{{ old('title') }}"
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
                       value="{{ old('url') }}"
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
                          placeholder="简单描述一下这个链接的内容...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-1">可选项，帮助其他用户了解链接内容</p>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                    发布链接
                </button>
                
                <a href="{{ route('links.index') }}" class="text-gray-600 hover:text-gray-800">
                    取消
                </a>
            </div>
        </form>
    </div>
    
    <!-- 发布指南 -->
    <div class="mt-8 bg-blue-50 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-blue-900 mb-3">发布指南</h2>
        <ul class="text-blue-800 space-y-2">
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                确保链接地址有效且可访问
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                使用清晰、描述性的标题
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                添加有用的描述信息
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                分享有价值、有趣的内容
            </li>
        </ul>
    </div>
</div>

<script>
// 自动获取页面标题
document.getElementById('url').addEventListener('blur', function() {
    const url = this.value;
    const titleField = document.getElementById('title');
    
    if (url && !titleField.value) {
        // 这里可以添加获取页面标题的逻辑
        // 由于跨域限制，实际项目中可能需要后端支持
        console.log('可以在这里添加自动获取标题的功能');
    }
});

// 表单验证
document.querySelector('form').addEventListener('submit', function(e) {
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
</script>
@endsection