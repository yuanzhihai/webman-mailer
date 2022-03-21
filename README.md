# webman-mailer

基于 symfony mailer二次开发 支持邮件模板、纯文本、附件邮件发送以及更多邮件功能, 邮件发送简单到只需一行代码

## 安装

```
composer require yzh52521/webman-mailer
```

## 配置

配置文件 `config/plugin/yzh52521/mailer/app.php` 内容如下

```
return [
    'enable'   => true,
    'scheme'   => 'smtp',// "smtps": using TLS, "smtp": without using TLS.
    'host'     => '', // 服务器地址
    'username' => '', //用户名
    'password' => '', // 密码
    'port'     => 25, // SMTP服务器端口号,一般为25
    'options'  => [], // See: https://symfony.com/doc/current/mailer.html#tls-peer-verification
    'debug'    => false, // 开启debug模式会直接抛出异常, 记录邮件发送日志
    'embed'    => 'cid:', // 邮件中嵌入图片元数据标记
];

or

return [
    'enable'   => true,
    'dsn'      => 'smtp://username:password@smtp.example.com:25', //默认优先使用该配置
    'debug'    => false, // 开启debug模式会直接抛出异常, 记录邮件发送日志
    'embed'    => 'cid:', // 邮件中嵌入图片元数据标记
];

```

## 优雅的发送邮件

```
use yzh52521\mailer\think\Mailer;

$mailer = new Mailer();
$mailer->setFrom('10086@qq.com')
      ->setTo('your-mail@domain.com')
      ->setSubject('纯文本测试')
      ->setTextBody('欢迎您使用webman-mailer')
      ->send();
```

### 创建实例

不传递任何参数表示使用配置文件里默认的配置

```
$mailer = new Mailer();
```

### 设置发件人

发件人邮箱地址必须和配配置里的 username 一致

```
$mailer->setFrom('10086@qq.com');
```

### 设置收件人

以下几种方式任选一种

```
$mailer->setTo(['10086@qq.com']);
$mailer->setTo('10086@qq.com');
$mailer->setTo(['tianpian0805@qq.com', '10086@qq.com']);
```

### 设置邮件主题

```
$mailer->setSubject('邮件主题');
```

### 设置邮件内容 - 纯文本

```
$mailer->setTextBody('欢迎使用webman-mailer');

或者使用变量替换纯文本内容

$mailer->setTextBody('欢迎使用{name}', ['name' => 'webman-mailer']);
```

### 设置邮件内容 - HTML

```
$mailer->setHtmlBody('<p>欢迎使用webman-mailer</p>');

或者使用变量替换HTML内容

$mailer->setHtmlBody('<p>欢迎使用{name}</p>', ['name' => 'webman-mailer']);
```

### 设置邮件内容 - 模板

ThinkPHP模板, 具体请看ThinkPHP框架的模板怎么用, 第二个参数是要进行模板赋值的数组

```
$mailer->view('mail/register');
$mailer->view('common@mail/register', ['account' => $account, 'name' => $name]);
```

### 将图片作为元数据嵌入到邮件中

邮件内容中包含图片的, 可以直接指定 `img` 标签的 `src` 属性为远程图片地址, 此处图片地址必须为远程图片地址, 必须为一个带域名的完整图片链接, 这似乎很麻烦, 所以你还可以将图片作为元数据嵌入到邮件中,
至于其他文件是否也可以嵌入请自己尝试

下面介绍一下 `webman-mailer` 如何快速简便的将图片元数据嵌入到邮件中:

#### 配置嵌入标签

嵌入元数据需要在模板赋值或者使用 `setHtmlBody()` 传递变量时, 给变量添加特殊的标签, 该嵌入标签默认为 `cid:`, 你可以修改配置文件中 `embed` 项, 修改为你想要的形式

#### 模板或HTML中设置变量

在模板中, 例如 ThinkPHP框架 都是使用 `{$var}` 的形式传递变量, 假设变量为 `image_src`, 那么模板中填写 `{$image_src}`, 如果是在HTML中, 请使用 `{image_src}`,
注意如果修改过左、右定界符请使用自己定义的左右定界符

#### 传递变量参数和值

在 `setHtmlBody()` 和 `view()` 方法的第二个参数里, 该数组必须有一个变量, 格式为 `['cid:image_src'] => '/path/to/image.jpg']`
或者 `['cid:image_src'] => ['file_stream', 'filename','filemime']]`, 即参数数组的键名是上面配置的 `嵌入标签 + 变量名`, 但值有两种情况:

