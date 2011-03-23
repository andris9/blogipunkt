
var MAIN = {
    checkTracker: function(event){
        var element = Event.element(event);
        if(element.tagName=="A" && element.hasClassName("out")){
            this.track(element);
        }
    },

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


$(document).observe("mousedown", MAIN.checkTracker.bindAsEventListener(MAIN));