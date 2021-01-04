thinkphp-qiniu-sdk

基于tp5.1框架的七牛云存储实现，实现文件上传，文件管理功能

基于 https://github.com/teg1c/thinkphp-qiniu-sdk 进行修改，如有侵权请联系 fzwl@fzxywl.cn

composer 安装

```composer require xzhiyu/thinkphp-qiniu-sdk```


如果该方法安装不成功，请在项目根目录下的composer.json的require中添加

```"xzhiyu/thinkphp-qiniu-sdk": "dev-master"```

然后使用cmd进入项目根目录下运行composer update



配置使用
===============


## 配置：


在tp5.1的配置文件app.php中配置七牛云的配置参数
```
'qiniu' => [

        'accesskey' => '你自己的七牛云accesskey',
        'secretkey' => '你自己的七牛云secretkey',
        'bucket' => 'bucket',
 ]
```
## 使用

```
use xzhiyu\qiniu\Qiniu;
try{
      
      $qiniu = new Qiniu();
      $result = $qiniu->upload();
      dump($result);
    }catch (Exception $e){
      
      dump($e->getMessage());
    }
```
 
上传成功则返回的是key值为文件名


## 直接使用

```
  try{
  
      $qiniu = new Qiniu('你自己的七牛云accesskey','你自己的七牛云secretkey','你自己创建的bucket');
      $result = $qiniu->upload();
      
 }catch (Exception $e){
 
      dump($e->getMessage());
 }
```

## 文件流上传(这个属于新增)

使用的场景：微信小程序等生成的场景用于直接上传到cdn的情况，其余自测

```
use xzhiyu\qiniu\Qiniu;
try{
      
      $qiniu = new Qiniu();
      $result = $qiniu->binUpload('二进制文件流',['file_name'=>'文件名字', 'size' => '文件大小<单位：字节>']);
      dump($result);
    }catch (Exception $e){
      
      dump($e->getMessage());
    }
```

上传成功返回数据实例：
```
[
  {
    hash: "Fs0A2qYOuLKw9mpPvRE2zor-k-Gb",
    key: "qrcode/1011_article_qrcode.png"
  },
  null
]

```

上传失败(已存在的情况下):

```
[null,{}]

## 实际上有数据的
array(2) {
  [0]=>
  NULL
  [1]=>
  object(Qiniu\Http\Error)#182 (2) {
    ["url":"Qiniu\Http\Error":private]=>
    string(23) "http://up-z2.qiniup.com"
    ["response":"Qiniu\Http\Error":private]=>
    object(Qiniu\Http\Response)#172 (6) {
      ["statusCode"]=>
      int(614)
      ["headers"]=>
      array(16) {
        ["Server"]=>
        string(9) "openresty"
        ["Date"]=>
        string(19) "Mon, 04 Jan 2021 06"
        ["Content-Type"]=>
        string(16) "application/json"
        ["Content-Length"]=>
        string(2) "23"
        ["Connection"]=>
        string(10) "keep-alive"
        ["Access-Control-Allow-Headers"]=>
        string(37) "X-File-Name, X-File-Type, X-File-Size"
        ["Access-Control-Allow-Methods"]=>
        string(19) "OPTIONS, HEAD, POST"
        ["Access-Control-Allow-Origin"]=>
        string(1) "*"
        ["Access-Control-Expose-Headers"]=>
        string(14) "X-Log, X-Reqid"
        ["Access-Control-Max-Age"]=>
        string(7) "2592000"
        ["Cache-Control"]=>
        string(35) "no-store, no-cache, must-revalidate"
        ["Pragma"]=>
        string(8) "no-cache"
        ["X-Content-Type-Options"]=>
        string(7) "nosniff"
        ["X-Reqid"]=>
        string(16) "0tFAABCR7dO-81YW"
        ["X-Svr"]=>
        string(2) "UP"
        ["X-Log"]=>
        string(5) "X-Log"
      }
      ["body"]=>
      string(23) "{"error":"file exists"}"
      ["error"]=>
      string(11) "file exists"
      ["jsonData":"Qiniu\Http\Response":private]=>
      array(1) {
        ["error"]=>
        string(11) "file exists"
      }
      ["duration"]=>
      float(0.187)
    }
  }
}
```

> 上传数据的mimeType 暂时写死为 'image/png' 后序改为自行传入
---
说明：
- 修改了七牛参数配置请清除一下缓存
- upload()方法支持参数传入。可传入第一个参数为要上传文件保存的名称，第二个参数为bucket名称。
 
 第一个参数默认取文件的hash串拼接时间戳time()
 
 第二个参数默认为配置里的bucket


如果使用中有任何错误或者疑问可以给我发邮件：fzwl@fzxywl.cn

