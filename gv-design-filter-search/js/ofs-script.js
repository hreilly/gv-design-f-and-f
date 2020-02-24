$ = jQuery;
  
var ofs = $("#option-filter-search"); 
var ofsForm = ofs.find("form");

$('input[type=radio]').on('change', function() {
    $(this).closest("form").submit();
    $('#options-query').fadeOut( 5, function(){
        $('#options-query').fadeIn( 500 );
    });
});
  
ofsForm.submit(function(e){
    e.preventDefault(); 
  
    // console.log("form submitted");
  
    var product = $("input[name='product']:checked").val();
    var option = $("input[name='option']").val();
      
    var data = {
        action : "option_filter_search",
        product : product,
        option : option,
    }

    $.ajax({
        url : ajax_url,
        data : data,
        success : function(response) {
            ofs.find("#options-query").empty();
            if(response) {
                for(var i = 0 ;  i < response.length ; i++) {
                     var html  = "<div class='single-option option-breakout' style='position: relative;'>";
                         html +=    "<a href='" + response[i].url + "'>";
                         if(response[i].img != '') {
                             html +=    "<img src='" + response[i].img + "' width='150px' height='150px' alt='' />";
                         } else {
                             html +=    "<div></div>";
                         }
                         html +=        "<div>";
                         html +=            "<p>";
                         html +=            response[i].termName;
                         if(response[i].standardtoggle != 1) {
                             html +=        "<sup>★</sup>";
                         }
                         html +=            "</p>";
                         html +=            "<p>";
                         html +=            response[i].truncdesc;
                         if(response[i].getlength > response[i].thelength) {
                            html += "...";
                         }
                         html +=            "</p>";
                         html +=            "<p style='font-style: italic;'>Available in: " + response[i].ucLines + "</p>";
                         html +=        "</div>";
                         html +=    "</a>";
                         html += "</div>"
                     ofs.find("#options-query").append(html);
                }
            } else {
                var html  = "<p style='color: black;'>No matching options found.</p>";
                ofs.find("#options-query").append(html);
            }
        } 
    });

});