# BootstrapSmarty

A wrapper for Smarty to set (and maintain) defaults within a Bootstrap UI environment

## Install

Include in `composer.json`:

```
"require": {
  "battis/BootstrapSmarty": "~1.0"
}
```

## Use

If you have no templates of your own:

```
$smarty = Battis\BootstrapSmarty\BootstrapSmarty::getSmarty();

// ...app logic...

$smarty->assign('content', '<p>whatever content you want displayed</p>');
$smarty->display();
```

If you have your own templates directory:

```
$smarty->addTemplateDir('<path-to-your-templates-dir>');
```

Complete [API documentation](http://htmlpreview.github.io/?https://github.com/battis/BootstrapSmarty/blob/master/doc/index.html) is included in the repo and the [Smarty API documentation](http://www.smarty.net/docs/en/) is also online.
