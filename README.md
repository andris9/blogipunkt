
## Install

Muuda faili *sample.config.php* nime *config.php* vastu ja uuenda selle sisu

Lisa andmebaasitabelid (hetkel veel puudu, kuna pole lõülikud)

### Cron

Üles tuleb seada järgmised Cron tööd

  * http://example.com/pubsub/lease - kord tunnis, kontrollib PubSubHubbub *lease* aegumist    
  * http://example.com/robot/harvester - nii tihti kui võimalik, kontrollib järjekorras olevaid blogisid
  * http://example.com/robot/weblogs - iga 5 minuti tagant, kontrollib Weblogs.com ja Google Blogs uuenduste nimekirju