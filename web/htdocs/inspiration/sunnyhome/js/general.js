$(document).ready(function() {
    if ($("#isAwayCheck").is(":checked") == true) {
        $("#awaymodetext").text(awaymodeontext);
    } else {
        $("#awaymodetext").text(awaymodeofftext);
    }
});
function SetAway() {
   var valstring = "";
   if ($("#isAwayCheck").is(":checked") == true) {
       valstring = "true";
       $("#awaymodetext").text(awaymodeontext);
   } else {
       valstring = "false";
       $("#awaymodetext").text(awaymodeofftext);
   }
   $.ajax({
       url: "/setsetting?token=frR4h32GMkrRlopoRekt&settingname=away_mode&settingvalue=" + valstring,
       dataType: 'text',
       cache: false,
       timeout: 600000,
       success: function(response) {
           if (response != "OK") {
               if (valstring == "true") {
                   $("#isAwayCheck").prop("checked", false);
               } else {
                   $("#isAwayCheck").prop("checked", true);
               }
           }
       }
   });
}