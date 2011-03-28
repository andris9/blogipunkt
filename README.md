# Blogipunkt

**Blogipunkt** on projekt blogikataloogi loomiseks. Hetkel on olemas enamvähem valmis back-end (blogide lisamise API, postituste korjamine jne) ning lihtne demo front.

Blogipunkt kasutab **PubSubHubbub** tehnoloogiat, mis tähendab, et toetatud blogidest (peaaegu kõik) jõuavad uued postitused lehele **koheselt**!

## Demo

Demosait asub aadressil [pang.digituvastus.org](http://pang.digituvastus.org) kuid see ei pruugi alati parasjagu töötada.

## Tahad kaasa lüüa?

Igaüks võib Blogipunkti arendamises kaasa lüüa. Bugfixe jms. väiksemat saab teha omal algatusel (tee repost *fork*, paranda viga ja saada mulle *pull request*), suuremate plaanide korral võta ühendust [andris@kreata.ee](mailto:andris@kreata.ee). Arvestada tuleb vaid, et kõik mida teed, läheb MIT litsentsi alla.

## Install

  * Lae Blogipunkti failid alla [siit](https://github.com/andris9/blogipunkt/zipball/master)
  * Kopeeri Blogipunkti failid veebiserveri juurkataloogi
  * Muuda faili *sample.config.php* nime *config.php* vastu ja uuenda selle sisu
  * Lisa andmebaasitabelid failist *blogipunkt.sql*
  * Lisa mõni kirje andmebaasitabelisse *categories* (näiteks phpMyAdmin abil)
  * Ava oma uus Blogipunkt veebilehitsejas ja kliki lingil "Lisa blogi"
  * Lisa uus blogi. Kui nüüd esileht avada, peaks seal olema blogi viimased postitused
  * Sea üles CRON (vaata järgmist punkti), et uueneks PubSubHubbub *lease* ning vanemat tüüpi blogide (ilma PubSubHubbubtoetuseta) postitusi kontrollitaks Weblogs.com listist

### Cron

Üles tuleb seada järgmised Cron tööd

  * http://example.com/pubsub/lease - kord tunnis, kontrollib PubSubHubbub *lease* aegumist    
  * http://example.com/robot/harvester - nii tihti kui võimalik, kontrollib järjekorras olevaid blogisid
  * http://example.com/robot/weblogs - iga 5 minuti tagant, kontrollib Weblogs.com ja Google Blogs uuenduste nimekirju
  
**NB!** kui *weblogs* cron on sees, laetakse iga 5 minuti tagant mitu megabaiti andmeid. Juhul kui konto
andmemaht on piiratud ja kataloogis on vaid moodsamad blogid, võib selle väja lülitada.
  
### Nõuded

  * **PHP5**
  * **MySQL5** (vanema puhul tuleb muuta config.php failis mysql charseti seadmist)
  * **curl**
  * **DOM moodul** ([PHP](http://www.php.net/manual/en/book.dom.php))

Näiteks [Zone.ee](http://www.zone.ee) ja [Veebimajutus.ee](http://www.veebimajutus.ee) virtuaalserverites on tingimused täidetud out of the box.


## Katloogide struktuur

  * **ajax** - siin asuvad Ajax päringute haldajad
  * **bot** - erinevad Cron skriptid ja muud autonoomsed asjad (PubSubHubbub klient jne)
  * **includes** - sisemised API'd
    * **vendor** - kolmanda osapoole skriptid (n. SimplePie)
  * **static** - staatilised failid (JS, CSS, pildid)
  * **views** - lehtede templiidid, *main.php* on põhikonteiner ja muud käivad selle sisse $body väärtusena

## Litsents

Antud projekt on välja antud oluliste piiranguteta [MIT](/andris9/blogipunkt/blob/master/LICENSE) vabavara litsensiga.

Projekt kasutab ka mitmeid teistsuguse litsentsiga komponente:

  * [Circular Icon Set](http://prothemedesign.com/circular-icons/) ikoonid, mis on tasuta kasutamiseks
  * [SimplePie](http://simplepie.org/) RSS voogude töötleja, oluliste piiranguteta [BSD](http://www.opensource.org/licenses/bsd-license.php) vabavara litsents
  * [pubsubhubbub-php](http://code.google.com/p/pubsubhubbub-php/) PubSubHubbub klient, oluliste piiranguteta [Apache License 2.0](http://www.opensource.org/licenses/apache2.0) vabavara litsents
  
## Lisainfo

### Postituste korjamine blogidest

Üldiselt saab postitused kätte RSS või Atom voost, kui blogi seda võimaldab (reeglina võimaldab). Tegu on XML formaadis andmete edastamise
protokolliga ning PHP keele jaoks saab selle formaadiga kõige paremini hakkama [SimplePie](http://www.simplepie.org/) nimeline teek.

Kuna blogisid on palju ning pole ka viisakas servereid liialt RSS faili kontrollimisega koormata, tuleb kasutada ka teisi teid,
kui lihtlabane perioodiline RSS faili allalaadimine serverist. Üldiselt on võimalik kasutada kolme eri viisi:

  * **RSS faili perioodiline kontrollimine**. Siin on võimalik saavutada mõningane optimeerimine, kui kasutada *If-Modified-Since*
    parameetrit (juhul kui fail ei ole serveris muutunud, tagastab server *304 Not Modified* vastuse)
  * **Ping listide jälgimisega**. Enamus blogimootoreid *ping*ib uue postituse järel suuri *ping* saite nagu näiteks [Weblogs.com](http://www.weblogs.com).
    Need saidid omakorda koostavad viimati muutunud blogide nimekirju. Nii saab perioodiliselt laadida mõni neist nimekirjadest alla
    ja otsida sellest oma baasis olevaid blogisid - juhul kui mõni neist on nimekirjas olemas, tasub minna sellest konkreetset blogist
    uusi postitusi otsima. 
  * **[PubSubHubbub](http://code.google.com/p/pubsubhubbub/)**. Tegu on uusima ja parima protokolliga, mis töötab *push* põhimõttel ja praktiliselt reaalajas.
    Kliendid saavad blogi poolt määratud serverile (nn. *hub*) teada anda, et on selle blogi postitustest huvitatud ning server
    hakkab edaspidi koheselt peale postituse avaldamist nendest huvilistele teada andma. Momendil toetavad PubSubHubbub protokolli
    kõik livejournal.com, wordpress.com ja blogspot.com platvormidel olevad blogid, samuti ka paljud *self-hosted* WordPressi blogid,
    mis sisaldavad [PuSHPress pluginat](http://wordpress.org/extend/plugins/pushpress/).
    
### Kommentaarid

Moodsamad blogimootorid (Blogspot, WordPress) annavad iga postituse juurde ka selle postitusega seotud kommentaaride RSS/Atom voo.
Selle väärtuse leiab blogi RSS/Atom voost postituse andmete juurest. Blogspot kasutab Atom formaati ning seal tuleb kommentaaride
voo aadressi otsida elemendist *link* (elemendi nimeruumiks on standardne *"http://www.w3.org/2005/Atom"*).

    <link rel="replies" type="application/atom+xml" href="feed_url"/>

WordPress kasutab RSS formaati ning kuna selles vaikimisi kommentaaride element puudub, kasutatakse selle jaoks nimeruumi *"http://wellformedweb.org/CommentAPI/"* elementi *commentRss*

    <wfw:commentRss>feed_url</wfw:commentRss>

Lisaks on kasutusel veel mitmed kolmanda osapoole kommentaaride platvorme, mille saab blogiomanik oma blogiga integreerida. Näiteks <a href="http://disqus.com">disqus</a> või <a href="http://www.aboutecho.com/commenting">haloscan/js-kit</a>, mis suudavad samuti kommentaaridest RSS voo välja anda. Probleemiks on aga, et seda RSS aadressi
ei ole kõige lihtsam tuvastada, kuna selle genereerib vastav JavaScripti teek ja blogi lehele lisatakse link alles peale postituse kuvamist. 

**NB!** Kommentaari RSS/Atom voo faili puhul *If-Modified-Since* ei tööta, kuna reeglina kasutab server selleks väärtuseks viimasena salvestatud postituse aega!