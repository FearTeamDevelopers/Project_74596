jQuery.noConflict();jQuery(document).ready(function(){jQuery("a.ajaxLoadTemplate").click(function(c){c.preventDefault();var b=jQuery("select[name=template] option:selected").val();var d=jQuery("select[name=lang] option:selected").val();var a=jQuery(this).attr("href")+b+"/"+d;jQuery.post(a,function(f){if(f=="notfound"){jQuery("#dialog p").text("Error while loading template");jQuery("#dialog").dialog()}else{var e=jQuery.parseJSON(f);CKEDITOR.instances.ckeditor.setData(e.text);jQuery("input[name=subject]").val(e.subject)}})});jQuery(".nosubmit").submit(function(a){a.preventDefault();return false});jQuery("#clear-text").click(function(a){a.preventDefault();CKEDITOR.instances.ckeditor.setData("")});jQuery("#clear-texten").click(function(a){a.preventDefault();CKEDITOR.instances.ckeditor2.setData("")});jQuery("#text-new-paragraph").click(function(a){a.preventDefault();CKEDITOR.instances.ckeditor.insertText('<br class="clear-all" />')});jQuery("#texten-new-paragraph").click(function(a){a.preventDefault();CKEDITOR.instances.ckeditor2.insertText('<br class="clear-all" />')});jQuery("#text-link-to-gallery, #texten-link-to-gallery").click(function(b){b.preventDefault();var a=jQuery(this).attr("id");jQuery("#insert-dialog p").html("Nahrávám ...");jQuery("#insert-dialog p").load("/admin/gallery/inserttocontent/");jQuery("#insert-dialog").dialog({title:"Vložit odkaz",width:600,modal:true,buttons:{Insert:function(){var f=jQuery("#content").val();var e=jQuery("#link-target").val();var d=jQuery("#link-name").val();var c='<a href="http://www.hastrman.cz/galerie/r/'+f+'" target='+e+">"+d+"</a>";if(a.substr(0,6)=="texten"){CKEDITOR.instances.ckeditor2.insertText(c)}else{CKEDITOR.instances.ckeditor.insertText(c)}jQuery("#insert-dialog p").html("");jQuery(this).dialog("close")},Close:function(){jQuery("#insert-dialog p").html("");jQuery(this).dialog("close")}}});return false});jQuery("#text-link-to-action, #texten-link-to-action").click(function(b){b.preventDefault();var a=jQuery(this).attr("id");jQuery("#insert-dialog p").html("Nahrávám ...");jQuery("#insert-dialog p").load("/admin/action/inserttocontent/");jQuery("#insert-dialog").dialog({title:"Vložit odkaz",width:600,modal:true,buttons:{Insert:function(){var f=jQuery("#content").val();var e=jQuery("#link-target").val();var d=jQuery("#link-name").val();var c='<a href="http://www.hastrman.cz/akce/r/'+f+'" target='+e+">"+d+"</a>";if(a.substr(0,6)=="texten"){CKEDITOR.instances.ckeditor2.insertText(c)}else{CKEDITOR.instances.ckeditor.insertText(c)}jQuery("#insert-dialog p").html("");jQuery(this).dialog("close")},Close:function(){jQuery("#insert-dialog p").html("");jQuery(this).dialog("close")}}});return false});jQuery("#text-link-to-news, #texten-link-to-news").click(function(b){b.preventDefault();var a=jQuery(this).attr("id");jQuery("#insert-dialog p").html("Nahrávám ...");jQuery("#insert-dialog p").load("/admin/news/inserttocontent/");jQuery("#insert-dialog").dialog({title:"Vložit odkaz",width:600,modal:true,buttons:{Insert:function(){var f=jQuery("#content").val();var e=jQuery("#link-target").val();var d=jQuery("#link-name").val();var c='<a href="http://www.hastrman.cz/novinky/r/'+f+'" target='+e+">"+d+"</a>";if(a.substr(0,6)=="texten"){CKEDITOR.instances.ckeditor2.insertText(c)}else{CKEDITOR.instances.ckeditor.insertText(c)}jQuery("#insert-dialog p").html("");jQuery(this).dialog("close")},Close:function(){jQuery("#insert-dialog p").html("");jQuery(this).dialog("close")}}});return false});jQuery("#text-link-to-content, #texten-link-to-content").click(function(b){b.preventDefault();var a=jQuery(this).attr("id");jQuery("#insert-dialog p").html("Nahrávám ...");jQuery("#insert-dialog p").load("/admin/content/inserttocontent/");jQuery("#insert-dialog").dialog({title:"Vložit odkaz",width:600,modal:true,buttons:{Insert:function(){var f=jQuery("#content").val();var e=jQuery("#link-target").val();var d=jQuery("#link-name").val();var c='<a href="http://www.hastrman.cz/page/'+f+'" target='+e+">"+d+"</a>";if(a.substr(0,6)=="texten"){CKEDITOR.instances.ckeditor2.insertText(c)}else{CKEDITOR.instances.ckeditor.insertText(c)}jQuery("#insert-dialog p").html("");jQuery(this).dialog("close")},Close:function(){jQuery("#insert-dialog p").html("");jQuery(this).dialog("close")}}});return false});jQuery("select[name=type]").change(function(){var a=jQuery(this).children("option:selected").val();if(a==1){if(jQuery("#singleRecipient").hasClass("nodisplay")){jQuery("#singleRecipient").removeClass("nodisplay");jQuery("#groupRecipient").addClass("nodisplay")}}else{if(jQuery("#groupRecipient").hasClass("nodisplay")){jQuery("#groupRecipient").removeClass("nodisplay");jQuery("#singleRecipient").addClass("nodisplay")}}})});