<?php if ( ! Simplechart::instance()->post_type->current_user_can() ) {
	die( esc_html__( 'Insufficient user capability', 'simplechart' ) );
} ?>

<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simplechart</title>
    <link rel="icon" type="image/png" href="<?php echo esc_url( Simplechart::instance()->get_plugin_url() . 'app/assets/images/simplechartIcon.png' );?>">
    <base href="<?php echo esc_url( Simplechart::instance()->get_plugin_url() . 'app/' ); ?>"></base>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular-animate.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular-resource.min.js"></script>
    <script src="<?php echo esc_url( Simplechart::instance()->get_plugin_url() . 'app/main.bundle.js' ); ?>"></script>
</head>

<body>
	<!--[if lt IE 10]>
			<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->
	<nav class="navbar navbar-default navbar-simplechart navbar-fixed-top">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#simplechart-nav-main">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a ui-sref="/" class="navbar-brand"><img width="150" src="<?php echo esc_url( Simplechart::instance()->get_plugin_url() . 'app/assets/images/simplechartLogo.svg' );?>" /></a>
		</div>

		<div id="simplechart-nav-main" class="collapse navbar-collapse" ng-controller="Nav">
			<ul class="nav navbar-nav navbar-right">
				<li ui-sref-active="active"><a ui-sref="simplechart">Build</a></li>
				<li ui-sref-active="active"><a ui-sref="tutorials">Tutorials</a></li>
				<li ui-sref-active="active"><a ui-sref="faq">FAQ</a></li>
				<li ui-sref-active="active"><a ui-sref="about">About</a></li>
				<li class="alley-logo hidden-xs">
					<a href="http://alleyinteractive.com" target="_blank" title="Alley Interactive">
						<img src="<?php echo esc_url( Simplechart::instance()->get_plugin_url() . 'app/assets/images/alleyLogo.png' );?>" />
					</a>
				</li>
			</ul>
		</div>
	</nav>

	<div class="main-content-container">
		<div class="container-fluid" ui-view></div>
	</div>
</body>

</html>
<?php die(); ?>
