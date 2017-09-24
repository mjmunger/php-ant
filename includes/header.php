<?php

/**
 * System header
 *
 * Starts the page, contains many componenents like CSS, javascript, and other
 * important resources. Also contains the following hooks: header_css_inject,
 * footer_js_inject,
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     Page Components
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */


  $performance = array();

  $header_start = microtime(true);

  require_once('application_top.php');
  $header_end = microtime(true);
  $t = $header_end -$header_start;
  array_push($performance,array('Application Top' => $t));
  $perfHeaderLogger = new logger('performance-header');
?>
<!DOCTYPE html>
<html>
<head>
  <!--CSS Style Sheets-->
  <link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"/>
  <link rel="stylesheet" type="text/css" href="/css/bootstrap-theme.min.css"/>
  <link rel="stylesheet" type="text/css" href="/css/custom.css"/>
  <link rel="stylesheet" type="text/css" href="/css/glyphicons.css">
  <!-- End CSS Style Sheets -->

  <!--Injected CSS Style Sheets-->
  <?php $PE->runActions('header_css_inject'); ?>
  <!--End Injected CSS Style Sheets-->

  <!-- Standard Javascript / jQuery -->
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="/js/modernizr.js"></script>
  <!-- End Standard Javascript / jQuery -->

  <!-- Injected Javascript / jQuery -->
  <?php $PE->runActions('header_js_inject'); ?>
  <!-- End Javascript / jQuery -->

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?php $configs = $PE->Configs->getConfigs(['siteTitle']); echo $configs['siteTitle']; ?></title>\
</head>
<body>
<!--[if lt IE 9]>
<div id="unsupportedBrowser">
  <div class="alert alert-warning text-center">It looks like you're using an old browser, which may not fully function on this site. <a href="https://bugzy.highpoweredhelp.com/docs/security/supported-browsers/">Click here to find out how to fix this for free</a>. </div>
<div class="container">
<![endif]-->
<!--[if gt IE 8]>\
<div id="unsupportedBrowser">
  <div class="alert alert-warning text-center">It looks like you're using an old browser, which may not fully function on this site. <a href="https://bugzy.highpoweredhelp.com/docs/security/supported-browsers/">Click here to find out how to fix this for free</a>. </div>
<div class="container">
<! [endif] -->
<div id="unsupportedBrowser" style="display:none">
  <div class="alert alert-warning text-center visible-md">It looks like you're using an old browser, which may not fully function on this site. <a href="http://browsehappy.com/">Click here to find out how to fix this for free</a>. </div>
</div>
<div class="container">
<script>
//Browser support check.
$(document).ready(function(e) {
  var sBrowser, sUsrAg = navigator.userAgent;
  var unsupported = false;

  if(sUsrAg.indexOf("Chrome") > -1) {
    var i = sUsrAg.indexOf("Chrome");
    i = i + 7;
    var v = sUsrAg.substr(i,2);
    if(v<30)
    {
      unsupported = false;
    }
  } else if (sUsrAg.indexOf("Safari") > -1) {
    sBrowser = "Apple Safari";
    i = sUsrAg.indexOf("Safari");
    //subtract out the first space.
    i = i -6;
    v = sUsrAg.substr(i,1);
    if(v < 6)
    {
      unsupported = true;
    }

  } else if (sUsrAg.indexOf("Opera") > -1) {
    alert(sUsrAg);
  } else if (sUsrAg.indexOf("Firefox") > -1) {
    var i = sUsrAg.indexOf("Firefox");
    i = i + 7; //Skip over "Firefox"
    i++; //Skip the /
    //Grab the next two numbers as a version
    var v = navigator.userAgent.substr(i,2);
    if(v < 25)
    {
      unsupported = true;
    }
  } else if (sUsrAg.indexOf("MSIE") > -1) {
    //Done with HTML
  }

  if(unsupported)
  {
    $("#unsupportedBrowser").slideDown(400);
  }
});

</script>

<?php


  $header_start = microtime(true);

  if($Authenticator->logged_in) {
    include('navigation.php');
  }
  $header_end = microtime(true);
  $t = $header_end - $header_start;
  array_push($performance,array('Nav' => $t));

  foreach($performance as $metric)
  {
    foreach($metric as $key => $value)
    {
      $message = sprintf("%s loaded in %s seconds",$key,strval(round($value,4)));
      $perfHeaderLogger->log($message);
    }
  }
?>
