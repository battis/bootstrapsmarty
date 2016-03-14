# BootstrapSmarty

A wrapper for Smarty to set (and maintain) defaults within a Bootstrap UI environment

## Install

Include in `composer.json`:

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



Complete [API documentation](https://htmlpreview.github.io/?https://raw.githubusercontent.com/battis/BootstrapSmarty/master/doc/namespaces/Battis.BootstrapSmarty.html) is included in the repo and the [Smarty API documentation](http://www.smarty.net/docs/en/) is also online.
