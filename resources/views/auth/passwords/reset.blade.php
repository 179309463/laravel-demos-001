@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                设置新密码
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                请输入您的新密码
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">邮箱地址</label>
                <input id="email" 
                       name="email" 
                       type="email" 
                       autocomplete="email" 
                       required 
                       class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror" 
                       placeholder="请输入您的邮箱地址"
                       value="{{ $email ?? old('email') }}"
                       readonly>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">新密码</label>
                <div class="mt-1 relative">
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror" 
                           placeholder="请输入新密码（至少8位）">
                    <button type="button" 
                            onclick="togglePassword('password')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg id="password-eye" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <!-- 密码强度指示器 -->
                <div class="mt-2">
                    <div class="flex space-x-1">
                        <div id="strength-1" class="h-1 w-1/4 bg-gray-200 rounded"></div>
                        <div id="strength-2" class="h-1 w-1/4 bg-gray-200 rounded"></div>
                        <div id="strength-3" class="h-1 w-1/4 bg-gray-200 rounded"></div>
                        <div id="strength-4" class="h-1 w-1/4 bg-gray-200 rounded"></div>
                    </div>
                    <p id="strength-text" class="text-xs text-gray-500 mt-1">密码强度：弱</p>
                </div>
            </div>

            <div>
                <label for="password-confirm" class="block text-sm font-medium text-gray-700">确认新密码</label>
                <div class="mt-1 relative">
                    <input id="password-confirm" 
                           name="password_confirmation" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                           placeholder="请再次输入新密码">
                    <button type="button" 
                            onclick="togglePassword('password-confirm')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg id="password-confirm-eye" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <p id="password-match" class="text-xs mt-1 hidden"></p>
            </div>

            <div>
                <button type="submit" 
                        id="submit-btn"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    重置密码
                </button>
            </div>
            
            <div class="text-center">
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    返回登录页面
                </a>
            </div>
        </form>
        
        <!-- 密码要求说明 -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-blue-900 mb-3">密码要求</h3>
            <ul class="text-blue-800 space-y-2 text-sm">
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    至少8个字符
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    包含大小写字母
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    包含数字
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    建议包含特殊字符
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
// 密码显示/隐藏切换
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + '-eye');
    
    if (field.type === 'password') {
        field.type = 'text';
        eye.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
        `;
    } else {
        field.type = 'password';
        eye.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}

// 密码强度检查
function checkPasswordStrength(password) {
    let strength = 0;
    const checks = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        numbers: /\d/.test(password),
        special: /[^\w\s]/.test(password)
    };
    
    strength = Object.values(checks).filter(Boolean).length;
    
    return { strength, checks };
}

// 更新密码强度显示
function updatePasswordStrength() {
    const password = document.getElementById('password').value;
    const { strength } = checkPasswordStrength(password);
    
    const strengthBars = ['strength-1', 'strength-2', 'strength-3', 'strength-4'];
    const strengthText = document.getElementById('strength-text');
    
    // 重置所有强度条
    strengthBars.forEach(bar => {
        document.getElementById(bar).className = 'h-1 w-1/4 bg-gray-200 rounded';
    });
    
    // 根据强度设置颜色
    let color = 'bg-red-400';
    let text = '弱';
    
    if (strength >= 4) {
        color = 'bg-green-400';
        text = '强';
    } else if (strength >= 3) {
        color = 'bg-yellow-400';
        text = '中等';
    } else if (strength >= 2) {
        color = 'bg-orange-400';
        text = '一般';
    }
    
    // 填充强度条
    for (let i = 0; i < strength && i < 4; i++) {
        document.getElementById(strengthBars[i]).className = `h-1 w-1/4 ${color} rounded`;
    }
    
    strengthText.textContent = `密码强度：${text}`;
    strengthText.className = `text-xs mt-1 ${
        strength >= 4 ? 'text-green-600' :
        strength >= 3 ? 'text-yellow-600' :
        strength >= 2 ? 'text-orange-600' : 'text-red-600'
    }`;
}

// 检查密码确认
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('password-confirm').value;
    const matchElement = document.getElementById('password-match');
    const submitBtn = document.getElementById('submit-btn');
    
    if (confirm.length > 0) {
        if (password === confirm) {
            matchElement.textContent = '密码匹配';
            matchElement.className = 'text-xs mt-1 text-green-600';
            matchElement.classList.remove('hidden');
            submitBtn.disabled = false;
        } else {
            matchElement.textContent = '密码不匹配';
            matchElement.className = 'text-xs mt-1 text-red-600';
            matchElement.classList.remove('hidden');
            submitBtn.disabled = true;
        }
    } else {
        matchElement.classList.add('hidden');
        submitBtn.disabled = password.length < 8;
    }
}

// 事件监听
document.getElementById('password').addEventListener('input', function() {
    updatePasswordStrength();
    checkPasswordMatch();
});

document.getElementById('password-confirm').addEventListener('input', checkPasswordMatch);

// 表单提交验证
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('password-confirm').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('密码确认不匹配，请重新输入');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('密码长度至少需要8位');
        return false;
    }
});
</script>
@endsection