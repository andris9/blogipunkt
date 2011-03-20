# Blogipass

**Blogipass** on projekt blogikataloogi loomiseks. Hetkel on olemas vaid back-end (blogide lisamise API, postituste korjamine jne)

## Install

Muuda faili *sample.config.php* nime *config.php* vastu ja uuenda selle sisu

Lisa andmebaasitabelid failist *blogipass.sql*

### Cron

Üles tuleb seada järgmised Cron tööd

  * http://example.com/pubsub/lease - kord tunnis, kontrollib PubSubHubbub *lease* aegumist    
  * http://example.com/robot/harvester - nii tihti kui võimalik, kontrollib järjekorras olevaid blogisid
  * http://example.com/robot/weblogs - iga 5 minuti tagant, kontrollib Weblogs.com ja Google Blogs uuenduste nimekirju