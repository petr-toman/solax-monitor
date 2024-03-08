class SolarPowerLerp {
    constructor(elementId, color, maxPower) {
        this.elementId = elementId;
        this.color = color;
        this.currentGlowSize = 0;
        this.currentGlowSpread = 0;
        this.targetGlowSize = 0;
        this.targetGlowSpread = 0;
        this.lerpStartTime = null;
        this.maxGlowSize = 18;
        this.maxGlowSpread = 20;
        this.maxPower = maxPower;
        this.lerpDuration = 800;
        this.currentPower = 0;

        this.lerp_solar();
    }

    setCurrentPower(power) {
        this.currentPower = power;
        this.updateGlow(this.currentPower);
    }

    updateGlow(currentPower) {
        if (currentPower < 100) {
            this.targetGlowSize = 0;
            this.targetGlowSpread = 0;
        } else {
            this.targetGlowSize = (currentPower / this.maxPower) * this.maxGlowSize;
            this.targetGlowSpread = this.maxGlowSpread;
        }
        this.lerpStartTime = performance.now();
        this.startGlowSize = this.currentGlowSize;
        this.startGlowSpread = this.currentGlowSpread;
    }

    lerp_solar() {
        const lerp = () => {
            if (this.lerpStartTime !== null) {
                const elapsedTime = performance.now() - this.lerpStartTime;
                if (elapsedTime >= this.lerpDuration) {
                    this.currentGlowSize = this.targetGlowSize;
                    this.currentGlowSpread = this.targetGlowSpread;
                    this.lerpStartTime = null;
                } else {
                    const t = elapsedTime / this.lerpDuration;
                    this.currentGlowSize = this.startGlowSize * (1 - t) + this.targetGlowSize * t;
                    this.currentGlowSpread = this.startGlowSpread * (1 - t) + this.targetGlowSpread * t;
                }
            }

            let element = document.getElementById(this.elementId);
            if (element === null) {
                return;
            }
            element.style.boxShadow = `0px 0px ${this.currentGlowSpread.toFixed(2)}px ${this.currentGlowSize.toFixed(2)}px ${this.color}`;
            requestAnimationFrame(lerp);
        }

        lerp();
    }
}

setInterval(function() {
    refresh();
}, 1000);
// Update the device table every 11 seconds
setInterval(fetchAndRenderDevices, 11000);

$( document ).ready( refresh );

let glowSolarPanels;
let glowGrid;
let glowBattery;
let glowHouse;
let glowWallbox;

