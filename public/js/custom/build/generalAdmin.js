jQuery.noConflict();jQuery(document).ready(function(){jQuery(".widgetlist a").hover(function(){jQuery(this).switchClass("default","hover")},function(){jQuery(this).switchClass("hover","default")});jQuery(window).load(function(){jQuery("#loader, .loader").hide();jQuery.post("/admin/system/showprofiler/",function(i){jQuery("body").append(i)})});jQuery("[title]").tooltip({position:{my:"left top",at:"right+5 top-5"}});jQuery("#tabs, .tabs").tabs();jQuery("#image-cropper, #image-cropper2").cropit({imageBackground:true,imageBackgroundBorderSize:15});jQuery(".cropit-form").submit(function(){var j=jQuery(this).find(".cropit-hidden-resized-image");var i=jQuery("#image-cropper").cropit("export");j.val(i);return true});jQuery(".cropit-form-dual").submit(function(){var k=jQuery(this).find(".cropit-hidden-resized-image");var l=jQuery(this).find(".cropit-hidden-resized-image2");var j=jQuery("#image-cropper").cropit("export");var i=jQuery("#image-cropper2").cropit("export");k.val(j);l.val(i);return true});jQuery(".datepicker, .datepicker2, .datepicker3").datepicker({changeMonth:true,changeYear:true,dateFormat:"yy-mm-dd",firstDay:1});jQuery("button.dialog, a.dialog").click(function(){var i=jQuery(this).attr("href");var j=jQuery(this).attr("value");jQuery("#dialog p").load(i);jQuery("#dialog").dialog({title:j,width:600,modal:true,position:{my:"center",at:"top",of:window},buttons:{Close:function(){jQuery("#dialog p").text("");jQuery(this).dialog("close")}}});return false});jQuery.fn.dataTableExt.oApi.fnPagingInfo=function(i){return{iStart:i._iDisplayStart,iEnd:i.fnDisplayEnd(),iLength:i._iDisplayLength,iTotal:i.fnRecordsTotal(),iFilteredTotal:i.fnRecordsDisplay(),iPage:Math.ceil(i._iDisplayStart/i._iDisplayLength),iTotalPages:Math.ceil(i.fnRecordsDisplay()/i._iDisplayLength)}};jQuery(".stdtable").DataTable({aaSorting:[],iDisplayLength:25,sPaginationType:"full_numbers"});var f=[];var e=jQuery("#type").val();var g=jQuery(".stdtable2").DataTable({aaSorting:[],iDisplayLength:50,sPaginationType:"full_numbers",serverSide:true,bProcessing:true,sServerMethod:"POST",sAjaxDataProp:"data",sAjaxSource:"/admin/"+e+"/load/",fnServerParams:function(i){i.push({name:"page",value:this.fnPagingInfo().iPage})},rowCallback:function(k,j,i){if(jQuery.inArray(j[0],f)!==-1){jQuery(k).addClass("togglerow")}},aoColumns:[null,null,null,null,{bSortable:false},{bSortable:false},{bSortable:false}]});g.on("draw",function(){jQuery(".ajaxDelete").click(function(k){k.preventDefault();var l=jQuery(this).parents("tr");var i=jQuery(this).attr("href");var j=jQuery("#csrf").val();jQuery("#deleteDialog p").text("Opravdu chcete pokračovat v mazání?");jQuery("#deleteDialog").dialog({resizable:false,width:300,height:150,modal:true,buttons:{Smazat:function(){jQuery("#loader, .loader").show();jQuery.post(i,{csrf:j},function(m){if(m=="success"){jQuery("#loader, .loader").hide();l.fadeOut()}else{alert(m)}});jQuery(this).dialog("close")},"Zrušit":function(){jQuery(this).dialog("close")}}});return false});jQuery(".ajaxReload").click(function(k){k.preventDefault();var i=jQuery(this).attr("href");var j=jQuery("#csrf").val();jQuery("#deleteDialog p").text("Opravdu chcete pokračovat?");jQuery("#deleteDialog").dialog({resizable:false,width:300,height:150,modal:true,buttons:{Ano:function(){jQuery("#loader, .loader").show();jQuery.post(i,{csrf:j},function(l){if(l=="success"){location.reload()}else{alert(l)}})},Ne:function(){jQuery(this).dialog("close")}}});return false});jQuery("button.dialog, a.dialog").click(function(){var i=jQuery(this).attr("href");var j=jQuery(this).attr("value");jQuery("#dialog p").load(i);jQuery("#dialog").dialog({title:j,width:600,modal:true,position:{my:"center",at:"top",of:window},buttons:{Close:function(){jQuery("#dialog p").text("");jQuery(this).dialog("close")}}});return false})});jQuery(".stdtable2 tbody").on("click","tr",function(){var j=jQuery(this).find("td:first").text();var i=jQuery.inArray(j,f);if(i===-1){f.push(j)}else{f.splice(i,1)}jQuery(this).toggleClass("togglerow")});jQuery(".tableoptions select").change(function(){var k=jQuery(this).children("option:selected").val();var i=jQuery(this).attr("name");jQuery(".tableoptions select[name="+i+"]").val(k);if(k==2){var j=jQuery(".stdtable2 tbody tr");j.each(function(){var m=jQuery(this).find("td:first").text();var l=jQuery.inArray(m,f);if(l===-1){f.push(m)}jQuery(this).addClass("togglerow")})}else{if(k==1){jQuery(".stdtable2 tbody tr.togglerow").removeClass("togglerow");f=[]}}});jQuery(".ajax-massaction").click(function(k){k.preventDefault();jQuery("#loader, .loader").show();var i=jQuery(this).attr("href");var l=jQuery(".tableoptions select[name=action]").children("option:selected").val();var j=jQuery("#csrf").val();jQuery.post(i,{csrf:j,action:l,ids:f},function(m){jQuery("#dialog p").text(m);jQuery("#loader, .loader").hide();jQuery("#dialog").dialog({title:"Výsledek",width:450,modal:true,buttons:{Close:function(){jQuery(this).dialog("close");jQuery(".stdtable2 tbody tr.togglerow").removeClass("togglerow");jQuery(".tableoptions select[name=selection]").val("1");f=[];g.ajax.reload()}}})});return false});jQuery(".userinfo").click(function(){if(!jQuery(this).hasClass("userinfodrop")){var i=jQuery(this);jQuery(".userdrop").width(i.width()+30);jQuery(".userdrop").slideDown("fast");i.addClass("userinfodrop")}else{jQuery(this).removeClass("userinfodrop");jQuery(".userdrop").hide()}jQuery(".notialert").removeClass("notiactive");jQuery(".notibox").hide();return false});jQuery(".notialert").click(function(){var j=jQuery(this);var i=j.attr("href");if(!j.hasClass("notiactive")){jQuery(".notibox").slideDown("fast");jQuery(".noticontent").empty();jQuery(".notibox .tabmenu li").each(function(){jQuery(this).removeClass("current")});jQuery(".notibox .tabmenu li:first-child").addClass("current");j.addClass("notiactive");jQuery(".notibox .loader").show();jQuery.post(i,function(k){jQuery(".notibox .loader").hide();jQuery(".noticontent").append(k)})}else{j.removeClass("notiactive");jQuery(".notibox").hide()}jQuery(".userinfo").removeClass("userinfodrop");jQuery(".userdrop").hide();return false});jQuery(document).click(function(k){var j=jQuery(".userdrop");var i=jQuery(".notibox");if(!jQuery(k.target).is(".userdrop")&&j.is(":visible")){j.hide();jQuery(".userinfo").removeClass("userinfodrop")}if(!jQuery(k.target).is(".notibox")&&i.is(":visible")){i.hide();jQuery(".notialert").removeClass("notiactive")}});jQuery(".tabmenu a").click(function(){var i=jQuery(this).attr("href");jQuery(".tabmenu li").each(function(){jQuery(this).removeClass("current")});jQuery(".noticontent").empty();jQuery(".notibox .loader").show();jQuery(this).parent().addClass("current");jQuery.post(i,function(j){jQuery(".notibox .loader").hide();jQuery(".noticontent").append(j)});return false});jQuery(".widgetbox .title").hover(function(){if(!jQuery(this).parent().hasClass("uncollapsible")){jQuery(this).addClass("titlehover")}},function(){jQuery(this).removeClass("titlehover")});jQuery(".widgetbox .title").click(function(){if(!jQuery(this).parent().hasClass("uncollapsible")){if(jQuery(this).next().is(":visible")){jQuery(this).next().slideUp("fast");jQuery(this).addClass("widgettoggle")}else{jQuery(this).next().slideDown("fast");jQuery(this).removeClass("widgettoggle")}}});jQuery(".leftmenu a span").each(function(){jQuery(this).wrapInner("<em />")});jQuery(".leftmenu a").click(function(m){var k=jQuery(this);var l=k.parent();var j=l.find("ul");var i=jQuery(this).parents(".lefticon");if(jQuery(this).hasClass("menudrop")){if(!i.length>0){if(j.length>0){if(j.is(":visible")){j.slideUp("fast");l.next().css({borderTop:"0"});k.removeClass("active")}else{j.slideDown("fast");l.next().css({borderTop:"1px solid #ddd"});k.addClass("active")}}if(jQuery(m.target).is("em")){return true}else{return false}}else{return true}}else{return true}});jQuery(".leftmenu a").hover(function(){if(jQuery(this).parents(".lefticon").length>0){jQuery(this).next().stop(true,true).fadeIn()}},function(){if(jQuery(this).parents(".lefticon").length>0){jQuery(this).next().stop(true,true).fadeOut()}});jQuery("#togglemenuleft a").click(function(){if(jQuery(".mainwrapper").hasClass("lefticon")){jQuery(".mainwrapper").removeClass("lefticon");jQuery(this).removeClass("toggle");jQuery(".leftmenu a").each(function(){jQuery(this).next().remove()})}else{jQuery(".mainwrapper").addClass("lefticon");jQuery(this).addClass("toggle");b()}});function b(){jQuery(".leftmenu a").each(function(){var i=jQuery(this).text();jQuery(this).removeClass("active");jQuery(this).parent().attr("style","");jQuery(this).parent().find("ul").hide();jQuery(this).after('<div class="menutip">'+i+"</div>")})}jQuery(document).scroll(function(){var i=jQuery(document).scrollTop();if(i>50){jQuery(".floatleft").css({position:"fixed",top:"10px",right:"10px"})}else{jQuery(".floatleft").css({position:"absolute",top:0,right:0})}});jQuery(document).scroll(function(){if(jQuery(this).width()>580){var i=jQuery(document).scrollTop();if(i>50){jQuery(".floatright").css({position:"fixed",top:"10px",right:"10px"})}else{jQuery(".floatright").css({position:"absolute",top:0,right:0})}}});jQuery(".notification .close").click(function(){jQuery(this).parent().fadeOut()});jQuery(".errorWrapper a").hover(function(){jQuery(this).switchClass("default","hover")},function(){jQuery(this).switchClass("hover","default")});var a=false;jQuery(window).resize(function(){if(jQuery(this).width()<1024){jQuery(".mainwrapper").addClass("lefticon");jQuery("#togglemenuleft").hide();jQuery(".mainright").insertBefore(".footer");b();if(jQuery(this).width()<=580){jQuery(".stdtable, .stdtable2").wrap('<div class="tablewrapper"></div>');if(jQuery(".headerinner2").length==0){h()}}else{c()}}else{d();c()}});if(jQuery(window).width()<1024){jQuery(".mainwrapper").addClass("lefticon");jQuery("#togglemenuleft").hide();jQuery(".mainright").insertBefore(".footer");b();if(jQuery(window).width()<=580){jQuery("table").wrap('<div class="tablewrapper"></div>');h()}}else{d()}function d(){if(!jQuery(".mainwrapper").hasClass("lefticon")){jQuery(".mainwrapper").removeClass("lefticon");jQuery("#togglemenuleft").show()}else{jQuery("#togglemenuleft").show();jQuery("#togglemenuleft a").addClass("toggle")}}function h(){jQuery(".headerinner").after('<div class="headerinner2"></div>');jQuery("#searchPanel").appendTo(".headerinner2");jQuery("#userPanel").appendTo(".headerinner2");jQuery("#userPanel").addClass("userinfomenu")}function c(){jQuery("#searchPanel").insertBefore("#notiPanel");jQuery("#userPanel").insertAfter("#notiPanel");jQuery("#userPanel").removeClass("userinfomenu");jQuery(".headerinner2").remove()}jQuery(".uploadForm .multi_upload").click(function(){if(jQuery(".uploadForm .file_inputs input[type=file]").length<7){jQuery(".uploadForm .file_inputs input[type=file]").last().after('<input type="file" name="uploadfile[]" accept="image/*"/>')}});jQuery(".uploadForm .multi_upload_dec").click(function(){if(jQuery(".uploadForm .file_inputs input[type=file]").length>1){jQuery(".uploadForm .file_inputs input[type=file]").last().remove()}});jQuery(".uploadForm").submit(function(){jQuery("#loader").show()});jQuery(".deleteImg").click(function(k){k.preventDefault();var i=jQuery(this).attr("href");var j=jQuery("#csrf").val();jQuery.post(i,{csrf:j},function(l){if(l=="success"){jQuery("#currentLogo, #currentImage").hide(500);jQuery(".uploadNewImage").removeClass("nodisplay")}else{jQuery("#currentLogo").append("<label class='error'>"+l+"</label>")}});return false});jQuery(".imagelist a.delete").click(function(l){l.preventDefault();var j=jQuery(this).parents("li");var i=jQuery(this).attr("href");var k=jQuery("#csrf").val();jQuery("#deleteDialog p").text("Opravdu chcete pokračovat v mazání?");jQuery("#deleteDialog").dialog({resizable:false,width:300,height:150,modal:true,buttons:{Smazat:function(){jQuery("#loader, .loader").show();jQuery.post(i,{csrf:k},function(m){if(m=="success"){jQuery("#loader, .loader").hide();j.hide("explode",500)}else{alert(m)}});jQuery(this).dialog("close")},"Zrušit":function(){jQuery(this).dialog("close")}}});return false});jQuery(".imagelist a.activate").click(function(l){l.preventDefault();var j=jQuery(this).parents("li");var i=jQuery(this).attr("href");var k=jQuery("#csrf").val();jQuery.post(i,{csrf:k},function(m){if(m=="active"){j.removeClass("photoinactive").addClass("photoactive")}else{if(m=="inactive"){j.removeClass("photoactive").addClass("photoinactive")}else{alert(m)}}});return false});jQuery(".ajaxDelete").click(function(k){k.preventDefault();var l=jQuery(this).parents("tr");var i=jQuery(this).attr("href");var j=jQuery("#csrf").val();jQuery("#deleteDialog p").text("Opravdu chcete pokračovat v mazání?");jQuery("#deleteDialog").dialog({resizable:false,width:300,height:150,modal:true,buttons:{Smazat:function(){jQuery("#loader, .loader").show();jQuery.post(i,{csrf:j},function(m){if(m=="success"){jQuery("#loader, .loader").hide();l.fadeOut()}else{alert(m)}});jQuery(this).dialog("close")},"Zrušit":function(){jQuery(this).dialog("close")}}});return false});jQuery(".ajaxReload").click(function(){event.preventDefault();var i=jQuery(this).attr("href");var j=jQuery("#csrf").val();jQuery("#deleteDialog p").text("Opravdu chcete pokračovat?");jQuery("#deleteDialog").dialog({resizable:false,width:300,height:150,modal:true,buttons:{Ano:function(){jQuery("#loader, .loader").show();jQuery.post(i,{csrf:j},function(k){if(k=="success"){location.reload()}else{alert(k)}})},Ne:function(){jQuery(this).dialog("close")}}});return false});jQuery(".ajaxChangestate").click(function(){var i=jQuery(this).attr("href");var j=jQuery("#csrf").val();jQuery("#loader, .loader").show();jQuery.post(i,{csrf:j},function(k){if(k=="active"||k=="inactive"){location.reload()}else{alert(k)}});return false});jQuery(".imagelist img").hover(function(){jQuery(this).stop().animate({opacity:0.75})},function(){jQuery(this).stop().animate({opacity:1})});jQuery(".btn").hover(function(){jQuery(this).stop().animate({backgroundColor:"#eee"})},function(){jQuery(this).stop().animate({backgroundColor:"#f7f7f7"})});jQuery(".stdbtn").hover(function(){jQuery(this).stop().animate({opacity:0.75})},function(){jQuery(this).stop().animate({opacity:1})});jQuery(".button-edit").button({icons:{primary:"ui-icon-pencil"},text:false});jQuery(".button-delete").button({icons:{primary:"ui-icon-trash"},text:false});jQuery(".button-detail").button({icons:{primary:"ui-icon-search"},text:false});jQuery(".button-comment").button({icons:{primary:"ui-icon-comment"},text:false});jQuery(".button-person").button({icons:{primary:"ui-icon-person"},text:false});jQuery(".stdtable .checkall").click(function(){var j=jQuery(this).parents("table");var i=j.find("tbody input[type=checkbox]");if(jQuery(this).is(":checked")){i.each(function(){jQuery(this).attr("checked",true);jQuery(this).parent().addClass("checked");jQuery(this).parents("tr").addClass("selected")});j.find(".checkall").each(function(){jQuery(this).attr("checked",true)})}else{i.each(function(){jQuery(this).attr("checked",false);jQuery(this).parent().removeClass("checked");jQuery(this).parents("tr").removeClass("selected")});j.find(".checkall").each(function(){jQuery(this).attr("checked",false)})}});jQuery(".stdtable tbody input[type=checkbox]").click(function(){if(jQuery(this).is(":checked")){jQuery(this).parents("tr").addClass("selected")}else{jQuery(this).parents("tr").removeClass("selected")}});jQuery(".massActionForm").submit(function(){var j=false;var i=jQuery(this).find("tbody input[type=checkbox]");i.each(function(){if(jQuery(this).is(":checked")){j=true}});if(!j){alert("No data selected");return false}else{return true}});jQuery("input[type=checkbox]").each(function(){var i=jQuery(this);i.wrap('<span class="checkbox"></span>');i.click(function(){if(jQuery(this).is(":checked")){i.attr("checked",true);i.parent().addClass("checked")}else{i.attr("checked",false);i.parent().removeClass("checked")}});if(jQuery(this).is(":checked")){i.attr("checked",true);i.parent().addClass("checked")}else{i.attr("checked",false);i.parent().removeClass("checked")}})});var editor1=CKEDITOR.replace("ckeditor",{height:550,filebrowserBrowseUrl:"/public/js/plugins/filemanager/elfinder.php",filebrowserImageBrowseUrl:"/public/js/plugins/filemanager/elfinder.php",filebrowserFlashBrowseUrl:"/public/js/plugins/filemanager/elfinder.php"});var editor2=CKEDITOR.replace("ckeditor2",{filebrowserBrowseUrl:"/public/js/plugins/filemanager/elfinder.php",filebrowserImageBrowseUrl:"/public/js/plugins/filemanager/elfinder.php",filebrowserFlashBrowseUrl:"/public/js/plugins/filemanager/elfinder.php"});