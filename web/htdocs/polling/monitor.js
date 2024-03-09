       var polling_interval = 50;

       let slx_totalConsumption = new countUp.CountUp('kt_countup-totalConsumption', 0, {  decimalPlaces: 2, suffix: ' kWh',});
          if (!slx_totalConsumption.error) {
                slx_totalConsumption.start();
          } else {
            console.log(slx_totalConsumption.error);
          }

          let slx_loadHome = new countUp.CountUp('kt_countup-loadHome', 0, {  duration: 4, suffix: ' W',});
          if (!slx_loadHome.error) {
            slx_loadHome.start();
          } else {
            console.log(slx_loadHome.error);
          }

        function pollAndDisplay() {

            var progressBar = document.getElementById('progressBar');
            progressBar.value = 0
            
            var progressInterval = setInterval(function () {
                progressBar.value += 1;
                progressBar.style.width =  progressBar.value  + "%";
                progressBar.innerText =  progressBar.value  + "%";

                if (progressBar.value < 100) {


                } else {
                    // Po dosažení 100% spustíme AJAX požadavek
                    progressBar.value = 0
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function () {
                        if (this.readyState == 4 && this.status == 200) {
                            // Po obdržení odpovědi vložíme výsledek do divu a spustíme další odpočítávání
                            try {
                            var response = JSON.parse(this.responseText);
                           
                            slx_loadHome.update(response.loadHome);
                            slx_totalConsumption.update(response.totalConsumption);
                            plain_text_container.innerHTML = response.formatted;
                            plain_text_container.innerHTML = e;
                           } catch (e) {
                              console.log(e); // error in the above string (in this case, yes)!
                           }
                           // setTimeout(pollAndDisplay, polling_interval); // 10000 ms = 10 sekund
                        }
                    };
                    xhttp.open("GET", "poll_mockup.php", true);
                    xhttp.send();
                }
            }, polling_interval); // 1000 ms = 1 sekunda (interval pro aktualizaci progress baru)
        }

        // Spustíme funkci při načtení stránky
       // window.onload = pollAndDisplay;

        window.onload = function() {
            pollAndDisplay(  );
          }