第一, 如果值为字符串, 则该值为图片的路径 (绝对路径或相对路径) 或者 有效的url地址 第二, 如果值为数组, 数组为 `['stream','name','mime']` 的形式, 其中 `stream` 表示图片的数据流,
即是未保存的文件数据流, 例如 `fopen()` 方法获取的文件数据流, 第二个参数为文件名, 默认为 `image`,第三个参数可选, 为文件的mime类型, 默认为 null

#### 示例

```
$mailer->setForm('10086@qq.com')
       ->setTo('10086@qq.com') 
       ->setSubject('测试邮件模板中嵌入图片元数据')
       ->view('mail/index', [
        'date' => date('Y-m-d H:i:s'),     
        'cid:image' => '/path/to/images/image.jpg',
        // 'cid:image' => '/path/to/images/image.jpg',
        // 'cid:image' => [fopen('/path/to/images/image.jpg','r')],
        // 'cid:image' => [fopen('/path/to/images/image.jpg','r'), 'image','image/jpg'],
     ])
    ->send();
```

其中模板的内容如下:

```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>测试邮件</title>
</head>
<body>
<p>尊敬的yzh52521:</p>
<p>     这是一封模板测试邮件</p>
<p>{$date}</p>
<p>
    <img src="{$image}" alt="">
</p>
</body>
</html>
```

在 HTML 中使用一样:

```
$mailer
    ->setForm('10086@qq.com')
    ->setTo('10086@qq.com') 
    ->setSubject('测试邮件模板中嵌入图片元数据')
    ->setHtmlBody('<img src="{image}" />图片测试', [
        'cid:image' => '/path/to/images/image.jpg',
        // 'cid:image' => '/path/to/images/image.jpg',
        // 'cid:image' => [fopen('/path/to/images/image.jpg','r')],
        // 'cid:image' => [fopen('/path/to/images/image.jpg','r'), 'image','image/jpg')],
     ])
    ->send();
```

### 添加附件

```
$mailer->attach('/path/to/images/file.jpg');

$mailer->attachContent(fopen('/path/to/documents/contract.doc', 'r'))
```

或者指定附件的文件名

```
$mailer->attach('/path/to/images/foo.jpg', ['fileName'=>文件名.jpg','contentType'=>'image/jpeg']);

$mailer->attachContent(fopen('/path/to/documents/contract.doc', 'r'), ['fileName'=>文件名.doc','contentType'=>'application/msword'])

```

###设置消息加密/签名
```
消息加密
$encryptor=new SMimeEncrypter('/path/to/certificate.crt');
$mailer->withEncryptor($encryptor);  @see https://symfony.com/doc/current/mailer.html#encrypting-messages

签名

$signer = new DkimSigner('file:///path/to/private-key.key', 'example.com', 'sf');
or
$signer = new SMimeSigner('/path/to/certificate.crt', '/path/to/certificate-private-key.key');


$mailer->withSigner($signer); @see https://symfony.com/doc/current/mailer.html#signing-messages

```

### 设置字符编码

```
$mailer->setCharset('utf8');
```

### 设置邮件优先级

```
$mailer->setPriority(1);
// 可选值有: 
// 1 Highest
// 2 High
// 3 Normal
// 4 Low
// 5 Lowest
```

### 发送邮件

```
$mailer->send();
```

使用匿名函数 $message是 \Symfony\Component\Mime\Email 对象

```
$mailer->send(function ($mailer,$message) {
    $mailer->setFrom('10086@qq.com')
           ->setTo('10086@qq.com')
           ->setSubject('测试邮件')
           ->setTextBody('测试内容');
});
```

第二个参数是数组, 可以重新邮件配置

```
$mailer->send(
    function ($message) {
         $mailer->setFrom(''10086@qq.com')
           ->setTo('10086@qq.com')
           ->setSubject('测试邮件')
           ->setTextBody('测试内容');
            },
            ['scheme'=>'',
            'host'     => '', // 服务器地址
            'username' => '', //用户名
            'password' => '', // 密码
            'port'     => 465, // SMTP服务器端口号,一般为25
            'options'  => [], // See: https://symfony.com/doc/current/mailer.html#tls-peer-verification
            'dsn'      => '']
            );
```

开启 `debug` 模式后, 邮件发送失败会直接以异常抛出, 如果没有开启, 可以通过 `getError()` 获取错误信息

```
$mailer->getError();
```

使用 `getHeaders()` 和 `getHeadersString()` 方法可以获取头信息
`getHeaders()` 返回的是头信息数组, `getHeadersString()` 返回的是头信息字符串

## Issues

如果有遇到问题请提交 [issues](https://github.com/yzh52521/webman-mailer/issues)

## License

Apache 2.0



