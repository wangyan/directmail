# DirectMail

使用阿里云的邮件推送（DirectMail）发送邮件。

需要 Laravel 5.5+ ，目前仅支持单一发信接口，后续会支持批量发信接口。

优点是非常简洁，没有引入阿里云全家桶，使用 laravel mailables 发送邮件。

> 邮件推送（DirectMail）是一款简单高效的电子邮件发送服务，它构建在可靠稳定的阿里云基础之上，帮助您快速、精准地实现事务邮件、通知邮件和批量邮件的发送。邮件推送历经两年双11考验，在发送速度、系统稳定性和到达率上表现优异；提供丰富的接口和灵活的使用方式，为企业和开发者解决邮件投递的难题，用户无需自建邮件服务器，开通服务即可享受阿里云优质的邮件服务，获得邮件投递的最佳实践。

> DirectMail 官网： <https://www.aliyun.com/product/directmail>

## 安装

在项目目录下执行

```bash
composer require wang_yan/directmail:dev-master
```

## 配置

修改 `config/app.php`，添加服务提供者

```php
<?php
'providers' => [
   // 添加这行
    WangYan\DirectMail\DirectMailTransportProvider::class,
];
```

在 `.env` 中配置你的密钥， 并修改邮件驱动为 `directmail`

```bash
MAIL_DRIVER=directmail

DIRECT_MAIL_KEY=     # AccessKeyId
DIRECT_MAIL_SECRET=  # AccessSecret
```

## 使用

详细用法请参考 laravel 文档： 

> <http://d.laravel-china.org/docs/5.5/mail>

使用演示：

```php
<?php
// routes\web.php
Route::get('/email', function(){
    $data = [
        'url'  => 'https://laravel.com',
        'name' => 'laravel'
    ];

    Mail::send('emails.register', $data, function ($message) {
        $message->from('us@example.com', 'Laravel');
        $message->to('foo@example.com');
        $message->subject('Hello World');
    });
});
```

## 实例

演示怎样发送注册验证邮件，首先初始化

```bash
php artisan make:auth
php artisan migrate
```

修改 `RegisterController` 控制器

```bash
<?php 
// app\Http\Controllers\Auth\RegisterController.php

use Illuminate\Support\Facades\Mail;

protected function create(array $data)
{
    $user =  User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        // 数据库 users 表要有 token 字段
        'token' => str_random(30), 
        'password' => bcrypt($data['password']),
    ]);

    $this->sendVerifyEmail($user);
    return $user;
}

private function sendVerifyEmail($user)
{
    $data = [
        'name' => $user->name,
        'url'  => Route('email.verify',['token' => $user->token])
    ];
    Mail::send('emails.register', $data, function ($message) use ($user) {
        $message->from('service@dm.mail.wangyan.org', env('APP_NAME','Laravel'));
        $message->to($user->email);
        $message->subject('请验证您的 Email 地址');
    });
}
```

修改 User 模型

```bash
<?php 
// app\User.php
    protected $fillable = [
        'name', 'email', 'password','token'
    ];
```

增加邮件模板

```bash
vim resources\views\emails\register.blade.php
```

增加路由

```php
<?php
// routes\web.php
Route::get('/email/verify/{token}', 'EmailController@verify')->name('email.verify');
```

增加控制器

```bash
php artisan make:controller EmailController
```

编辑控制器

```php
<?php
class EmailController extends Controller
{
    /**
     * @param $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    function verify($token)
    {
        $user = User::where('token',$token)->first();

        if(is_null($user)){
            return redirect('/');
        }

        $user->is_active = 1;

        $user->token= str_random(30);
        $user->save();

        return redirect('/home');
    }
}
```

## 贡献

- <https://github.com/NauxLiu/Laravel-SendCloud>
- <https://github.com/rainwsy/aliyundm>