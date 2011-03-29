<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="et" lang="et">
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

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="keywords" content="eesti blogid, blogid, blog">
    <title><?php echo $title?$title." &laquo; ":""; ?><?php echo SITE_TITLE; ?></title>

    <!-- CSS -->
    <link href="/static/main.css" type="text/css" rel="stylesheet"/>
    <?php if($css):?>
        <?php foreach($css as $file):?>
            <link href="<?php echo $file;?>" type="text/css" rel="stylesheet"/>
        <?php endforeach;?>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" type="text/javascript"></script>
    <script src="/static/main.js" type="text/javascript"></script>
    <?php if($js):?>
        <?php foreach($js as $file):?>
            <script src="<?php echo $file;?>" type="text/javascript"></script>
        <?php endforeach;?>
    <?php endif; ?>


</head>
<body>

    <div id="container">
        <div id="banner">
            <div id="logo">
                <a href="/"><img src="/static/blogipunkt.gif" alt="<?php echo SITE_TITLE; ?>"></a>
                <a href="/node/addBlog"><img class="lisablog" src="/static/lisa.gif"></a>
            </div>

            <div id="navcontainer">

                <ul>
                    <li><a href="#">A-D</a></li>
                    <li><a href="#">E-I</a></li>
                    <li><a href="#">J-M</a></li>
                    <li><a href="#">N-R</a></li>
                    <li><a href="#">S-Z</a></li>
                </ul>
            </div>

        </div>

        <?php echo $body?$body:"Not found :/"; ?>

        <div id="footer">
            <p>
                Au, kuulsus ja krediit: Koodi eest <a href="http://www.andrisreinman.com/">Andrisele</a> ja kujunduse eest <a href="http://zooda.tr.ee/">Zoodale</a>.
                <?php echo SITE_TITLE; ?> on avatud lähtekoodiga (<a href="https://github.com/andris9/<?php echo SITE_TITLE; ?>/blob/master/LICENSE">MIT litsents</a>) ning allalaetav <a href="https://github.com/andris9/<?php echo SITE_TITLE; ?>">siit</a>.
            </p>
        </div>
    </div>


<?php if(GOOGLE_ANALYTICS_ID):?>
    <script type="text/javascript">

        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', '<?php echo GOOGLE_ANALYTICS_ID; ?>']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();

    </script>
<?php endif; ?>

</body>
</html>