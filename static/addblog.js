
KREATA.AddForm = {

    data: {},

    domCache: {},

    checkedCategories: 0,

    initPage: function(){

        // cache dom elements
        this.domCache.step1 = {
            main: $("step_1_main"),
            form: $("step_1_form"),
            url: $("step_1_url"),
            next: $("step_1_button_next")
        }

        this.domCache.step2 = {
            form: $("step_2_form"),
            main: $("step_2_main"),
            category_checkboxes: $$(".category-select-cb"),
            warning_container: $("step_2_warning_container"),
            warning: $("step_2_warning"),

            title_text: $("step_2_title_text"),
            title: $("step_2_title"),
            title_container: $("step_2_title_container"),
            titlediv: $("step_2_titlediv"),

            description: $("step_2_description"),
            description_container: $("step_2_description_container"),
            url_text: $("step_2_url_text"),
            url: $("step_2_url"),
            url_container: $("step_2_url_container"),
            feed_text: $("step_2_feed_text"),
            feed: $("step_2_feed"),
            feed_container: $("step_2_feed_container"),
            
            lang: $("step_2_lang"),
            
            edit: $("step_2_button_edit"),
            back: $("step_2_button_back"),
            next: $("step_2_button_next")
        }

        this.domCache.step3 = {
            main: $("step_3_main"),
            warning_container: $("step_3_warning_container"),
            warning: $("step_3_warning"),
            title: $("step_3_title"),
            description: $("step_3_description"),
            description_container: $("step_3_description_container"),
            url: $("step_3_url"),
            feed: $("step_3_feed"),
            categories: $("step_3_categories")
        }

        this.setupStep1();
        this.setupStep2();

        this.domCache.step1.url.focus();
        this.domCache.step1.url.select();
    },

    setupStep1: function(){
        this.domCache.step1.form.observe("submit", this.submitStep1.bindAsEventListener(this));

        this.domCache.step1.url.observe("click", this.checkStep1Button.bindAsEventListener(this));
        this.domCache.step1.url.observe("keyup", this.checkStep1Button.bindAsEventListener(this));
        this.domCache.step1.url.observe("change", this.checkStep1Button.bindAsEventListener(this));
        this.checkStep1Button();
    },

    setupStep2: function(){
        this.setupCategoryCheckboxes();

        this.domCache.step2.form.observe("submit", this.submitStep2.bindAsEventListener(this));

        this.domCache.step2.url.observe("click", this.checkStep2Button.bindAsEventListener(this));
        this.domCache.step2.url.observe("keyup", this.checkStep2Button.bindAsEventListener(this));
        this.domCache.step2.url.observe("change", this.checkStep2Button.bindAsEventListener(this));

        this.domCache.step2.edit.observe("click", this.editStep2.bindAsEventListener(this));

        this.domCache.step2.back.observe("click", (function(event){
           this.showStep(1);
        }).bindAsEventListener(this));
    },

    setupStep3: function(){
    },

    showError: function(message){
        alert(message);
    },

    showStep: function(nr){
        for(var i=1; i<=3; i++){
            if(this.domCache["step"+i] && this.domCache["step"+i].main){
                if(nr!=i){
                    this.domCache["step"+i].main.hide();
                }else{
                    this.domCache["step"+i].main.show();
                }
            }
        }
    },

    submitStep1: function(event){
        event.stop(); // prevent default
        if(!this.validateStep1())return;

        this.data.step1 = {
            url: this.domCache.step1.url.value.strip()
        }

        if(!this.data.step1.url.match(/^https?:\/\//)){
            this.data.step1.url = "http://"+this.data.step1.url;
        }
        this.runStep1();
    },

    runStep1: function(){
        this.waitStep1();
        new Ajax.Request(KREATA.BaseDir+"ajax/blog/check",{
            method:"post",
            parameters: {
                url: this.data.step1.url
            }, onComplete: (function(response){
                var data = {status:"error", message:"Viga serveriga ühendumisel"};
                try{
                    data = response.responseText.evalJSON();
                }catch(E){};
                if(data.status=="error"){
                    this.showError(data.message);
                    this.readyStep1();
                    return;
                }
                this.data.step2 = data.data;
                this.fillStep2Data();
                this.showStep(2);
                this.readyStep1();

            }).bind(this)
        });
    },

    waitStep1: function(){
        this.domCache.step1.url.disabled = true;
        this.domCache.step1.next.disabled = true;
        this.domCache.step1.url.addClassName("wait");
    },

    readyStep1: function(){
        this.domCache.step1.url.disabled = false;
        this.domCache.step1.url.removeClassName("wait");
        this.validateStep1();
    },

    checkStep1Button: function(event){
        this.validateStep1();
    },

    validateStep1: function(){
        if(!this.domCache.step1.url.value ||
          this.domCache.step1.url.value=="http://"){
            this.domCache.step1.next.disabled = true;
            return false;
        }else{
            this.domCache.step1.next.disabled = false;
            return true;
        }
    },

    fillStep2Data: function(){
        // warning
        if(this.data.step2.exists){
            this.domCache.step2.warning_container.show();
        }else{
            this.domCache.step2.warning_container.hide();
        }

        // title
        this.domCache.step2.title_text.innerHTML = this.data.step2.title || "-pealkiri puudub-";
        this.domCache.step2.title.value = this.data.step2.title || "";
        this.domCache.step2.title_text.show();
        this.domCache.step2.title_container.hide();

        // description
        if(this.data.step2.description){
            this.domCache.step2.description_container.show();
            this.domCache.step2.description.innerHTML = this.data.step2.description;
        }else{
            this.domCache.step2.description_container.hide();
        }

        // url
        this.domCache.step2.url_text.show();
        var url_title = this.data.step2.url && this.data.step2.url.replace(/^https?:\/\//,''),
            url_elm = new Element("a",{href:this.data.step2.url, target:"_blank"}).update(url_title);
        this.domCache.step2.url_text.innerHTML = "";
        this.domCache.step2.url_text.appendChild(url_elm);
        this.domCache.step2.url_container.hide();
        this.domCache.step2.url.value = this.data.step2.url;
        this.domCache.step2.url_text.show();
        this.domCache.step2.url_container.hide();

        // feed
        this.domCache.step2.feed_text.show();
        var feed_title = this.data.step2.feed && this.data.step2.feed.replace(/^https?:\/\//,'') || "",
            feed_elm = new Element("a",{href:this.data.step2.feed, target:"_blank"}).update(feed_title);
        this.domCache.step2.feed_text.innerHTML = "";
        this.domCache.step2.feed_text.appendChild(feed_elm);
        this.domCache.step2.feed_container.hide();
        this.domCache.step2.feed.value = this.data.step2.feed || "";
        this.domCache.step2.feed_text.show();
        this.domCache.step2.feed_container.hide();

        // lang
        $A(this.domCache.step2.lang.options).each((function(option){
            if(option.value == this.data.step2.lang){
                option.selected = true;
                throw $break;
            }
        }).bind(this));
    
        // categories
        this.domCache.step2.category_checkboxes.each((function(cb){
            if(this.data.step2.categories.indexOf(String(cb.value))<0){
                cb.checked = false;
            }else{
                cb.checked = true;
            }
        }).bind(this));
        this.checkCategories();

        // buttons
        this.domCache.step2.edit.disabled = false;

        this.validateStep2();
    },

    editStep2: function(event){
        this.domCache.step2.edit.disabled = true;

        this.domCache.step2.title_text.hide();
        this.domCache.step2.title_container.show();

        this.domCache.step2.url_text.hide();
        this.domCache.step2.url_container.show();

        this.domCache.step2.feed_text.hide();
        this.domCache.step2.feed_container.show();

        this.domCache.step2.url.focus();
    },

    checkStep2Button: function(event){
        this.validateStep2();
    },

    validateStep2: function(){
        var status = true;

        if(!this.checkedCategories){
            status = false;
        }

        if(!this.domCache.step2.url.value ||
          this.domCache.step2.url.value=="http://"){
            status = false;
        }

        if(!status){
            this.domCache.step2.next.disabled = true;
        }else{
            this.domCache.step2.next.disabled = false;
        }

        return status;
    },

    submitStep2: function(event){
        event.stop(); // prevent default
        if(!this.validateStep2())return;

        this.data.step2.title = this.domCache.step2.title.value.strip();
        this.data.step2.url = this.domCache.step2.url.value.strip();
        this.data.step2.feed = this.domCache.step2.feed.value.strip();
        this.data.step2.lang = this.domCache.step2.lang.value.strip();

        if(!this.data.step2.url.match(/^https?:\/\//)){
            this.data.step2.url = "http://"+this.data.step2.url;
        }

        if(this.data.step2.feed && !this.data.step2.feed.match(/^https?:\/\//)){
            this.data.step2.feed = "http://"+this.data.step2.feed;
        }

        this.data.step2.categories = [];
        this.domCache.step2.category_checkboxes.each((function(cb){
            if(cb.checked){
                this.data.step2.categories.push(Number(cb.value));
            }
        }).bind(this));
        this.data.step2.categories = this.data.step2.categories.sort();

        this.runStep2();
    },

    runStep2: function(){
        this.waitStep2();

        var params = [
            "title="+encodeURIComponent(this.data.step2.title),
            "url="+encodeURIComponent(this.data.step2.url),
            "feed="+encodeURIComponent(this.data.step2.feed),
            "lang="+encodeURIComponent(this.data.step2.lang)
        ];

        this.data.step2.categories.each(function(cat){
            params.push("categories[]="+encodeURIComponent(cat))
        });

        new Ajax.Request(KREATA.BaseDir+"ajax/blog/add",{
            method:"post",
            postBody: params.join("&"),
            onComplete: (function(response){
                var data = {status:"error", message:"Viga serveriga ühendumisel"};
                try{
                    data = response.responseText.evalJSON();
                }catch(E){};
                if(data.status=="error"){
                    this.showError(data.message);
                    this.readyStep2();
                    return;
                }

                this.data.step3 = data.data;
                this.data.step3.previouslyAdded = data.status=="exists";

                this.fillStep3Data();
                this.showStep(3);
                this.readyStep2();

            }).bind(this)
        });
    },

    setupCategoryCheckboxes: function(){
        // categories
        this.domCache.step2.category_checkboxes.each((function(cb){
            cb.observe("click",this.checkCategories.bindAsEventListener(this));
            cb.observe("change",this.checkCategories.bindAsEventListener(this));
        }).bind(this));

        this.checkCategories();
    },

    checkCategories: function(event){

        // loe kokku valitud teemade arv
        this.checkedCategories = 0;
        // check already checked
        this.domCache.step2.category_checkboxes.each((function(cb){
            if(cb.checked){
                this.checkedCategories++;
            }
        }).bind(this));

        // juhul kui valitud on maksimum, peida ülejäänud
        this.domCache.step2.category_checkboxes.each((function(cb){
            if(!cb.checked){
                if(this.checkedCategories >= this.MAX_CATEGORIES){
                    cb.disabled = true;
                }else{
                    cb.disabled = false;
                }
            }else{
                cb.disabled = false;
            }
        }).bind(this));

        this.validateStep2();
    },

    waitStep2: function(){
        this.domCache.step2.next.disabled = true;
        this.domCache.step2.back.disabled = true;
        this.domCache.step2.edit.disabled = true;
        this.domCache.step2.titlediv.addClassName("wait");
    },

    readyStep2: function(){
        this.domCache.step2.next.disabled = false;
        this.domCache.step2.back.disabled = false;
        this.domCache.step2.edit.disabled = false;
        this.domCache.step2.titlediv.removeClassName("wait");
        this.validateStep2();
    },

    fillStep3Data: function(){
        // warning
        if(this.data.step3.previouslyAdded){
            this.domCache.step3.warning_container.show();
        }else{
            this.domCache.step3.warning_container.hide();
        }

        // title
        this.domCache.step3.title.innerHTML = this.data.step3.title || "-pealkiri puudub-";

        // description
        if(this.data.step3.description){
            this.domCache.step3.description_container.show();
            this.domCache.step3.description.innerHTML = this.data.step3.description;
        }else{
            this.domCache.step3.description_container.hide();
        }

        // url
        this.domCache.step3.url.show();
        var url_title = this.data.step3.url && this.data.step3.url.replace(/^https?:\/\//,''),
            url_elm = new Element("a",{href:this.data.step3.url, target:"_blank"}).update(url_title);
        this.domCache.step3.url.innerHTML = "";
        this.domCache.step3.url.appendChild(url_elm);

        // feed
        this.domCache.step3.feed.show();
        var feed_title = this.data.step3.feed && this.data.step3.feed.replace(/^https?:\/\//,'') || "",
            feed_elm = new Element("a",{href:this.data.step3.feed, target:"_blank"}).update(feed_title);
        this.domCache.step3.feed.innerHTML = "";
        this.domCache.step3.feed.appendChild(feed_elm);

        // categories
        this.domCache.step3.categories.innerHTML = "";
        this.data.step3.categories.each((function(cat){
            var cat_elm;
            if(this.categories[cat]){
                cat_elm = new Element("div").update(this.categories[cat].name);
                this.domCache.step3.categories.appendChild(cat_elm);
            }

        }).bind(this));
    }

}

$(document).observe("dom:loaded",KREATA.AddForm.initPage.bind(KREATA.AddForm));