#Murrframework

MVC Framework I started working on during my studies written in PHP.

####INSTALLATION

run install script:

```
php ./install/index.php
```

####FILE STRUCTURE:
```
/
|-- install
|   `-- install scripts
|
|--framework
|  |
|  |-- framework specific classes
|  |
|  |-- mvcbase
|  |   |-- base model class
|  |   |-- base controller class
|  |   |-- base view class
|  |   `-- base viewhelper class
|  |
|  |-- predef
|  |   |-- config
|  |   |--application
|  |   |  |-- models
|  |   |  |-- controllers
|  |   |  |-- views
|  |   |  `-- viewhelper
|  |   `-- templates
|  |
|  |-- lib
|  |
|  `-- drivers
|
`--application
   |
   |-- config
   |   |-- config file
   |   `-- table definition file
   |
   |-- libs
   |
   |-- models
   |-- controllers
   |-- views
   |-- viewhelpers
   |
   |-- templates
   `-- uploads
```
