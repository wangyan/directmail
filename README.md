# DirectMail

使用阿里云的邮件推送（DirectMail）发送邮件。

需要 Laravel 5.X ，目前仅支持单一发信接口，后续会支持批量发信接口。

优点是非常简洁，没有引入阿里云全家桶，使用 laravel mailables 发送邮件。

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

> <http://d.laravel-china.org/docs/5.4/mail>

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

## 贡献

- <https://github.com/NauxLiu/Laravel-SendCloud>
- <https://github.com/rainwsy/aliyundm>