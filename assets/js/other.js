function moveMenuBar(element, time){
        var left = $(element).position().left;
        var width = $(element).width();
        $(".menu-bar").animate({
            left: left - 14,
            width: width
        }, time);
}

$(window).resize(function () {
    moveMenuBar('.active-menu-item', 0)   
})

$( document ).ready(function() {
    setTimeout(() => {
        $(".lds-ring").hide();
        $("#app").show();
    }, 550);
});

