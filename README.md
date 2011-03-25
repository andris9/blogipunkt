# Blogipass

**Blogipass** on projekt blogikataloogi loomiseks. Hetkel on olemas enamvähem valmis back-end (blogide lisamise API, postituste korjamine jne) ning lihtne demo front.

## Install

Muuda faili *sample.config.php* nime *config.php* vastu ja uuenda selle sisu

Lisa andmebaasitabelid failist *blogipass.sql*

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

### Litsents

Antud projekt on välja antud oluliste piiranguteta [MIT](blob/master/LICENSE) vabavara litsensiga.

Projekt kasutab ka mitmeid teistsuguse litsentsiga komponente:

  * [Circular Icon Set](http://prothemedesign.com/circular-icons/) ikoonid, mis on tasuta kasutamiseks
  * [SimplePie](http://simplepie.org/) RSS voogude töötleja, oluliste piiranguteta [BSD](http://www.opensource.org/licenses/bsd-license.php) vabavara litsents
  * [pubsubhubbub-php](http://code.google.com/p/pubsubhubbub-php/) PubSubHubbub klient, oluliste piiranguteta [Apache License 2.0](http://www.opensource.org/licenses/apache2.0) vabavara litsents