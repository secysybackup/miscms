function fontResizer(smallFont,medFont,largeFont){

    function clearSelected(){
        $(".smallFont").removeClass("curFont");
        $(".medFont").removeClass("curFont");
        $(".largeFont").removeClass("curFont");
    }

    function saveState(curSize){
        var date = new Date();
        date.setTime(date.getTime()+(7*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
        document.cookie = "fontSizer"+"="+curSize+expires+"; path=/";
    }

    $(".smallFont").click(function(){
        $('.fontsizebox').css('font-size', smallFont);
        clearSelected();
        $(".smallFont").addClass("curFont");
        saveState(smallFont);
    });

    $(".medFont").click(function(){
        $('.fontsizebox').css('font-size', medFont);
        clearSelected();
        $(".medFont").addClass("curFont");
        saveState(medFont);
    });

    $(".largeFont").click(function(){
        $('.fontsizebox').css('font-size', largeFont);
        clearSelected();
        $(".largeFont").addClass("curFont");
        saveState(largeFont);
    });

    function getCookie(c_name){
        if(document.cookie.length>0){
            c_start=document.cookie.indexOf(c_name + "=");
            if (c_start!=-1){
                c_start=c_start + c_name.length+1;
                c_end=document.cookie.indexOf(";",c_start);
                if(c_end==-1)c_end=document.cookie.length;
                return unescape(document.cookie.substring(c_start,c_end));
            }
        }
        return "";
    }
    var savedSize = getCookie('fontSizer');
    if(savedSize!=""){
        $('.fontsizebox').css('font-size', savedSize);
        switch(savedSize){
            case smallFont: $(".smallFont").addClass("curFont");
            break;
            case medFont: $(".medFont").addClass("curFont");
            break;
            case largeFont: $(".largeFont").addClass("curFont");
            break;
            default: $(".medFont").addClass("curFont");
        }
    }else {
        $('.fontsizebox').css('font-size', medFont);
        $(".medFont").addClass("curFont");
    }
}