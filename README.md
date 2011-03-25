# Blogipass

**Blogipass** on projekt blogikataloogi loomiseks. Hetkel on olemas vaid back-end (blogide lisamise API, postituste korjamine jne) ning lihtne demo front.

## Install

Muuda faili *sample.config.php* nime *config.php* vastu ja uuenda selle sisu

Lisa andmebaasitabelid failist *blogipass.sql*

### Cron

Üles tuleb seada järgmised Cron tööd

  * http://example.com/pubsub/lease - kord tunnis, kontrollib PubSubHubbub *lease* aegumist    
  * http://example.com/robot/harvester - nii tihti kui võimalik, kontrollib järjekorras olevaid blogisid
  * http://example.com/robot/weblogs - iga 5 minuti tagant, kontrollib Weblogs.com ja Google Blogs uuenduste nimekirju
  
**NB!** kui *weblogs* cron on sees, laetakse iga 5 minuti tagant mitu megabaiti andmeid, juhul kui konto
andmemaht on piiratud ja kataloogis on vaid moodsamad blogid, võib selle väja lülitada.
  
### Nõuded

  * **PHP5**
  * **MySQL5** (vanema puhul tuleb muuta config.php failis mysql charseti seadmist)
  * **curl**
  * **DOM** moodul (PHP)

Näiteks [Zone.ee](http://www.zone.ee) ja [Veebimajutus.ee](http://www.veebimajutus.ee) virtuaalserverites on tingimused täidetud out of the box. 