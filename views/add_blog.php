
<h1>Blogi lisamine/muutmine</h1>

<!-- STEP 1 -->

<div class="edit_blog" id="step_1_main">

    <h2>Vali blogi</h2>

    <form method="post" action="#" id="step_1_form">
        <table border="0" cellspacing="0" cellpadding="0">

            <tr>
                <td width="120">
                    <span class="title">Blogi URL*</span>
                </td>
                <td>
                    <input type="url" class="textinput wide" required id="step_1_url" placeholder="http://www.example.com" name="url" value="" />
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <input type="submit" id="step_1_button_next" name="mine" value="Edasi &raquo;" />
                </td>
            </tr>

        </table>
    </form>
    <p class="notice">* tärniga väljad on kohustuslikud</p>
</div>


<!-- STEP 2 -->

<div class="edit_blog" id="step_2_main" style="display:none">

    <h2>Kontrolli blogi andmeid</h2>

    <form method="post" action="#" id="step_2_form">
        <table border="0" cellspacing="0" cellpadding="0">

            <tr id="step_2_warning_container" style="display:none">
                <td colspan="2">
                    <div id="step_2_warning" class="warning"><strong>NB! See blogi on andmebaasis juba olemas</strong><br />Juhul kui Sa ei ole blogi omanik võid minna vastuolla blogi omaniku arvamusega blogi seadetest</div>
                </td>
            </tr>

            <tr class="bbottom">
                <td width="120">
                    <span class="title">Blogi pealkiri</span>
                </td>
                <td>
                    <div id="step_2_titlediv">
                        <div style="float: right">
                            <input id="step_2_button_edit" type="button" name="muuda" value="Muuda andmeid" />
                        </div>
                        <div id="step_2_title_text" class="bold"></div>
                        <div id="step_2_title_container" style="display:none;"><input id="step_2_title" type="text" class="textinput wide" name="url" value="" /></div>
                    </div>
                </td>
            </tr>

            <tr class="bbottom" id="step_2_description_container">
                <td>
                    <span class="title">Kirjeldus</span>
                </td>
                <td>
                    <div id="step_2_description" class="description"></div>
                </td>
            </tr>

            <tr class="bbottom">
                <td>
                    <span class="title">Blogi URL*</span>
                </td>
                <td>
                    <div id="step_2_url_text" class="url"></div>
                    <div id="step_2_url_container" style="display:none;"><input id="step_2_url" type="url" placeholder="http://www.example.com/" class="textinput wide" name="url" value="" /></div>
                </td>
            </tr>

            <tr class="bbottom">
                <td>
                    <span class="title">Blogi RSS aadress</span>
                </td>
                <td>
                    <div id="step_2_feed_text" class="url rss-icon"></div>
                    <div id="step_2_feed_container" style="display:none;" class="rss-icon"><input id="step_2_feed" type="text" placeholder="http://www.example.com/rss" class="textinput wide" name="feed" value="" /></div>
                </td>
            </tr>

            <tr class="bbottom">
                <td>
                    <span class="title">Blogi keel</span>
                </td>
                <td>

                    <select name="lang" id="step_2_lang">
                        <option value="et" style="font-weight: bold">eesti</option>
                        <?php
                         $langs = json_decode('{"af":"afrikaani","sq":"albaania","ar":"araabia","hy":"armeenia","az":"aserbaid\u017eaani","eu":"baski","bg":"bulgaaria","et":"eesti","gl":"galiitsia","ka":"gruusia","ht":"Haiti-kreool","iw":"heebrea","zh-CN":"Hiina","hi":"hindi","es":"hispaania","nl":"hollandi","hr":"horvaadi","ga":"iiri","id":"indoneesia","en":"inglise","is":"islandi","it":"itaalia","ja":"jaapani","yi":"jidi\u0161i","ca":"katalaani","ko":"korea","el":"kreeka","la":"ladina","lt":"leedu","lv":"l\u00e4ti","mk":"makedoonia","ms":"malai","mt":"malta","no":"norra","tl":"pilipino","pl":"poola","pt":"portugali","fr":"prantsuse","fa":"p\u00e4rsia","sv":"rootsi","ro":"rumeenia","de":"saksa","sr":"serbia","sk":"slovaki","sl":"sloveeni","fi":"soome","sw":"suahiili","da":"taani","th":"tai","cs":"t\u0161ehhi","tr":"t\u00fcrgi","cy":"uelsi","uk":"ukraina","hu":"ungari","ur":"urdi","be":"valgevene","ru":"vene","vi":"vietnami"}', true);

                         foreach($langs as $key=>$name):
                        ?>
                        <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                        <?php endforeach;?>
                    </select>

                    <div style="margin-top: 5px; font-size: 11px; vertical-align:middle;">keele tuvastas <img src="http://www.google.com/uds/css/small-logo.png" style="vertical-align:middle;"/></div>

                </td>
            </tr>

            <tr>
                <td valign="top">
                    <span class="title">Kategooriad*<br />(kuni <?php echo BLOG_MAX_CATEGORIES; ?>)</span>
                </td>
                <td>
                    <ul class="category-list">
                    <?php foreach(Blog::getCategories() as $id=>$category):?>

                    <li><label><input type="checkbox" class="category-select-cb" name="categories[]" value="<?php echo $id;?>"/> <?php echo htmlspecialchars($category["name"]);?></label></li>

                    <?php endforeach;?>
                    </ul>
                </td>
            </tr>


            <tr>
                <td colspan="2">
                    <input id="step_2_button_back" type="button" name="tagasi" value="&laquo; Tagasi" />
                    <input id="step_2_button_next" type="submit" name="mine" value="Salvesta andmed" />
                </td>
            </tr>

        </table>
    </form>
    <p class="notice">* tärniga väljad on kohustuslikud</p>
</div>

<!-- STEP 3 -->
<div class="edit_blog" id="step_3_main" style="display:none">

    <h2>Andmed on salvestatud!</h2>

    <table border="0" cellspacing="0" cellpadding="0">

        <tr id="step_3_warning_container" style="display:none">
            <td colspan="3">
                <div id="step_3_warning" class="warning"><strong>NB! Muutsid olemasoleva blogi andmeid</strong></div>
            </td>
        </tr>

        <tr class="bbottom">
            <td width="120">
                <span class="title">Blogi pealkiri</span>
            </td>
            <td>
                <div id="step_3_title" class="bold"></div>
            </td>
        </tr>

        <tr class="bbottom" id="step_3_description_container">
            <td>
                <span class="title">Kirjeldus</span>
            </td>
            <td>
                <div id="step_3_description" class="description"></div>
            </td>
        </tr>

        <tr class="bbottom">
            <td>
                <span class="title">Blogi URL</span>
            </td>
            <td>
                <div id="step_3_url" class="url"></div>
            </td>
        </tr>

        <tr class="bbottom">
            <td>
                <span class="title">Blogi RSS aadress</span>
            </td>
            <td>
                <div id="step_3_feed" class="url rss-icon"></div>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <span class="title">Kategooriad</span>
            </td>
            <td>
                <div id="step_3_categories" class="category-list"></div>
            </td>
        </tr>
    </table>

</div>


<script>
AddForm.MAX_CATEGORIES = <?php echo BLOG_MAX_CATEGORIES; ?>;
AddForm.categories = <?php echo json_encode(Blog::getCategories());?>;
</script>
