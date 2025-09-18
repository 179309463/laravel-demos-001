@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-12 w-12 text-blue-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                验证您的邮箱地址
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                我们已向您的邮箱发送了验证链接
            </p>
        </div>
        
        @if (session('resent'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            验证邮件已重新发送到您的邮箱地址
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="bg-white shadow rounded-lg p-6">
            <div class="text-center">
                <p class="text-gray-700 mb-4">
                    在继续使用之前，请检查您的邮箱中的验证链接。
                </p>
                <p class="text-sm text-gray-600 mb-6">
                    邮箱地址：<span class="font-medium text-gray-900">{{ Auth::user()->email }}</span>
                </p>
                
                <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        重新发送验证邮件
                    </button>
                </form>
            </div>
        </div>
        
        <!-- 验证说明 -->
        <div class="bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-blue-900 mb-3">验证步骤</h3>
            <ol class="text-blue-800 space-y-3 text-sm">
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">1</span>
                    <div>
                        <p class="font-medium">检查您的邮箱</p>
                        <p class="text-blue-700">查看收件箱中来自我们的验证邮件</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">2</span>
                    <div>
                        <p class="font-medium">点击验证链接</p>
                        <p class="text-blue-700">点击邮件中的"验证邮箱地址"按钮</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium mr-3">3</span>
                    <div>
                        <p class="font-medium">完成验证</p>
                        <p class="text-blue-700">验证成功后即可正常使用所有功能</p>
                    </div>
                </li>
            </ol>
        </div>
        
        <!-- 帮助信息 -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        没有收到验证邮件？
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>请检查垃圾邮件或促销邮件文件夹</li>
                            <li>确认邮箱地址是否正确</li>
                            <li>等待几分钟后再检查</li>
                            <li>点击上方按钮重新发送验证邮件</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 其他操作 -->
        <div class="flex justify-between items-center pt-4">
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="text-sm text-gray-600 hover:text-gray-900">
                退出登录
            </a>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
            
            <a href="#" class="text-sm text-blue-600 hover:text-blue-500">
                联系客服
            </a>
        </div>
        
        <!-- 安全提示 -->
        <div class="text-center">
            <p class="text-xs text-gray-500">
                为了您的账户安全，请不要将验证链接分享给他人
            </p>
        </div>
    </div>
</div>

<script>
// 自动刷新页面检查验证状态
let checkInterval;
let checkCount = 0;
const maxChecks = 60; // 最多检查60次（5分钟）

function checkVerificationStatus() {
    checkCount++;
    
    fetch('/email/verify-status', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.verified) {
            clearInterval(checkInterval);
            // 显示成功消息并重定向
            showSuccessMessage();
            setTimeout(() => {
                window.location.href = data.redirect || '/home';
            }, 2000);
        } else if (checkCount >= maxChecks) {
            clearInterval(checkInterval);
        }
    })
    .catch(error => {
        console.log('检查验证状态时出错:', error);
        if (checkCount >= maxChecks) {
            clearInterval(checkInterval);
        }
    });
}

function showSuccessMessage() {
    const successDiv = document.createElement('div');
    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    successDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            邮箱验证成功！正在跳转...
        </div>
    `;
    document.body.appendChild(successDiv);
    
    // 3秒后移除消息
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv);
        }
    }, 3000);
}

// 开始检查验证状态（每5秒检查一次）
checkInterval = setInterval(checkVerificationStatus, 5000);

// 页面可见性变化时的处理
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // 页面变为可见时立即检查一次
        checkVerificationStatus();
    }
});

// 重新发送邮件的处理
document.querySelector('form').addEventListener('submit', function(e) {
    const button = this.querySelector('button');
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        发送中...
    `;
    
    // 3秒后恢复按钮状态
    setTimeout(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    }, 3000);
});
</script>
@endsection