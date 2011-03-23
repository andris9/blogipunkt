<h1>Viimased postitused</h1>


<div class="posts">
    <?php foreach(Post::getList(0, 10) as $post):?>
    <div class="post">
        <div class="post-title">
            <a href="<?php echo htmlspecialchars($post["url"]);?>" class="out id:<?php echo $post["id"];?>"><?php echo htmlspecialchars($post["title"]);?></a><br />
            <span class="post-info">
                <?php echo strftime("%A, %e %b %y %H:%M", strtotime($post["date"]));?>
                blogis
                <a href="<?php echo htmlspecialchars($post["blogurl"]);?>"><?php echo htmlspecialchars($post["blogtitle"]);?></a>
            </span>
        </div>
        <div class="post-preview">
            <?php
                $contents = $post["snippet"]?$post["snippet"]:mb_substr(strip_tags($post["contents"]),0,200);
                echo htmlspecialchars($contents);
            ?>
        </div>


    </div>

    <?php endforeach; ?>
</div>