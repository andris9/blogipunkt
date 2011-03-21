<!DOCTYPE html>
<html>
<head>

<!--

oooo                                         .
`888                                       .o8
 888  oooo  oooo d8b  .ooooo.   .oooo.   .o888oo  .oooo.        .ooooo.   .ooooo.
 888 .8P'   `888""8P d88' `88b `P  )88b    888   `P  )88b      d88' `88b d88' `88b
 888888.     888     888ooo888  .oP"888    888    .oP"888      888ooo888 888ooo888
 888 `88b.   888     888    .o d8(  888    888 . d8(  888  .o. 888    .o 888    .o
o888o o888o d888b    `Y8bod8P' `Y888""8o   "888" `Y888""8o Y8P `Y8bod8P' `Y8bod8P'

-->

  <meta charset="utf-8" />
  <title><? echo $title?$title:"Ploginator"; ?></title>

  <!-- CSS -->
  <link href="/static/main.css" type="text/css" rel="stylesheet"/>
  <?php if($css):?>
    <? foreach($css as $file):?>
        <link href="<?php echo $file;?>" type="text/css" rel="stylesheet"/>
    <? endforeach;?>
  <?php endif; ?>

  <!-- JavaScript -->
  <script src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" type="text/javascript"></script>
  <?php if($js):?>
    <? foreach($js as $file):?>
        <script src="<?php echo $file;?>" type="text/javascript"></script>
    <? endforeach;?>
  <?php endif; ?>

</head>
<body>

<?php echo $body?$body:"Not found :/"; ?>

</body>
</html>