
/**
 * KREATA
 * 
 * Peamine globaalne objekt
 **/
var KREATA = {

    /**
     * KREATA.checkTracker(event) -> undefined
     * - event (Event): click sündmus
     * 
     * Funktsioon rakendub iga klikiga. Kui klikiti A tüüpi elemendil, millel
     * on CSS klassi nimeks "out", käivitatakse kliki loendamine
     **/
    checkTracker: function(event){
        var element = Event.element(event);
        if(element.tagName=="A" && element.hasClassName("out")){
            this.track(element);
        }
    },

    /**
     * KREATA.track(element) -> undefined
     * - element (Element): lingi DOM element
     * 
     * Funktsioon otsib üles CSS klassi nimest ID väärtuse, näiteks
     *     <a class="... ID:345 ..."> -> id=345
     * ja tellib selle ID loendamise
     **/
    track: function(element){
        var idval = element.className.match(/id:(\d+)/),
            id = idval && idval[1] || false,
            url;
        if(id){
            url = "/ajax/post/upvote?id="+id+"&t="+(+new Date());
            (new Image()).src = url;
        }
    }
    
}

// sea hiirekliki sündmuse haldaja
$(document).observe("mousedown", KREATA.checkTracker.bindAsEventListener(KREATA));