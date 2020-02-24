$ = jQuery;
  
var pfs = $("#plan-filter-search"); 
var pfsForm = pfs.find("form");

$('input[type=radio]').on('change', function() {
    $(this).closest("form").submit();
    $('#plan-query').fadeOut( 5, function(){
        $('#plan-query').fadeIn( 500 );
    });
});
  
pfsForm.submit(function(e){
    e.preventDefault(); 
  
    // console.log("form submitted");

    $body = $("body");

    $(document).on({
        ajaxStart: function() { $body.addClass("ajax-loading");    },
        ajaxStop: function() { $body.removeClass("ajax-loading"); }    
    });
  
    var product = $("input[name='product']:checked").val();
      
    var data = {
        action : "plan_filter_search",
        product : product,
    }

    $.ajax({
        url : ajax_url,
        data : data,
        success : function(response) {
            pfs.find("#plan-query").empty();
            if(response) {
                for(var i = 0 ;  i < response.length ; i++) {
                     var html  = "<a href='" + response[i].permalink + "' title='" + response[i].title + "' class='split-page-item' style='text-decoration: none;'>";
                         html +=    "<div class='plan-card'>";
                         html +=        "<div class='plan-card-content'>";
                         html +=            "<h3>" + response[i].title + "</h3>";
                         html +=            "<div class='plan-card-info'>";
                         html +=                "<div>";
                         html +=                    "<h4>" + response[i].beds + "</h4>";
                         html +=                    "<p>Bedrooms</p>";
                         html +=                "</div>";
                         html +=                "<div>";
                         html +=                    "<h4>" + response[i].baths + "</h4>";
                         html +=                    "<p>Bathrooms</p>";
                         html +=                "</div>";
                         html +=                "<div>";
                         html +=                    "<h4>" + response[i].garage + "</h4>";
                         html +=                    "<p>Car Gar.</p>";
                         html +=                "</div>";
                         html +=                "<div>";
                         html +=                    "<h4>" + response[i].sqft + "</h4>";
                         html +=                    "<p>Square Feet</p>";
                         html +=                "</div>";
                         html +=            "</div>";
                         html +=        "</div>";
                         html +=        "<div class='plan-card-image' style='background-image: url(" + response[i].image + ")'></div>";
                         html +=    "</div>";
                         html += "</a>";
                     pfs.find("#plan-query").append(html);
                }
            } else {
                var html  = "<p style='color: black;'>No matching plans found.</p>";
                pfs.find("#plan-query").append(html);
            }
        } 
    });

});