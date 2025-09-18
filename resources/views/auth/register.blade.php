@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                创建新账户
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                已有账户？
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    立即登录
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">姓名</label>
                    <input id="name" 
                           name="name" 
                           type="text" 
                           autocomplete="name" 
                           required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('name') border-red-500 @enderror" 
                           placeholder="请输入您的姓名"
                           value="{{ old('name') }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">邮箱地址</label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror" 
                           placeholder="请输入邮箱地址"
                           value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">密码</label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror" 
                           placeholder="请输入密码">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">密码至少需要8个字符</p>
                </div>
                
                <div>
                    <label for="password-confirm" class="block text-sm font-medium text-gray-700">确认密码</label>
                    <input id="password-confirm" 
                           name="password_confirmation" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                           placeholder="请再次输入密码">
                </div>
            </div>

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                注册失败
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center">
                <input id="agree-terms" 
                       name="agree_terms" 
                       type="checkbox" 
                       required
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="agree-terms" class="ml-2 block text-sm text-gray-900">
                    我已阅读并同意
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500">服务条款</a>
                    和
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500">隐私政策</a>
                </label>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    创建账户
                </button>
            </div>
            
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    注册后，您将能够发布链接、发表评论和参与投票。
                    我们承诺保护您的隐私信息。
                </p>
            </div>
        </form>
    </div>
</div>

<script>
// 密码强度检查
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthIndicator = document.getElementById('password-strength');
    
    if (!strengthIndicator) {
        // 创建密码强度指示器
        const indicator = document.createElement('div');
        indicator.id = 'password-strength';
        indicator.className = 'mt-1 text-xs';
        this.parentNode.appendChild(indicator);
    }
    
    let strength = 0;
    let message = '';
    let color = '';
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    switch (strength) {
        case 0:
        case 1:
            message = '密码强度：弱';
            color = 'text-red-500';
            break;
        case 2:
        case 3:
            message = '密码强度：中等';
            color = 'text-yellow-500';
            break;
        case 4:
        case 5:
            message = '密码强度：强';
            color = 'text-green-500';
            break;
    }
    
    const strengthElement = document.getElementById('password-strength');
    strengthElement.textContent = message;
    strengthElement.className = `mt-1 text-xs ${color}`;
});

// 密码确认检查
document.getElementById('password-confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    let indicator = document.getElementById('password-match');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'password-match';
        indicator.className = 'mt-1 text-xs';
        this.parentNode.appendChild(indicator);
    }
    
    if (confirmPassword === '') {
        indicator.textContent = '';
        return;
    }
    
    if (password === confirmPassword) {
        indicator.textContent = '密码匹配';
        indicator.className = 'mt-1 text-xs text-green-500';
    } else {
        indicator.textContent = '密码不匹配';
        indicator.className = 'mt-1 text-xs text-red-500';
    }
});

// 表单提交验证
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password-confirm').value;
    const agreeTerms = document.getElementById('agree-terms').checked;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('两次输入的密码不一致，请重新输入');
        return;
    }
    
    if (!agreeTerms) {
        e.preventDefault();
        alert('请先同意服务条款和隐私政策');
        return;
    }
});
</script>
@endsection