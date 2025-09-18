<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Link;
use App\Models\Comment;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 创建测试用户
        $users = collect();
        
        // 创建管理员用户
        $admin = User::create([
            'name' => '管理员',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $users->push($admin);
        
        // 创建普通测试用户
        $testUser = User::create([
            'name' => '测试用户',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $users->push($testUser);
        
        // 创建更多用户
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => "用户{$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $users->push($user);
        }
        
        // 创建链接数据
        $links = collect();
        
        $linkData = [
            [
                'title' => 'Laravel 官方文档',
                'url' => 'https://laravel.com/docs',
                'description' => 'Laravel 框架的官方文档，包含详细的使用指南和API参考。',
            ],
            [
                'title' => 'Vue.js 官方网站',
                'url' => 'https://vuejs.org',
                'description' => '渐进式 JavaScript 框架，易学易用，性能出色，适用场景丰富。',
            ],
            [
                'title' => 'Tailwind CSS',
                'url' => 'https://tailwindcss.com',
                'description' => '实用优先的 CSS 框架，快速构建现代化的用户界面。',
            ],
            [
                'title' => 'GitHub',
                'url' => 'https://github.com',
                'description' => '全球最大的代码托管平台，开发者协作的首选工具。',
            ],
            [
                'title' => 'Stack Overflow',
                'url' => 'https://stackoverflow.com',
                'description' => '程序员问答社区，解决编程问题的最佳去处。',
            ],
        ];
        
        foreach ($linkData as $data) {
            $link = Link::create([
                'title' => $data['title'],
                'url' => $data['url'],
                'description' => $data['description'],
                'user_id' => $users->random()->id,
            ]);
            $links->push($link);
        }
        
        // 为链接创建投票
        foreach ($links as $link) {
            // 随机为每个链接创建1-3个投票
            $voteCount = rand(1, 3);
            $votedUsers = $users->random($voteCount);
            
            foreach ($votedUsers as $user) {
                Vote::create([
                    'user_id' => $user->id,
                    'link_id' => $link->id,
                    'type' => rand(0, 1) ? 'upvote' : 'downvote', // 随机点赞或点踩
                ]);
            }
        }
        
        // 为链接创建评论
        $comments = [
            '这个资源非常有用，感谢分享！',
            '文档很详细，对初学者很友好。',
            '界面设计很棒，用户体验很好。',
            '功能强大，值得推荐给大家。',
            '学到了很多新知识，谢谢！',
            '这个工具确实能提高开发效率。',
            '社区很活跃，问题能快速得到解答。',
            '代码质量很高，可以学习借鉴。',
        ];
        
        foreach ($links as $link) {
            // 随机为每个链接创建0-3个评论
            $commentCount = rand(0, 3);
            
            for ($i = 0; $i < $commentCount; $i++) {
                Comment::create([
                    'content' => $comments[array_rand($comments)],
                    'user_id' => $users->random()->id,
                    'link_id' => $link->id,
                ]);
            }
        }
        
        $this->command->info('数据库填充完成！');
        $this->command->info('创建了 ' . $users->count() . ' 个用户');
        $this->command->info('创建了 ' . $links->count() . ' 个链接');
        $this->command->info('创建了 ' . Vote::count() . ' 个投票');
        $this->command->info('创建了 ' . Comment::count() . ' 个评论');
        $this->command->info('');
        $this->command->info('测试账户信息：');
        $this->command->info('管理员: admin@example.com / password');
        $this->command->info('测试用户: test@example.com / password');
        $this->command->info('其他用户: user1@example.com, user2@example.com, user3@example.com / password');
    }
}
