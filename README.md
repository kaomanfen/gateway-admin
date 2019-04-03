## 网关管理系统API项目

### **简介**
本项目是网关管理后台的API项目，基于 Lavravel 5.5 开发。

### **安装**
使用git命令克隆代码到本地：

`https://github.com/kaomanfen/gateway-admin.git`

创建一个新的数据库，拷贝 `.env.example` 到 `.env`，修改相应配置。


进入项目目录运行如下命令：
```
composer install
php artisan key:generate
php artisan jwt:secret
```