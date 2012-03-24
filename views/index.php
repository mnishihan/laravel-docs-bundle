<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $title ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <?php echo Asset::container('header1')->styles(); ?>
        <?php echo Asset::container('header2')->styles(); ?>

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <?php echo Asset::container('header2')->scripts() ?>

        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="<?php echo asset('bundles/docs/ico/favicon.png') ?>">
    </head>

    <body>
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner" id="navbar">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="<?php echo url('docs'); ?>">Doc Home</a>
                    <div class="nav-collapse">
                        <ul class="nav" id="nav">
                            <li><a href="http://laravel.com">Laravel Site</a></li>
                            <li><a href="http://bundles.laravel.com">Bundles</a></li>
                        </ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>
        <div class="container" data-spy="scroll" data-target=".subnav" data-offset="0">
            <div class="row">
                <div class="span3" id="toc">
                    <?php echo $toc; ?>
                </div>
                <div class="span9" id="content">
                    <?php echo $doc; ?>
                </div>
            </div>
        </div> <!-- /container -->

        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <?php echo Asset::container('footer1')->scripts(); ?>
    </body>
</html>