$( document ).ready(function () {
    // Create instances for your elements
    glowSolarPanels = new SolarPowerLerp('solar-power-compact', 'rgba(248,255,46,0.9)', power_installed);
    glowGrid = new SolarPowerLerp('grid-compact', 'rgba(220,53,69,0.9)', power_installed);
    glowBattery = new SolarPowerLerp('battery-compact', 'rgba(41,224,20,0.9)', power_installed);
    glowHouse = new SolarPowerLerp('house-compact', 'rgba(136,28,252,0.9)', power_installed);
    glowWallbox = new SolarPowerLerp('wallbox-compact', 'rgba(16,126,16,0.9)', power_installed);

    // Get the elements
    const yieldElement = document.getElementById('solax_today_yield_including_offgrid');
    const energyElement = document.getElementById('solax_today_energy');
    const yieldElementIcon = document.getElementById('solax_today_yield_ac');
    const energyElementIcon = document.getElementById('solax_today_energy_dc');
    // Get the elements
    const yieldTotalElement = document.getElementById('solax_total_energy');
    const energyTotalElement = document.getElementById('solax_total_panels_energy');
    const yieldTotalElementIcon = document.getElementById('solax_total_energy_ac');
    const energyTotalElementIcon = document.getElementById('solax_total_panels_energy_dc');

    const earningsTodayElement = document.getElementById('today_earnings_div');
    const savingsTodayElement = document.getElementById('today_savings_div');
    $( "#electron_wallbox_diagonal_move_down" ).hide();
    // Function to switch visibility
    function switchVisibility() {
        if (earningsTodayElement != null) {
            if (earningsTodayElement.style.display === 'none') {
                earningsTodayElement.style.display = 'inline';
                savingsTodayElement.style.display = 'none';
            } else {
                earningsTodayElement.style.display = 'none';
                savingsTodayElement.style.display = 'inline';
            }
        }
        if (energyTotalElement != null && yieldTotalElement != null) {
            if (yieldTotalElement.style.display === 'none') {
                yieldTotalElement.style.display = 'inline';
                yieldTotalElementIcon.style.display = 'inline';
                energyTotalElement.style.display = 'none';
                energyTotalElementIcon.style.display = 'none';
            } else {
                yieldTotalElement.style.display = 'none';
                energyTotalElement.style.display = 'inline';
                yieldTotalElementIcon.style.display = 'none';
                energyTotalElementIcon.style.display = 'inline';
            }
        }
        if (energyElement != null && yieldElement != null) {
            if (yieldElement.style.display === 'none') {
                yieldElement.style.display = 'inline';
                energyElement.style.display = 'none';
                yieldElementIcon.style.display = 'inline';
                energyElementIcon.style.display = 'none';
            } else {
                yieldElement.style.display = 'none';
                energyElement.style.display = 'inline';
                yieldElementIcon.style.display = 'none';
                energyElementIcon.style.display = 'inline';
            }
        }
        if (energyElement != null && yieldElement == null) {
            energyElement.style.display = 'inline';
            energyElementIcon.style.display = 'inline';
        }
        if (energyElement == null && yieldElement != null) {
            yieldElement.style.display = 'inline';
            yieldElement.style.display = 'inline';
        }

    }
    setInterval(switchVisibility, 10000);
    switchVisibility();
});

