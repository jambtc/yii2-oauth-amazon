Amazon Authorization Login
============================
yii2 oauth Amazon Authorization Login

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist jambtc/yii2-oauth-amazon "*"
```

or add

```
"jambtc/yii2-oauth-amazon": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by :

```php
use jambtc\oauthamazon\amazon;
$checkAmazonUrl = Url::to(['amazon/check-authorization']);

$clientId = Yii::$app->params['amazon.clientId'];
$clientSecret = Yii::$app->params['amazon.clientSecret'];

$lwa = new amazon($clientId, $clientSecret);

echo $lwa->setAmazonScript();
echo $lwa->loginButton($checkAmazonUrl);


```
