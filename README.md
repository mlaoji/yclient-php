# YClient with PHP for YGO

php 版的YClient, 用于访问YGO提供的grpc服务

## Installation
-------
 - Install the gRPC PHP extension

首先需要安装PHP grpc 扩展

   ```sh
   $ [sudo] pecl install grpc
   ```
   使用pecl安装或你擅长的方式安装

其次安装PHP protobuf 扩展（可选, vender下已包含Php版代码）

   ```sh
   $ [sudo] pecl install protobuf
   ```
   使用pecl安装或你擅长的方式安装

TRY IT!
-------

 - Run YGO server

启动一个由YGO创建的RPC服务

 - Edit example.php and run it!

编辑并执行example.php
