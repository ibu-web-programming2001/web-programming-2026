/*
 * Weather planner – the external API feature.
 * The browser calls OUR backend (/weather/forecast); the backend calls Open-Meteo.
 */
const DiscoverPage = {
    init: function () {
        $("#weather-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            DiscoverPage.search($("#weather-city").val().trim(), $("#weather-days").val());
        });
        DiscoverPage.search($("#weather-city").val().trim(), $("#weather-days").val());   // Sarajevo by default
    },

    search: function (city, days) {
        $("#weather-place").text("");
        $("#weather-results").html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>');

        RestClient.get("weather/forecast?city=" + encodeURIComponent(city) + "&days=" + days, function (forecast) {
            $("#weather-place").text(forecast.city + (forecast.country ? ", " + forecast.country : ""));
            const column = forecast.days.length > 7 ? "col-6 col-md-3 col-xl-2" : "col-6 col-md-4 col-xl";
            $("#weather-results").html(forecast.days.map(day =>
                '<div class="' + column + '">' + Utils.weatherCard(day) + "</div>").join(""));
        }, function (message) {
            // 404 "No city called ... was found." or 503 "The weather service is not available..." – the rest of the app keeps working
            $("#weather-results").html('<div class="col-12"><div class="alert alert-warning"><i class="bi bi-cloud-slash me-2"></i>' + message + "</div></div>");
        });
    }
};
