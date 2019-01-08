$(document).ready(function () {
    $(".name").animate({fontSize:'150px'}, 350, function() {
        $(".name").animate({fontSize:'110px'}, 400, function () {
            $(".name").animate({fontSize:'130px'}, 450);
        });
    });
    $(".wave1").animate({width:'100px', height:'100px'}, 200, function() {
        $(".wave1").animate({width:'80px', height:'80px'}, 250, function () {
            $(".wave1").animate({width:'90px', height:'90px'}, 900);
        });
    });

    $(".wave2").animate({width:'230px', height:'230px'}, 300, function() {
        $(".wave2").animate({width:'185px', height:'185px'}, 350, function () {
            $(".wave2").animate({width:'200px', height:'200px'}, 400);
        });
    });
    $(".wave3").animate({width:'450px', height:'450px'}, 300, function() {
        $(".wave3").animate({width:'375px', height:'375px'}, 350, function () {
            $(".wave3").animate({width:'400px', height:'400px'}, 400);
        });
    });
    $(".wave4").animate({width:'670px', height:'670px'}, 500, function() {
        $(".wave4").animate({width:'555px', height:'555px'}, 550, function () {
            $(".wave4").animate({width:'600px', height:'600px'}, 600);
        });
    });
    $(".wave5").animate({width:'940px', height:'940px'}, 600, function() {
        $(".wave5").animate({width:'790px', height:'790px'}, 650, function () {
            $(".wave5").animate({width:'850px', height:'850px'}, 700);
        });
    });
    $(".wave6").animate({width:'1300px', height:'1300px'}, 600, function() {
        $(".wave6").animate({width:'1100px', height:'1100px'}, 650, function () {
            $(".wave6").animate({width:'1200px', height:'1200px'}, 700);
        });
    });
    var $element1 = $(".msg1");
var $element2 = $(".msg2");   

$element1.show().arctext({radius:320});
$element2.show().arctext({radius:280, dir :-1})
});
