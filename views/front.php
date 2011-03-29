

        <div id="sidebar-a">

            <h3>Viimased 10 sissekannet Eesti blogides:</h3>
            <font style="padding-left: 10px">
                <a href="rss.php"><img src="/static/rss.gif" alt="RSS" title="RSS"></a>
                <a href="/" style="text-decoration: underline;">Üldised</a><br />
                <br />
            </font>
            <div id="list">
                <ul>

                    <?php foreach(Post::getList(0, 10, "et") as $post):
                          $contents = $post["snippet"]?$post["snippet"]:mb_substr(strip_tags($post["contents"]),0,200);
                    ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($post["url"]);?>" class="out id:<?php echo $post["id"];?>" title="<?php echo htmlspecialchars($contents);?>">
                            <?php echo htmlspecialchars($post["title"]);?>
                        </a><br />
                        kirjutab: <a href="<?php echo htmlspecialchars($post["blogurl"]);?>"><?php echo htmlspecialchars($post["blogtitle"]);?></a>
                        <?php echo strftime("%H:%M", strtotime($post["date"]));?>
                    </li>

                    <?php endforeach; ?>
                </ul>
            </div>

        </div>

        <div id="content">

            <h3>
                Valik juhuslikke blogisid<br/>
                <small>täieliku nimekirja vaatamiseks vali ülalt sobiv tähevahemik</small>
            </h3>

            <div id="list2">
                <ul>
                    <?php foreach(Blog::getRandomList(20, "et") as $blog):?>
                    <li>
                        <a href="<?php echo $blog["feed"]; ?>"><img src="/static/rss.gif" alt="RSS" title="RSS"></a>
                        <span class="hover"><a href="<?php echo $blog["url"]; ?>"><?php echo htmlspecialchars($blog["title"]); ?></a></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>