function refresh ( jQuery ) {
    $.ajax({
            type: "GET",
            contentType: "application/json",
            url: "/getinfodata?token=frR4h32GMkrRlopoRekt",
            dataType: 'json',
            cache: false,
            timeout: 600000,
            success: function (response) {
                var len = response.length;
                var total_consumption = 0;
                var solax_today_self_consumed_yield = 0;

                var battery_perc = 0;
                var battery_remenergy = 0;
                var battery_totalcharge = 0;

                for(var i = 0; i < len; i++){
                    var val = response[i].dataValue;
                    if (response[i].dataValue === "") {
                        val = "-";
                    }
                    var parsedVal = parseFloat(val);

                    if (response[i].units === "kWh") {
                        if (parsedVal > 1000) {
                            parsedVal = (parsedVal / 1000);
                            response[i].units = "MWh";
                        }
                    }

                    if (response[i].dataKey === "solax_wifi_firmware_version") {
                        parsedVal = val.toString();
                    }
                    if (response[i].dataKey === "solax_inverter_temperature") {
                        $("#compact_inverter_temperature_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                    }
                    if (isNaN(val)) {
                        $("#" + response[i].dataKey).text(val.toString() + " " + response[i].units);
                    } else {
                        $("#"+response[i].dataKey).text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                    }

                    if (response[i].dataKey === "solax_inverter_runmode") {
                        $("#solax_inverter_runmode").text(runModes[parsedVal]);
                    }
                    if (response[i].dataKey === "wallbox_workmode") {
                        $("#wallbox_workmode").text(runModesWallbox[parsedVal]);
                    }
                    $("#"+response[i].dataKey).prop('title', "Data Freshness: " + new Date(Date.parse(response[i].dataFreshness)).toLocaleString());
                    if (((new Date) - Date.parse(response[i].dataFreshness)) > response[i].validTime * 1000) {
                        $("#"+response[i].dataKey).text("n/a*");
                    }
                    if (response[i].dataKey === "ote_price_current_hour_czk_kwh" && (((new Date) - Date.parse(response[i].dataFreshness)) < 3700000)) {
                        $("#ote_price_current_hour_czk_kwh").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                    }

                    if (response[i].dataKey === "solax_today_feedin_energy" && spot_prices === false && (((new Date) - Date.parse(response[i].dataFreshness)) < 3700000)) {
                        let todayEarned = parsedVal * price_per_kwh_sell;
                        $("#fixed_earnings_today").text(todayEarned.toFixed(2).replace(/[.,]00$/, ""));
                    }
                    if (response[i].dataKey === "spot_earnings_today_local_currency" && spot_prices === true && (((new Date) - Date.parse(response[i].dataFreshness)) < 3700000)) {
                        $("#spot_earnings_today_local_currency").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + response[i].units);
                    }

                    if (response[i].dataKey === "solax_today_consumption") {
                        total_consumption = isNaN(parsedVal) ? total_consumption : parseFloat(parsedVal);
                    }
                    if (response[i].dataKey === "solax_today_self_consumed_yield") {
                        solax_today_self_consumed_yield = isNaN(parsedVal) ? solax_today_self_consumed_yield : parseFloat(parsedVal);
                        let todaySaved = solax_today_self_consumed_yield * price_per_kwh;
                        $("#today_savings_value").text(todaySaved.toFixed(2).replace(/[.,]00$/, "") + " " + energy_currency);
                    }
                    if (response[i].dataKey === "solax_battery_remaining_energy") {
                         battery_remenergy = parsedVal;
                    }
                    if (response[i].dataKey === "solax_total_battery_charge_energy") {
                        battery_totalcharge = parsedVal;
                    }
                    if (response[i].dataKey === "solax_battery_remaining_capacity") {
                        $("#compact_battery_percent").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        battery_perc = parsedVal;
                        var color = "";
                        if (parsedVal > 0) {
                            color = "#6b0700";
                            $("#compact_battery_icon").attr("class", "fa-solid fa-battery-empty");
                        }
                        if (parsedVal > 20) {
                            color = "#e00700";
                            $("#compact_battery_icon").attr("class", "fa-solid fa-battery-quarter");
                        }
                        if (parsedVal > 30) {
                            color = "#c76a00";
                            $("#compact_battery_icon").attr("class", "fa-solid fa-battery-quarter");
                        }
                        if (parsedVal > 50) {
                            color = "#0071c7";
                            $("#compact_battery_icon").attr("class", "fa-solid fa-battery-half");
                        }
                        if (parsedVal > 70) {
                            color = "#00c781";
                            $("#compact_battery_icon").attr("class", "fa-solid fa-battery-three-quarters");
                        }
                        if (parsedVal > 85) {
                            color = "#00d427";
                            $("#compact_battery_icon").attr("class", "fa-solid fa-battery-full");
                        }

                        $("#battery_percentage").attr("style", "width: " + parsedVal + "%; background-color: " + color);

                        if (((new Date) - Date.parse(response[i].dataFreshness)) > 600000) {
                            $("#battery_percentage").attr("style", "width: 0%");
                        }

                    }
                    if (response[i].dataKey === "solax_acpower") {
                        $("#compact_inverter_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                    }
                    if (response[i].dataKey === "solax_exported_power" && parsedVal < 0) {
                        glowGrid.setCurrentPower(Math.abs(parsedVal));
                        $( "#electron_grid_move_up" ).css({visibility: "visible"});
                        $( "#electron_grid_move_down" ).css({visibility: "hidden"});
                        $("#compact_grid_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $("#grid_arrow_down").children().hide();
                        $("#grid_arrow_up").children().show();
                    }
                    if (response[i].dataKey === "solax_exported_power" && parsedVal > 0) {
                        glowGrid.setCurrentPower(parsedVal);
                        $( "#electron_grid_move_up" ).css({visibility: "hidden"});
                        $( "#electron_grid_move_down" ).css({visibility: "visible"});
                        $("#compact_grid_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $( "#grid_arrow_up" ).children().hide();
                        $( "#grid_arrow_down" ).children().show();
                    }
                    if (response[i].dataKey === "solax_exported_power" && parsedVal === 0) {
                        glowGrid.setCurrentPower(0);
                        $( "#electron_grid_move_up" ).css({visibility: "hidden"});
                        $( "#electron_grid_move_down" ).css({visibility: "hidden"});
                        $("#compact_grid_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $("#grid_arrow_down").children().hide();
                        $("#grid_arrow_up").children().hide();
                    }

                    if (response[i].dataKey === "solax_solar_panels_power_total" && parsedVal > 0) {
                        glowSolarPanels.setCurrentPower(parsedVal);
                        $( "#electron_panels_move_down" ).css({visibility: "visible"});
                        $("#compact_panels_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $("#compact_solar_panels_perc").text(Math.round(parsedVal * 100/power_installed) + "%");
                        $( "#solar_arrow_down" ).children().show();
                    }

                    if (response[i].dataKey === "solax_solar_panels_power_total" && parsedVal === 0) {
                        glowSolarPanels.setCurrentPower(0);
                        $( "#electron_panels_move_down" ).css({visibility: "hidden", opacity: 0});
                        $("#compact_panels_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $("#compact_solar_panels_perc").text(Math.round(parsedVal * 100/power_installed) + "%");
                        $("#solar_arrow_down").children().hide();
                    }

                    if (response[i].dataKey === "solax_battery_power" && parsedVal < 0) {
                        if (glowBattery !== undefined) {
                            glowBattery.setCurrentPower(Math.abs(parsedVal));
                        }
                        $( "#electron_battery_move_left" ).css({visibility: "visible"});
                        $( "#electron_battery_move_right" ).css({visibility: "hidden"});
                        $("#compact_battery_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $("#battery_arrow_out").children().hide();
                        $("#battery_arrow_in").children().show();
                    }
                    if (response[i].dataKey === "solax_battery_power" && parsedVal > 0) {
                        if (glowBattery !== undefined) {
                            glowBattery.setCurrentPower(parsedVal);
                        }
                        $( "#electron_battery_move_left" ).css({visibility: "hidden"});
                        $( "#electron_battery_move_right" ).css({visibility: "visible"});
                        $("#compact_battery_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $( "#battery_arrow_in" ).children().hide();
                        $( "#battery_arrow_out" ).children().show();
                    }
                    if (response[i].dataKey === "solax_battery_power" && parsedVal === 0) {
                        if (glowBattery !== undefined) {
                            glowBattery.setCurrentPower(0);
                        }
                        $( "#electron_battery_move_left" ).css({visibility: "hidden"});
                        $( "#electron_battery_move_right" ).css({visibility: "hidden"});
                        $("#compact_battery_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $("#battery_arrow_in").children().hide();
                        $("#battery_arrow_out").children().hide();
                    }

                    if (response[i].dataKey === "solax_power_consumption_now" && parsedVal > 0) {
                        glowHouse.setCurrentPower(parsedVal);
                        $( "#electron_house_move_left" ).show();
                        $( "#electron_house_move_right" ).hide();
                        $("#compact_house_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $( "#house_arrow_out" ).children().show();
                    }
                    if (response[i].dataKey === "solax_power_consumption_now" && parsedVal === 0) {
                        $( "#electron_house_move_left" ).hide();
                        $( "#electron_house_move_right" ).hide();
                        glowHouse.setCurrentPower(0);
                        $("#compact_house_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $("#house_arrow_out").children().hide();
                    }
                    if (response[i].dataKey === "solax_power_consumption_now" && parsedVal < 0) {
                        $("#electron_house_move_left").hide();
                        $("#electron_house_move_right").show();
                        $("#compact_house_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                    }

                    /* WALLBOX START */
                    if (response[i].dataKey === "wallbox_charge_power_total" && parsedVal > 0) {
                        if (glowWallbox !== undefined) {
                            glowWallbox.setCurrentPower(parsedVal);
                        }
                        $( "#electron_wallbox_diagonal_move_down" ).show();
                        $( "#electron_wallbox_diagonal_move_down" ).css({visibility: "visible"});
                        $( "#compact_wallbox_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $( "#wallbox_arrow_in" ).children().hide();
                        $( "#wallbox_arrow_out" ).children().show();
                    }
                    if (response[i].dataKey === "wallbox_charge_power_total" && parsedVal === 0 && parsedVal != null) {
                        if (glowWallbox !== undefined) {
                            glowWallbox.setCurrentPower(0);
                        }
                        $( "#electron_wallbox_diagonal_move_down" ).hide();
                        $( "#electron_wallbox_diagonal_move_down" ).css({visibility: "hidden"});
                        $( "#compact_wallbox_text").text(parsedVal.toFixed(2).replace(/[.,]00$/, "") + " " + response[i].units);
                        $( "#wallbox_arrow_in").children().hide();
                        $( "#wallbox_arrow_out").children().hide();
                    }

                }
                var battery_cycles = battery_totalcharge / (100 * battery_remenergy / battery_perc);
                $("#solax_battery_cycles").text(battery_cycles.toFixed(0).replace(/[.,]00$/, ""));

                var totalcon = solax_today_self_consumed_yield + total_consumption;
                $("#solax_solar_power_balance").text((100 - (total_consumption / totalcon * 100)).toFixed(0).replace(/[.,]00$/, "") + "% solar");
                $("#total_consumption").text(totalcon.toFixed(2).replace(/[.,]00$/, "") + " kWh");
            }
        });
}

function fetchAndRenderDevices() {
    const currentUrl = window.location.href;
    if (currentUrl.includes("custom_dashboard")) {
        return;
    }
    fetch('/getdevices?token=frR4h32GMkrRlopoRekt')
        .then(response => response.json())
        .then(devices => {
            const table = document.getElementById('deviceTable');
            table.innerHTML = ''; // Clear the table content

            for (const device of devices) {
                const row = document.createElement('tr');

                const nameCell = document.createElement('td');
                // if device is on make the text green and append "- ON" to the name
                if (device.deviceStatus.currentState === "ON") {
                    nameCell.style.color = '#00ff00';
                    nameCell.textContent = `${device.deviceName} - ON`;
                } else {
                    nameCell.style.color = '#dc3545';
                    nameCell.textContent = `${device.deviceName} - OFF`;
                }
                row.appendChild(nameCell);

                const consumptionCell = document.createElement('td');
                consumptionCell.textContent = device.deviceStatus.currentConsumption !== null
                    ? `${device.deviceStatus.currentConsumption.toFixed(2)} W`
                    : '-';
                consumptionCell.style.color = '#fcba03';
                row.appendChild(consumptionCell);

                const todayConsumptionCell = document.createElement('td');
                todayConsumptionCell.textContent = device.deviceStatus.todayConsumption !== null
                    ? `${device.deviceStatus.todayConsumption.toFixed(1)} kWh`
                    : '-';
                todayConsumptionCell.style.color = '#fcba03';
                row.appendChild(todayConsumptionCell);

                table.appendChild(row);
            }
        })
        .catch(error => console.error('Error fetching devices:', error));
}
function scaleFlowchart() {
    const currentUrl = window.location.href;
    if (currentUrl.includes("custom_dashboard")) {
        return;
    }
    var parentElement = document.querySelector('#compact-flowchart-container');
    var parentWidth = parentElement.offsetWidth;
    var flowchart = document.getElementById('compact-flowchart');
    var originalFlowchartWidth = 450; // original width before scaling
    var maxFlowchartWidth = 450;

    // Calculate the scale factor based on the parent's width and the maximum width.
    var scaleFactor = Math.min(parentWidth * 1, maxFlowchartWidth) / originalFlowchartWidth;

    flowchart.style.transform = `scale(${scaleFactor})`;
    flowchart.style.transformOrigin = 'top left';

    // Center the flowchart within the parent.
    flowchart.style.position = 'absolute';
    flowchart.style.left = '50%';
    flowchart.style.top = '50%';
    flowchart.style.transform += ' translate(-50%, -50%)';
    parentElement.style.position = 'relative';

    // Adjust parent's height based on scale factor
    parentElement.style.height = (flowchart.offsetHeight * scaleFactor) + 'px';
}
$( document ).ready( function (){
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(scaleFlowchart, 100);
    });
    // Call once to scale initially
    scaleFlowchart();
    setTimeout(scaleFlowchart, 500);
    fetchAndRenderDevices()
});

