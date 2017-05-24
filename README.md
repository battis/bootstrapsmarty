# BootstrapSmarty

[![Latest Version](https://img.shields.io/packagist/v/battis/bootstrapsmarty.svg)](https://packagist.org/packages/battis/bootstrapsmarty)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/battis/bootstrapsmarty/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/battis/bootstrapsmarty/?branch=master)

A wrapper for Smarty to set (and maintain) defaults within a Bootstrap UI environment

## Install

Because this makes use of front-end files managed via Bower, as well as the back-end managed by Composer, it is _really, really, super-helpful_ to run the following command before trying to work with this package:

```BASH
composer global require "fxp/composer-asset-plugin:^1.1"
```

Find out more about [`fxp/composer-asset-plugin`](https://github.com/francoispluchino/composer-asset-plugin) and [Bower](http://bower.io/).

And then, include in `composer.json`:

```JSON
"require": {
  "battis/bootstrapsmarty": "~1.0"
}
```

## Use

If you have no templates of your own:

```PHP
use Battis\BootstrapSmarty\BootstrapSmarty;
$smarty = BootstrapSmarty::getSmarty();

// ...app logic...

$smarty->assign('content', '<p>whatever content you want displayed</p>');
$smarty->display();
```

If you have your own templates directory:

```PHP
$smarty->addTemplateDir('path/to/your/templates_dir');
```

If you have your own stylesheet:

```PHP
$smarty->addStylesheet('path/to/your/stylesheet.css');
```



Complete [API documentation](https://battis.github.io/bootstrapsmarty/namespaces/Battis.BootstrapSmarty.html) is included in the repo and the [Smarty API documentation](http://www.smarty.net/docs/en/) is also online.
