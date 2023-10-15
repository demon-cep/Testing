$( document ).ready(function() {
    $(".form_ip").submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("action") + "?AJAX_REQUEST=Y",
            data: $(this).serialize(),
            type: 'POST',
            success: function (data) {
                $("#ansver").empty();
                if(data.ERROR=="Y"){
                        var error=" <div class=\"alert alert-danger\" role=\"alert\">\n" +
                            "        <h4 class=\"alert-heading\">"+data.ERROR_TEXT+"</h4>\n" +
                            "    </div>"
                    $("#ansver").append(error);
                }else{
                    var ansver =" <div class=\"alert alert-success\" role=\"alert\">\n" +
                        "        <h4 class=\"alert-heading\">Данные для "+data.GEO.UF_IP+"!</h4>\n" +
                        "        <hr>\n" +
                        "        <p>Страна: "+data.GEO.UF_COUNTRY_NAME+"</p>\n" +
                        "        <p>Регион: "+data.GEO.UF_REGION_NAME+"</p>\n" +
                        "        <p>Город: "+data.GEO.UF_CITY_NAME+"</p>\n" +
                        "\n" +
                        "    </div>";
                    $("#ansver").append(ansver);
                }

            }
        })
    });
});
